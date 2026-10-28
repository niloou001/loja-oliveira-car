<?php
require_once __DIR__ . '/data/produtos.php';
require_once __DIR__ . '/php/Layout.php';
cabecalho('Encontre seu próximo carro', 'Explore os veículos da Oliveira Car. Compare modelos, salve seus favoritos e simule as parcelas de forma simples.');
$marcas = array_unique(array_column($produtos, 'marca')); sort($marcas);
$hero = reset($produtos);
?>
<main id="conteudo">
<section class="hero"><div class="wrap hero-grid">
<div class="hero-copy"><p class="eyebrow"><span class="dot"></span> OLIVEIRA CAR · CATÁLOGO DE VEÍCULOS</p><h1>O próximo capítulo<br>começa na sua <em>garagem.</em></h1><p class="hero-description">Encontre o carro que combina com você. Explore os modelos, descubra cada detalhe e planeje seu próximo passo.</p><a class="button button-orange" href="#estoque">Encontrar meu carro <span class="arrow" aria-hidden="true"></span></a><div class="hero-notes"><span>✓ Preços à vista e parcelados</span><span>✓ Detalhes em um só lugar</span></div></div>
<?php if ($hero): ?><div class="hero-visual"><span class="hero-label">EM DESTAQUE</span><img src="<?= h($hero['imagem']) ?>" alt="<?= h($hero['nome']) ?>" fetchpriority="high" data-car-image><div class="hero-vehicle"><div><small>FEITO PARA IMPRESSIONAR</small><strong><?= h($hero['nome']) ?></strong></div><a href="detalhe-produto.php?id=<?= $hero['id'] ?>" aria-label="Conhecer <?= h($hero['nome']) ?>"><span class="arrow" aria-hidden="true"></span></a></div></div><?php endif; ?>
</div></section>
<section class="catalog wrap" id="estoque" aria-labelledby="catalog-title">
<div class="section-heading"><div><p class="eyebrow">ESCOLHA O SEU PRÓXIMO DESTINO</p><h2 id="catalog-title">Um carro. Muitas possibilidades.</h2></div><p>Explore com calma.<br>Seu próximo carro pode estar aqui.</p></div>
<form id="filters" class="filters" role="search">
<label class="search-field"><span>O que você procura?</span><input id="search" type="search" placeholder="Busque por nome ou marca" autocomplete="off"></label>
<label><span>Marca</span><select id="brand"><option value="">Todas as marcas</option><?php foreach ($marcas as $marca): ?><option value="<?= h($marca) ?>"><?= h($marca) ?></option><?php endforeach; ?></select></label>
<label><span>Preço máximo</span><select id="price"><option value="">Qualquer valor</option><option value="300000">Até R$ 300 mil</option><option value="500000">Até R$ 500 mil</option><option value="1000000">Até R$ 1 milhão</option><option value="5000000">Até R$ 5 milhões</option></select></label>
<button type="reset" class="reset-button">Limpar filtros</button>
</form>
<div class="catalog-toolbar"><p id="results-count" role="status" aria-live="polite"><?= count($produtos) ?> veículos no catálogo</p><div class="toolbar-actions"><button type="button" class="saved-filter" id="saved-filter" aria-pressed="false">♡ Só favoritos</button><label class="sort-label">Ordenar <select id="sort"><option value="default">Destaques</option><option value="price-asc">Menor preço</option><option value="price-desc">Maior preço</option><option value="name">Nome A–Z</option></select></label></div></div>
<noscript><p class="notice">Todos os veículos estão listados abaixo. Ative o JavaScript para usar filtros, favoritos e o simulador interativo.</p></noscript>
<div class="vehicle-grid" id="vehicle-grid"><?php foreach ($produtos as $indice => $produto) include __DIR__ . '/views/card.php'; ?></div>
<div id="empty-state" class="empty-state" hidden><span aria-hidden="true">⌕</span><h3>Nenhum veículo por aqui.</h3><p>Experimente outra busca ou salve um carro nos favoritos para vê-lo depois.</p><button class="button button-dark" type="button" id="clear-all">Ver todos os veículos</button></div>
</section>
<section class="how-it-works" id="como-funciona"><div class="wrap"><div class="section-heading"><div><p class="eyebrow">DO SEU JEITO, NO SEU TEMPO</p><h2>Mais simples a cada passo.</h2></div></div><div class="steps"><article><span>01 /</span><h3>Encontre seu favorito</h3><p>Filtre por marca e valor. Salve os modelos que mais combinam com você.</p></article><article><span>02 /</span><h3>Conheça cada detalhe</h3><p>Veja a descrição e simule entrada e parcelas antes de decidir.</p></article><article><span>03 /</span><h3>Planeje o próximo passo</h3><p>Guarde o link do veículo e confirme valores e disponibilidade antes da compra.</p></article></div></div></section>
</main>
<?php rodape(); ?>
