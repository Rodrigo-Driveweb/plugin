<?php
if (!defined('ABSPATH')) exit;

// Registrar o CPT Avaliação
add_action('init', function () {
    register_post_type('agp_avaliacao', [
        'labels' => [
            'name' => 'Avaliações',
            'singular_name' => 'Avaliação'
        ],
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-star-half',
        'supports' => ['title'],
        'rewrite' => false
    ]);
});

// Metabox
add_action('add_meta_boxes', function () {
    add_meta_box('agp_aval_info', 'Feedback do Cliente', 'agp_render_avaliacao_metabox', 'agp_avaliacao', 'normal', 'default');
});

function agp_render_avaliacao_metabox($post) {
    $cliente = get_post_meta($post->ID, '_agp_cliente', true);
    $profissional = get_post_meta($post->ID, '_agp_profissional', true);
    $nota = get_post_meta($post->ID, '_agp_nota', true);
    $comentario = get_post_meta($post->ID, '_agp_comentario', true);

    wp_nonce_field('agp_save_avaliacao', 'agp_avaliacao_nonce');
    ?>
    <p><label>Cliente:</label><br>
        <input type="text" name="agp_cliente" value="<?php echo esc_attr($cliente); ?>" class="widefat" /></p>

    <p><label>Profissional:</label><br>
        <input type="text" name="agp_profissional" value="<?php echo esc_attr($profissional); ?>" class="widefat" /></p>

    <p><label>Nota (1 a 5):</label><br>
        <select name="agp_nota" class="widefat">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?php echo $i; ?>" <?php selected($nota, $i); ?>>
                    <?php echo $i; ?> estrela<?php echo $i > 1 ? 's' : ''; ?>
                </option>
            <?php endfor; ?>
        </select></p>

    <p><label>Comentário:</label><br>
        <textarea name="agp_comentario" class="widefat"><?php echo esc_textarea($comentario); ?></textarea></p>
    <?php
}

// Salvar avaliação
add_action('save_post_agp_avaliacao', function ($post_id) {
    if (!isset($_POST['agp_avaliacao_nonce']) || !wp_verify_nonce($_POST['agp_avaliacao_nonce'], 'agp_save_avaliacao')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    update_post_meta($post_id, '_agp_cliente', sanitize_text_field($_POST['agp_cliente']));
    update_post_meta($post_id, '_agp_profissional', sanitize_text_field($_POST['agp_profissional']));
    update_post_meta($post_id, '_agp_nota', intval($_POST['agp_nota']));
    update_post_meta($post_id, '_agp_comentario', sanitize_textarea_field($_POST['agp_comentario']));
});
