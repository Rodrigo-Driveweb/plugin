<?php
/**
 * Funções auxiliares (helpers) reutilizáveis no plugin
 * DriveWeb - Rodrigo Soares | www.driveweb.pt | 17/06/2025
 */

if (!defined('ABSPATH')) exit;

// Retorna total de agendamentos
function agp_total_agendamentos() {
    return wp_count_posts('agp_agendamento')->publish ?? 0;
}

// Retorna total de clientes
function agp_total_clientes() {
    return wp_count_posts('agp_cliente')->publish ?? 0;
}

// Retorna total de profissionais
function agp_total_profissionais() {
    return wp_count_posts('agp_profissional')->publish ?? 0;
}

// Calcula receita total a partir dos agendamentos pagos
function agp_total_receita() {
    global $wpdb;
    $table = $wpdb->prefix . 'postmeta';

    $result = $wpdb->get_var($wpdb->prepare("
        SELECT SUM(meta_value)
        FROM $table
        WHERE meta_key = %s
    ", '_agp_pagamento_valor'));

    return number_format((float)$result, 2, ',', '.');
}

// Oculta detalhes do box "Publicar" em post types do plugin no admin
add_action('admin_head-post.php', 'agp_ocultar_infos_publicar');
add_action('admin_head-post-new.php', 'agp_ocultar_infos_publicar');

function agp_ocultar_infos_publicar() {
    global $post;

    $tipos_ocultar = [
        'agp_agendamento',
        'agp_cliente',
        'agp_profissional',
        'agp_servico',
        'agp_financeiro',
        'agp_avaliacao'
        // Adicione mais se criar novos post types
    ];

    if (!$post || !in_array($post->post_type, $tipos_ocultar)) return;
    ?>
    <style>
        #submitdiv .misc-pub-section,
        #submitdiv .edit-visibility,
        #submitdiv .edit-timestamp,
        #submitdiv .misc-pub-curtime,
        #submitdiv .curtime {
            display: none !important;
        }
    </style>
    <?php
}

// Enfileira o script JS do frontend e passa as variáveis ajax para ele
add_action('wp_enqueue_scripts', function () {
    if (!defined('AGP_URL')) return;

    wp_enqueue_script(
        'agp-frontend-js',
        AGP_URL . 'js/a952f3a2-f43d-43c8-a151-9309e16fdff5.js',
        ['jquery'],
        '1.0',
        true
    );

    wp_localize_script('agp-frontend-js', 'agpAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('agp_front_agendamento'),
    ]);
});

// Shortcode para exibir formulário de agendamento simples
add_shortcode('agp_form_agendamento', function () {
    ob_start();

    $profissionais = get_posts(['post_type' => 'agp_profissional', 'numberposts' => -1]);
    $servicos = get_posts(['post_type' => 'agp_servico', 'numberposts' => -1]);
    ?>

    <form id="agp-form-agendamento">
        <p><label>Nome:</label><br>
            <input type="text" name="nome" required></p>

        <p><label>Telefone:</label><br>
            <input type="text" name="telefone" required></p>

        <p><label>Profissional:</label><br>
            <select name="profissional" required>
                <option value="0">Selecione profissional</option>
                <?php foreach ($profissionais as $profissional): ?>
                    <option value="<?php echo esc_attr($profissional->ID); ?>">
                        <?php echo esc_html($profissional->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p><label>Serviço:</label><br>
            <select name="servico" required>
                <option value="">Selecione serviço</option>
                <?php foreach ($servicos as $servico): ?>
                    <option value="<?php echo esc_attr($servico->post_title); ?>">
                        <?php echo esc_html($servico->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p><label>Data:</label><br>
            <input type="date" name="data" required></p>

        <p><label>Hora:</label><br>
            <input type="time" name="hora" required></p>

        <p><button type="submit">Agendar</button></p>
    </form>

    <div id="agp-msg-retorno" style="margin-top:15px;"></div>

    <?php
    return ob_get_clean();
});
