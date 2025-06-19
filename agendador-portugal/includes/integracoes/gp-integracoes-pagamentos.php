<?php
/**
 * Integração com sistemas de pagamento: Stripe, PayPal, MB WAY, Multibanco
 * @author DriveWeb - Rodrigo Soares
 * @link https://www.driveweb.pt
 * @date 18/06/2025
 */

if (!defined('ABSPATH')) exit;

/**
 * Gera uma referência Multibanco simulada
 *
 * @param string $entidade
 * @param string $subentidade
 * @param float $valor
 * @return array
 */
function agp_gerar_referencia_multibanco($entidade, $subentidade, $valor) {
    $valor = floatval($valor);
    if ($valor <= 0) {
        return ['erro' => 'Valor inválido para referência Multibanco.'];
    }

    $ref = rand(100000000, 999999999);
    return [
        'entidade'     => sanitize_text_field($entidade),
        'subentidade'  => sanitize_text_field($subentidade),
        'referencia'   => chunk_split($ref, 3, ' '),
        'valor'        => number_format($valor, 2, ',', '.')
    ];
}

/**
 * Simula envio de pagamento via MB WAY
 *
 * @param string $telefone
 * @param float $valor
 * @param string $descricao
 * @return array
 */
function agp_enviar_mbway_pagamento($telefone, $valor, $descricao) {
    $valor = floatval($valor);
    $apiKey = get_option('agp_mbway_api_key');

    if (!$apiKey) {
        return ['erro' => 'API Key do MB WAY não está configurada.'];
    }

    if ($valor <= 0 || empty($telefone)) {
        return ['erro' => 'Telefone ou valor inválido para envio MB WAY.'];
    }

    // Simulação de chamada à API MB WAY
    return [
        'status'  => 'pendente',
        'mensagem'=> "Pagamento MB WAY de €" . number_format($valor, 2, ',', '.') . " enviado para {$telefone}."
    ];
}
