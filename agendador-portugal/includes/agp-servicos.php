<?php
/**
 * Serviços - Módulo do Plugin Gestão de Serviços
 * Autor: DriveWeb - Rodrigo Soares | www.driveweb.pt
 * Data: 18/06/2025
 */
if (!defined('ABSPATH')) exit;

//
// REGISTRO DO CUSTOM POST TYPE "Serviços"
//
add_action('init', function () {
    register_post_type('agp_servico', [
        'labels' => [
            'name' => 'Serviços',
            'singular_name' => 'Serviço',
            'add_new' => 'Adicionar Novo',
            'add_new_item' => 'Novo Serviço',
            'edit_item' => 'Editar Serviço',
            'view_item' => 'Ver Serviço',
            'search_items' => 'Buscar Serviços',
            'not_found' => 'Nenhum serviço encontrado',
            'not_found_in_trash' => 'Nenhum serviço na lixeira'
        ],
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-hammer',
        'supports' => ['title'],
        'rewrite' => false,
        'show_in_menu' => false
    ]);
});

//
// METABOX - INFORMAÇÕES DO SERVIÇO
//
add_action('add_meta_boxes', function () {
    add_meta_box(
        'agp_servico_info',
        'Informações do Serviço',
        'agp_metabox_servico',
        'agp_servico',
        'normal',
        'default'
    );
});

function agp_metabox_servico($post) {
    $descricao = get_post_meta($post->ID, '_agp_descricao', true);
    $duracao = get_post_meta($post->ID, '_agp_duracao', true);
    $preco = get_post_meta($post->ID, '_agp_preco', true);
    $categoria = get_post_meta($post->ID, '_agp_categoria', true);

    wp_nonce_field('agp_save_servico', 'agp_servico_nonce');
    ?>
    <p><label for="agp_descricao">Descrição:</label><br>
        <textarea name="agp_descricao" class="widefat"><?php echo esc_textarea($descricao); ?></textarea></p>

    <p><label for="agp_duracao">Duração (minutos):</label><br>
        <input type="number" name="agp_duracao" value="<?php echo esc_attr($duracao); ?>" class="widefat" min="0" /></p>

    <p><label for="agp_preco">Preço (€):</label><br>
        <input type="number" step="0.01" name="agp_preco" value="<?php echo esc_attr($preco); ?>" class="widefat" min="0" /></p>

    <p><label for="agp_categoria">Categoria:</label><br>
        <input type="text" name="agp_categoria" value="<?php echo esc_attr($categoria); ?>" class="widefat" /></p>
    <?php
}

//
// SALVAMENTO DOS METADADOS
//
add_action('save_post_agp_servico', function ($post_id) {
    if (!isset($_POST['agp_servico_nonce']) || !wp_verify_nonce($_POST['agp_servico_nonce'], 'agp_save_servico')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['agp_descricao'])) {
        update_post_meta($post_id, '_agp_descricao', sanitize_textarea_field($_POST['agp_descricao']));
    }

    if (isset($_POST['agp_duracao'])) {
        $duracao = intval($_POST['agp_duracao']);
        update_post_meta($post_id, '_agp_duracao', max(0, $duracao));
    }

    if (isset($_POST['agp_preco'])) {
        $preco = floatval($_POST['agp_preco']);
        update_post_meta($post_id, '_agp_preco', max(0, $preco));
    }

    if (isset($_POST['agp_categoria'])) {
        update_post_meta($post_id, '_agp_categoria', sanitize_text_field($_POST['agp_categoria']));
    }
});
