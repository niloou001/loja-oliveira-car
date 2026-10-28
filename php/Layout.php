<?php
require_once __DIR__ . '/Produto.php';
function h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function contatoVeiculo(array $p): ?string {
    $config = require __DIR__ . '/../config/loja.php';
    $phone = $config['whatsapp'];
    if (!preg_match('/^[1-9][0-9]{9,14}$/', $phone)) return null;
    return 'https://wa.me/' . $phone . '?text=' . rawurlencode('Olá! Tenho interesse no ' . $p['nome'] . ' (código ' . $p['id'] . '). Gostaria de mais informações.');
}
function cabecalho(string $title, string $description): void { ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?= h($description) ?>"><meta name="theme-color" content="#101d2b">
<title><?= h($title) ?> · Oliveira Car</title>
<link rel="icon" href="img/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="css/style.css"><script src="js/site.js" defer></script>
</head>
<body>
<a class="skip-link" href="#conteudo">Pular para o conteúdo</a>
<header class="site-header"><div class="wrap header-inner">
<a class="logo" href="index.php" aria-label="Oliveira Car, início"><span class="logo-mark" aria-hidden="true">O<span class="arrow" aria-hidden="true"></span></span><span>OLIVEIRA<span class="logo-car"> CAR</span><small>SEU PRÓXIMO CAMINHO</small></span></a>
<nav aria-label="Principal"><a href="index.php#estoque">Explorar veículos</a><a class="nav-secondary" href="index.php#como-funciona">Como funciona</a><a class="nav-saved" href="index.php?favoritos=1#estoque" aria-label="Ver meus favoritos">♡ <span>Favoritos</span></a></nav>
</div></header>
<?php }
function rodape(): void { ?>
<footer class="site-footer"><div class="wrap footer-inner"><div><strong>OLIVEIRA CAR</strong><p>Grandes carros. Novas possibilidades.</p></div><p>Valores e disponibilidade sujeitos a confirmação.<br>Imagens ilustrativas. Simulações não constituem oferta de crédito.</p><a href="index.php#estoque">Explorar catálogo <span class="arrow" aria-hidden="true"></span></a></div></footer>
<p class="toast" id="toast" role="status" hidden></p>
</body></html>
<?php }
