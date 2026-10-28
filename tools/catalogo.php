<?php
declare(strict_types=1);
// Administração somente por terminal: nenhum endpoint de escrita é exposto.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../php/Catalogo.php';
function imprimir($value): void { echo json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL; }
try {
    $acao = $argv[1] ?? 'listar'; $db = bancoCatalogo();
    if ($acao === 'listar') { imprimir(array_values(listarVeiculos(true))); exit; }
    if ($acao === 'historico') { imprimir($db->query('SELECT * FROM historico ORDER BY id DESC LIMIT 100')->fetchAll()); exit; }
    if ($acao === 'backup') {
        $path = $argv[2] ?? throw new InvalidArgumentException('Informe o caminho de um novo backup fora da raiz pública.');
        if (file_exists($path)) throw new InvalidArgumentException('O destino já existe.');
        $db->exec('VACUUM INTO ' . $db->quote($path)); chmod($path, 0600); echo "Backup criado.\n"; exit;
    }
    if ($acao === 'exportar') {
        $path = __DIR__ . '/../data/catalogo.json';
        $rows = array_values(listarVeiculos(true));
        foreach ($rows as &$p) unset($p['atualizado_em']);
        $json = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
        $temp = tempnam(dirname($path), '.catalogo-');
        if (file_put_contents($temp, $json) === false || !rename($temp, $path)) throw new RuntimeException('Falha ao exportar catálogo.');
        echo "Catálogo exportado. Faça o build e publique para atualizar o site público.\n"; exit;
    }
    if (!in_array($acao, ['adicionar', 'atualizar', 'remover'], true)) throw new InvalidArgumentException('Comandos: listar, adicionar JSON, atualizar ID JSON, remover ID, exportar, historico, backup CAMINHO.');
    $db->beginTransaction(); $before = null;
    if ($acao === 'adicionar') {
        $p = json_decode($argv[2] ?? '', true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($p)) throw new InvalidArgumentException('Informe um objeto JSON.');
        $id = salvarVeiculo($db, $p);
    } else {
        $id = filter_var($argv[2] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($id === false) throw new InvalidArgumentException('ID inválido.');
        $before = listarVeiculos(true)[$id] ?? throw new InvalidArgumentException('Veículo não encontrado.');
        if ($acao === 'remover') $db->prepare('DELETE FROM veiculos WHERE id = ?')->execute([$id]);
        else {
            $patch = json_decode($argv[3] ?? '', true, 512, JSON_THROW_ON_ERROR);
            $allowed = ['nome', 'marca', 'descricao', 'imagem', 'valor', 'parcelamento_max_sj', 'parcelamento_max_cj', 'juros', 'status'];
            if (!is_array($patch) || !$patch || array_diff(array_keys($patch), $allowed)) throw new InvalidArgumentException('Campos de atualização inválidos.');
            salvarVeiculo($db, array_replace($before, $patch), $id);
        }
    }
    $after = listarVeiculos(true)[$id] ?? null;
    $db->prepare('INSERT INTO historico (acao, veiculo_id, dados) VALUES (?, ?, ?)')->execute([$acao, $id, json_encode(['antes' => $before, 'depois' => $after], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)]);
    $db->commit(); imprimir(['acao' => $acao, 'id' => $id, 'veiculo' => $after]);
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    fwrite(STDERR, $e->getMessage() . PHP_EOL); exit(1);
}
