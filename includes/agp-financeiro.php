<?php
/**
 * Módulo Financeiro - Plugin Gestão de Serviços
 * @author DriveWeb
 * @date 18/06/2025
 */

if (!defined('ABSPATH')) exit;

// Registrar CPT Financeiro
register_post_type('agp_financeiro', [
    'labels' => [
        'name' => 'Financeiro',
        'singular_name' => 'Lançamento',
        'add_new' => 'Novo Lançamento',
        'add_new_item' => 'Adicionar Novo Lançamento',
        'edit_item' => 'Editar Lançamento',
        'new_item' => 'Novo Lançamento',
        'view_item' => 'Ver Lançamento',
        'not_found' => 'Nenhum lançamento encontrado',
        'not_found_in_trash' => 'Nenhum lançamento na lixeira',
    ],
    'public' => false,
    'show_ui' => true,
    'menu_icon' => 'dashicons-money',
    'supports' => ['title'],
    'rewrite' => false
]);


// Adicionar metabox
add_action('add_meta_boxes', function () {
    add_meta_box('agp_financeiro_meta', 'Detalhes Financeiros', 'agp_render_financeiro_metabox', 'agp_financeiro', 'normal', 'high');
});

// Renderizar campos da metabox
function agp_render_financeiro_metabox($post) {
    $tipo       = get_post_meta($post->ID, '_agp_tipo', true);
    $valor      = get_post_meta($post->ID, '_agp_valor', true);
    $status     = get_post_meta($post->ID, '_agp_status', true);
    $vencimento = get_post_meta($post->ID, '_agp_vencimento', true);
    $cliente_id = get_post_meta($post->ID, '_agp_cliente', true);
    $servico_id = get_post_meta($post->ID, '_agp_servico', true);
    $obs        = get_post_meta($post->ID, '_agp_obs', true);

    wp_nonce_field('agp_save_financeiro', 'agp_financeiro_nonce');

    $clientes  = get_posts(['post_type' => 'agp_cliente', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);
    $servicos  = get_posts(['post_type' => 'agp_servico', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);
    ?>

    <p>
        <label><strong>Tipo:</strong></label><br>
        <select name="agp_tipo" class="widefat" required>
            <option value="receita" <?php selected($tipo, 'receita'); ?>>Receita</option>
            <option value="despesa" <?php selected($tipo, 'despesa'); ?>>Despesa</option>
        </select>
    </p>

    <p>
        <label><strong>Valor (€):</strong></label><br>
        <input type="number" step="0.01" min="0" name="agp_valor" value="<?php echo esc_attr($valor); ?>" class="widefat" required />
    </p>

    <p>
        <label><strong>Status:</strong></label><br>
        <select name="agp_status" class="widefat">
            <option value="em_aberto" <?php selected($status, 'em_aberto'); ?>>Em Aberto</option>
            <option value="pago" <?php selected($status, 'pago'); ?>>Pago</option>
            <option value="cancelado" <?php selected($status, 'cancelado'); ?>>Cancelado</option>
        </select>
    </p>

    <p>
        <label><strong>Data de Vencimento:</strong></label><br>
        <input type="date" name="agp_vencimento" value="<?php echo esc_attr($vencimento); ?>" class="widefat" />
    </p>

    <p>
        <label><strong>Cliente:</strong></label><br>
        <select name="agp_cliente" class="widefat" required>
            <option value="">Selecione um cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo esc_attr($cliente->ID); ?>" <?php selected($cliente_id, $cliente->ID); ?>>
                    <?php echo esc_html($cliente->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label><strong>Serviço/Pacote:</strong></label><br>
        <select name="agp_servico" class="widefat">
            <option value="">Nenhum</option>
            <?php foreach ($servicos as $servico): ?>
                <option value="<?php echo esc_attr($servico->ID); ?>" <?php selected($servico_id, $servico->ID); ?>>
                    <?php echo esc_html($servico->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label><strong>Observações:</strong></label><br>
        <textarea name="agp_obs" class="widefat" rows="4"><?php echo esc_textarea($obs); ?></textarea>
    </p>
    <?php
}

// Salvar dados do lançamento financeiro
add_action('save_post_agp_financeiro', function ($post_id) {
    if (!isset($_POST['agp_financeiro_nonce']) || !wp_verify_nonce($_POST['agp_financeiro_nonce'], 'agp_save_financeiro')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $campos = [
        '_agp_tipo'       => sanitize_text_field($_POST['agp_tipo'] ?? ''),
        '_agp_valor'      => floatval($_POST['agp_valor'] ?? 0),
        '_agp_status'     => sanitize_text_field($_POST['agp_status'] ?? ''),
        '_agp_vencimento' => sanitize_text_field($_POST['agp_vencimento'] ?? ''),
        '_agp_cliente'    => intval($_POST['agp_cliente'] ?? 0),
        '_agp_servico'    => intval($_POST['agp_servico'] ?? 0),
        '_agp_obs'        => sanitize_textarea_field($_POST['agp_obs'] ?? '')
    ];

    foreach ($campos as $meta_key => $value) {
        update_post_meta($post_id, $meta_key, $value);
    }
});
