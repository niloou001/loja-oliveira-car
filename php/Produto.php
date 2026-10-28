<?php

// Formata valor monetário em Real brasileiro
function formatarValor($valor) {
    return "R$ " . number_format($valor, 2, ",", ".");
}

// Calcula parcela sem juros
function calcularParcelaSemJuros($preco, $qtd) {
    return formatarValor($preco / $qtd);
}

// Juros simples: J = P × i × n
// Retorna o valor da parcela com juros simples para n meses
function calcularParcelaComJuros($preco, $taxa, $n) {
    $juros = $preco * ($taxa / 100) * $n;
    $total = $preco + $juros;
    $parcela = $total / $n;
    return formatarValor($parcela);
}

// Retorna o total pago com juros simples
function calcularTotalComJuros($preco, $taxa, $n) {
    $juros = $preco * ($taxa / 100) * $n;
    return formatarValor($preco + $juros);
}
