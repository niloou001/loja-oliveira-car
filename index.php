<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Loja Virtual - ETEC João Belarmino</title>
</head>
<body>

    <!-- cabeça -->
    <header>
        <a class="logo" href="index.php">
            <span>Loja Oliveira</span>
            <span class="badge">ETEC</span>
        </a>
        <nav>
            <a href="index.php">Início</a>
        </nav>
    </header>

    <!-- baner -->
    <section class="banner">
        <h1>Os Melhores Carros do Mundo</h1>
        <p>Encontre o supercarro dos seus sonhos com as melhores condições de pagamento. Parcele em até 60x com juros mínimos!</p>
        <span class="banner-badge">🏎️ +5 modelos exclusivos disponíveis</span>
        <span class="banner-badge">Preços acessíveis somente para você</span>
        <span class="banner-badge">Carros de ultima geração</span>
        <span class="banner-badge">Dirija com segurança e conforto</span>
    </section>

    <!-- titulo -->
    <h2 class="section-title">
        Nossos Produtos
        <span></span>
    </h2>

    <!-- lista -->
    <div class="container">
        <?php
            require_once("data/produtos.php");
            require_once("php/Produto.php");

            foreach ($produtos as $indice => $produto) {
                include("views/card.php");
            }
        ?>
    </div>

    <footer>
        <p><strong>Loja Nicolas Oliveira </strong> Projeto Loja Virtual · ETEC João Belarmino</p>
    </footer>

</body>
</html>
