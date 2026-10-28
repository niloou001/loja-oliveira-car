<?php
declare(strict_types=1);

/** Banco fora da raiz pública; JSON versionado para restaurar o catálogo. */
function bancoCatalogo(): PDO {
    static $db;
    if ($db instanceof PDO) return $db;
    $path = getenv('CATALOG_DB_PATH') ?: dirname(__DIR__, 2) . '/.loja-oliveira-car/catalogo.sqlite';
    if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0700, true) && !is_dir(dirname($path))) throw new RuntimeException('Não foi possível criar a pasta do banco.');
    $db = new PDO('sqlite:' . $path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $db->exec('PRAGMA busy_timeout = 5000');
    $db->exec('CREATE TABLE IF NOT EXISTS veiculos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL CHECK(length(trim(nome)) > 0),
        marca TEXT NOT NULL, descricao TEXT NOT NULL, imagem TEXT NOT NULL,
        valor_centavos INTEGER NOT NULL CHECK(valor_centavos > 0),
        parcelamento_max_sj INTEGER NOT NULL CHECK(parcelamento_max_sj BETWEEN 1 AND 120),
        parcelamento_max_cj INTEGER NOT NULL CHECK(parcelamento_max_cj BETWEEN parcelamento_max_sj AND 120),
        juros REAL NOT NULL CHECK(juros BETWEEN 0 AND 100),
        status TEXT NOT NULL CHECK(status IN (\'disponivel\', \'reservado\', \'vendido\')),
        atualizado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
    $db->exec('CREATE TABLE IF NOT EXISTS historico (id INTEGER PRIMARY KEY, acao TEXT NOT NULL, veiculo_id INTEGER, dados TEXT NOT NULL, criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');
    $db->exec('CREATE TABLE IF NOT EXISTS metadados (chave TEXT PRIMARY KEY, valor TEXT NOT NULL)');
    $db->beginTransaction();
    try {
        if (!$db->query("SELECT valor FROM metadados WHERE chave = 'inicializado'")->fetchColumn()) {
            $seed = json_decode(file_get_contents(__DIR__ . '/../data/catalogo.json'), true, 512, JSON_THROW_ON_ERROR);
            foreach ($seed as $veiculo) salvarVeiculo($db, $veiculo, $veiculo['id']);
            $db->exec("INSERT INTO metadados VALUES ('inicializado', '1')");
        }
        $db->commit();
    } catch (Throwable $e) { $db->rollBack(); throw $e; }
    if (is_file($path)) chmod($path, 0600);
    return $db;
}

function validarVeiculo(array $p): array {
    $fields = ['nome', 'marca', 'descricao', 'imagem', 'valor', 'parcelamento_max_sj', 'parcelamento_max_cj', 'juros', 'status'];
    foreach ($fields as $field) if (!array_key_exists($field, $p)) throw new InvalidArgumentException("Campo obrigatório: $field");
    foreach (['nome', 'marca', 'descricao', 'imagem', 'status'] as $field) {
        if (!is_string($p[$field]) || trim($p[$field]) === '') throw new InvalidArgumentException("Campo inválido: $field");
        $p[$field] = trim($p[$field]);
    }
    if (!filter_var($p['imagem'], FILTER_VALIDATE_URL) || parse_url($p['imagem'], PHP_URL_SCHEME) !== 'https') throw new InvalidArgumentException('A imagem deve usar uma URL HTTPS.');
    foreach (['valor', 'juros'] as $field) {
        if (!is_numeric($p[$field]) || !is_finite((float)$p[$field])) throw new InvalidArgumentException("Número inválido: $field");
        $p[$field] = (float)$p[$field];
    }
    if ($p['valor'] < 0.01 || $p['valor'] > 1000000000 || $p['juros'] < 0 || $p['juros'] > 100) throw new InvalidArgumentException('Preço ou juros fora do limite.');
    foreach (['parcelamento_max_sj', 'parcelamento_max_cj'] as $field) {
        if (filter_var($p[$field], FILTER_VALIDATE_INT) === false || $p[$field] < 1 || $p[$field] > 120) throw new InvalidArgumentException("Parcelamento inválido: $field");
        $p[$field] = (int)$p[$field];
    }
    if ($p['parcelamento_max_cj'] < $p['parcelamento_max_sj']) throw new InvalidArgumentException('O limite com juros deve ser maior ou igual ao limite sem juros.');
    if (!in_array($p['status'], ['disponivel', 'reservado', 'vendido'], true)) throw new InvalidArgumentException('Status inválido.');
    return array_intersect_key($p, array_flip($fields));
}

function salvarVeiculo(PDO $db, array $p, ?int $id = null): int {
    $p = validarVeiculo($p);
    $p['valor_centavos'] = (int) round($p['valor'] * 100); unset($p['valor']);
    if ($id !== null) $p['id'] = $id;
    $columns = implode(', ', array_keys($p));
    $placeholders = ':' . implode(', :', array_keys($p));
    $sql = "INSERT INTO veiculos ($columns) VALUES ($placeholders)";
    if ($id !== null) {
        $updates = implode(', ', array_map(fn($key) => "$key = excluded.$key", array_keys($p)));
        $sql .= " ON CONFLICT(id) DO UPDATE SET $updates, atualizado_em = CURRENT_TIMESTAMP";
    }
    $db->prepare($sql)->execute($p);
    return $id ?? (int)$db->lastInsertId();
}

function listarVeiculos(bool $incluirVendidos = false): array {
    $query = 'SELECT * FROM veiculos' . ($incluirVendidos ? '' : " WHERE status != 'vendido'") . ' ORDER BY id';
    $out = [];
    foreach (bancoCatalogo()->query($query)->fetchAll() as $p) {
        $p['valor'] = $p['valor_centavos'] / 100; unset($p['valor_centavos']);
        $out[(int)$p['id']] = $p;
    }
    return $out;
}
