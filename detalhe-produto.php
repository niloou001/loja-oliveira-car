<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Detalhe do Produto - AutoLoja ETEC</title>
</head>
<body>

    <!-- HEADER -->
    <header>
        <a class="logo" href="index.php">
            <span>Loja Oliveira</span>
            <span class="badge">ETEC</span>
        </a>
        <nav>
            <a href="index.php">Início</a>

        </nav>
    </header>

    <?php
        require_once("data/produtos.php");
        require_once("php/Produto.php");

  
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;


        if (!isset($produtos[$id])) {
            echo '<div class="detalhe-wrapper"><p>Produto não encontrado. <a href="index.php">Voltar</a></p></div>';
            exit;
        }

        $p = $produtos[$id];
    ?>

    <div class="detalhe-wrapper">


        <p class="breadcrumb">
            <a href="index.php">🏠 Início</a> › <?= htmlspecialchars($p['nome']); ?>
        </p>


        <div class="detalhe-card">
            <div class="detalhe-img">
                <img src="<?= htmlspecialchars($p['imagem']); ?>" alt="<?= htmlspecialchars($p['nome']); ?>">
            </div>
            <div class="detalhe-info">
                <h1><?= htmlspecialchars($p['nome']); ?></h1>

                <p class="descricao"><?= htmlspecialchars($p['descricao']); ?></p>

                <p class="preco-grande"><?= formatarValor($p['valor']); ?></p>
                <span class="avista">💳 Pagamento à vista</span>

                <p style="font-size:13px; color:#555; margin-top:8px;">
                    Sem juros: até <strong><?= $p['parcelamento_max_sj']; ?>x</strong> de
                    <strong><?= calcularParcelaSemJuros($p['valor'], $p['parcelamento_max_sj']); ?></strong>
                </p>
                <p style="font-size:13px; color:#555;">
                    Com juros: até <strong><?= $p['parcelamento_max_cj']; ?>x</strong> ·
                    Taxa: <strong><?= number_format($p['juros'], 2, ',', '.'); ?>% a.m.</strong>
                </p>
            </div>
        </div>

        <!-- TABELA DE PARCELAMENTO (Juros Simples) -->
        <div class="parc-section" style="margin-top: 36px;">
            <h2>💳 Tabela de Parcelamento</h2>

            <div class="parc-tabs">
                <div class="parc-tab sj">✅ Sem Juros</div>
                <div class="parc-tab cj">⚠️ Com Juros (Simples · <?= number_format($p['juros'], 2, ',', '.'); ?>% a.m.)</div>
            </div>

            <table class="parc-table">
                <thead>
                    <tr>
                        <th>Parcelas</th>
                        <th>Valor por parcela</th>
                        <th>Total pago</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // --- Parcelamentos SEM juros ---
                    // Exibe opções: 1x, 2x, 3x ... até parc_max_sj
                    for ($n = 1; $n <= $p['parcelamento_max_sj']; $n++):
                        $parcela = calcularParcelaSemJuros($p['valor'], $n);
                        $total   = formatarValor($p['valor']);
                    ?>
                    <tr>
                        <td><strong><?= $n; ?>x</strong></td>
                        <td><?= $parcela; ?></td>
                        <td><?= $total; ?></td>
                        <td><span class="tag-sj">Sem juros</span></td>
                    </tr>
                    <?php endfor; ?>

                    <?php
                    // --- Parcelamentos COM juros (Juros Simples: J = P × i × n) ---
                    // Começa logo após o limite sem juros
                    $inicio_cj = $p['parcelamento_max_sj'] + 1;
                    for ($n = $inicio_cj; $n <= $p['parcelamento_max_cj']; $n++):
                        $parcela = calcularParcelaComJuros($p['valor'], $p['juros'], $n);
                        $total   = calcularTotalComJuros($p['valor'], $p['juros'], $n);
                    ?>
                    <tr>
                        <td><strong><?= $n; ?>x</strong></td>
                        <td><?= $parcela; ?></td>
                        <td><?= $total; ?></td>
                        <td><span class="tag-cj">Com juros</span></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>

        <!-- Nota sobre Juros Simples -->
        <div style="margin-top:20px; padding:16px 20px; background:#e8f0fe; border-left:4px solid #1565c0; border-radius:8px; font-size:13px; color:#333; line-height:1.8;">
            <strong style="color:#0d47a1;">📌 Como calculamos o parcelamento com juros?</strong><br>
            Utilizamos <strong>Juros Simples</strong>: <em>J = P × i × n</em><br>
            Onde: <em>P</em> = valor do produto · <em>i</em> = taxa mensal (<?= number_format($p['juros'], 2, ',', '.'); ?>%) · <em>n</em> = número de parcelas<br>
            O total pago é <em>T = P + J</em>, dividido pelo número de parcelas.
            <br><a href="https://brasilescola.uol.com.br/matematica/juros-simples.htm" target="_blank" style="color:#1565c0;">📚 Saiba mais sobre juros simples</a>
        </div>

        <a href="index.php" class="btn-voltar">← Voltar para a Loja</a>

    </div>

    <!-- FOOTER -->
    <footer>
        <p><strong>Loja Oliveira ETEC</strong> — Projeto Loja Virtual · ETEC João Belarmino</p>
    </footer>

</body>
</html>
