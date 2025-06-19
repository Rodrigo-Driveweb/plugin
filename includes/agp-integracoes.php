<?php
/**
 * Módulo Integrações - Plugin Agendador Portugal
 * Autor: DriveWeb - Rodrigo Soares | www.driveweb.pt | 17/06/2025
 */

if (!defined('ABSPATH')) exit;

// Página Integrações
function agp_render_pagina_integracoes() {
    // Salvando os dados
    if (isset($_POST['agp_integracoes_nonce']) && wp_verify_nonce($_POST['agp_integracoes_nonce'], 'agp_salvar_integracoes')) {
        update_option('agp_google_api_key', sanitize_text_field($_POST['agp_google_api_key']));
        update_option('agp_whatsapp_token', sanitize_text_field($_POST['agp_whatsapp_token']));
        update_option('agp_stripe_secret_key', sanitize_text_field($_POST['agp_stripe_secret_key']));
        update_option('agp_ifthenpay_entidade', sanitize_text_field($_POST['agp_ifthenpay_entidade']));
        update_option('agp_ifthenpay_subentidade', sanitize_text_field($_POST['agp_ifthenpay_subentidade']));
        update_option('agp_ifthenpay_mbway_key', sanitize_text_field($_POST['agp_ifthenpay_mbway_key']));
        echo '<div class="updated notice"><p>Configurações salvas com sucesso.</p></div>';
    }

    // Carregar dados salvos
    $google_api = get_option('agp_google_api_key', '');
    $whatsapp_token = get_option('agp_whatsapp_token', '');
    $stripe_key = get_option('agp_stripe_secret_key', '');
    $entidade = get_option('agp_ifthenpay_entidade', '');
    $subentidade = get_option('agp_ifthenpay_subentidade', '');
    $mbway_key = get_option('agp_ifthenpay_mbway_key', '');

    echo '<div class="wrap">';
    echo '<h1>Integrações</h1>';

    echo '<form method="post">';
    wp_nonce_field('agp_salvar_integracoes', 'agp_integracoes_nonce');

    echo '<h2>Configurações de API</h2>';

    echo '<table class="form-table">';
    echo '<tr><th><label for="agp_google_api_key">Google Calendar API Key:</label></th>';
    echo '<td><input type="text" name="agp_google_api_key" value="' . esc_attr($google_api) . '" class="regular-text"></td></tr>';

    echo '<tr><th><label for="agp_whatsapp_token">WhatsApp API Token:</label></th>';
    echo '<td><input type="text" name="agp_whatsapp_token" value="' . esc_attr($whatsapp_token) . '" class="regular-text"></td></tr>';

    echo '<tr><th><label for="agp_stripe_secret_key">Stripe Secret Key:</label></th>';
    echo '<td><input type="text" name="agp_stripe_secret_key" value="' . esc_attr($stripe_key) . '" class="regular-text"></td></tr>';

    echo '<tr><th><label for="agp_ifthenpay_entidade">Entidade Multibanco:</label></th>';
    echo '<td><input type="text" name="agp_ifthenpay_entidade" value="' . esc_attr($entidade) . '" class="regular-text"></td></tr>';

    echo '<tr><th><label for="agp_ifthenpay_subentidade">Subentidade:</label></th>';
    echo '<td><input type="text" name="agp_ifthenpay_subentidade" value="' . esc_attr($subentidade) . '" class="regular-text"></td></tr>';

    echo '<tr><th><label for="agp_ifthenpay_mbway_key">MB WAY API Key:</label></th>';
    echo '<td><input type="text" name="agp_ifthenpay_mbway_key" value="' . esc_attr($mbway_key) . '" class="regular-text"></td></tr>';
    echo '</table>';

    submit_button('Salvar Configurações');

    echo '</form>';

    echo '<hr>';
    echo '<h2>Status</h2>';
    echo '<ul>';
    echo '<li><strong>WooCommerce:</strong> ' . (agp_tem_woocommerce() ? 'Ativo' : 'Não encontrado') . '</li>';
    echo '</ul>';
    echo '</div>';
}

// Verifica WooCommerce
function agp_tem_woocommerce() {
    return class_exists('WooCommerce');
}

// Link WhatsApp
function agp_gerar_link_whatsapp($telefone, $mensagem) {
    $tel = preg_replace('/[^0-9]/', '', $telefone);
    $msg = urlencode($mensagem);
    return "https://wa.me/{$tel}?text={$msg}";
}

// Link Google Calendar
function agp_gerar_link_google_calendar($titulo, $descricao, $inicio, $fim) {
    $start = date('Ymd\THis', strtotime($inicio));
    $end = date('Ymd\THis', strtotime($fim));
    $titulo = urlencode($titulo);
    $descricao = urlencode($descricao);
    return "https://www.google.com/calendar/render?action=TEMPLATE&text={$titulo}&details={$descricao}&dates={$start}/{$end}";
}

// Simula referência Multibanco
function agp_gerar_referencia_multibanco($id_pagamento, $valor) {
    $entidade = get_option('agp_ifthenpay_entidade');
    $subentidade = get_option('agp_ifthenpay_subentidade');

    if (!$entidade || !$subentidade) {
        return '⚠️ Entidade ou Subentidade não configuradas.';
    }

    $referencia = substr(md5($id_pagamento), 0, 9);
    $valor_formatado = number_format($valor, 2, ',', '');

    return "<strong>Entidade:</strong> $entidade<br>
            <strong>Referência:</strong> $referencia<br>
            <strong>Valor:</strong> €$valor_formatado";
}

// Simula envio MB WAY
function agp_enviar_mbway_pagamento($telefone, $valor, $descricao) {
    $mbway_key = get_option('agp_ifthenpay_mbway_key');

    if (!$mbway_key) {
        return '⚠️ MB WAY API Key não configurada.';
    }

    // Simulação: aqui entraria chamada real para IfThenPay
    return "✅ Pagamento MB WAY enviado para <strong>$telefone</strong> no valor de €" . number_format($valor, 2) . "<br><em>Descrição:</em> $descricao";
}

// Altera nome do remetente do wp_mail()
add_filter('wp_mail_from_name', function () {
    return 'Agendador Portugal';
});
