<a class="card" href="detalhe-produto.php?id=<?= $indice; ?>">
    <div class="card-img-wrap">
        <img src="<?= htmlspecialchars($produto['imagem']); ?>" alt="<?= htmlspecialchars($produto['nome']); ?>">
    </div>
    <div class="card-body">
        <p class="nome"><?= htmlspecialchars($produto['nome']); ?></p>

        <p class="preco"><?= formatarValor($produto['valor']); ?></p>

        <p class="preco_parc">
            Em até <?= $produto['parcelamento_max_sj']; ?>x de
            <?= calcularParcelaSemJuros($produto['valor'], $produto['parcelamento_max_sj']); ?> sem juros
        </p>

        <button>Ver detalhes</button>
    </div>
</a>
