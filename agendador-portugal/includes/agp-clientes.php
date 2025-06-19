<?php
/**
 * Módulo Clientes - Gestão de Serviços
 * Autor: DriveWeb - Rodrigo Soares | www.driveweb.pt
 * Data: 18/06/2025
 */

if (!defined('ABSPATH')) exit;

//
// REGISTRA O CUSTOM POST TYPE CLIENTES
//
add_action('init', function () {
    register_post_type('agp_cliente', [
        'labels' => [
            'name' => 'Clientes',
            'singular_name' => 'Cliente',
            'add_new' => 'Adicionar Novo',
            'add_new_item' => 'Adicionar Novo Cliente',
            'edit_item' => 'Editar Cliente',
            'new_item' => 'Novo Cliente',
            'view_item' => 'Ver Cliente',
            'search_items' => 'Buscar Clientes',
            'not_found' => 'Nenhum cliente encontrado',
            'not_found_in_trash' => 'Nenhum cliente na lixeira'
        ],
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-id',
        'supports' => ['title'],
        'rewrite' => false,
        'show_in_menu' => false // será adicionado via submenu
    ]);
});

//
// ADICIONA METABOX DE INFORMAÇÕES DO CLIENTE
//
add_action('add_meta_boxes', function () {
    add_meta_box(
        'agp_cliente_info',
        'Informações do Cliente',
        'agp_render_cliente_metabox',
        'agp_cliente',
        'normal',
        'default'
    );

    // Metabox de serviços associados
    add_meta_box(
        'agp_cliente_servicos',
        'Serviços Associados',
        'agp_render_cliente_servicos_metabox',
        'agp_cliente',
        'normal',
        'default'
    );
});

function agp_render_cliente_metabox($post) {
    $campos = [
        'telemovel' => 'Telemóvel',
        'email' => 'E-mail',
        'endereco' => 'Endereço',
        'aniversario' => 'Data de Aniversário',
        'preferencias' => 'Preferências'
    ];

    wp_nonce_field('agp_save_cliente', 'agp_cliente_nonce');

    foreach ($campos as $campo => $label) {
        $meta = get_post_meta($post->ID, "_agp_$campo", true);

        echo "<p><label for='agp_{$campo}'>{$label}:</label><br>";

        if ($campo === 'endereco' || $campo === 'preferencias') {
            echo "<textarea name='agp_{$campo}' class='widefat'>" . esc_textarea($meta) . "</textarea></p>";
        } elseif ($campo === 'aniversario') {
            echo "<input type='date' name='agp_{$campo}' value='" . esc_attr($meta) . "' class='widefat' /></p>";
        } else {
            $type = ($campo === 'email') ? 'email' : 'text';
            echo "<input type='{$type}' name='agp_{$campo}' value='" . esc_attr($meta) . "' class='widefat' /></p>";
        }
    }
}

//
// NOVA METABOX: Serviços Associados
//
function agp_render_cliente_servicos_metabox($post) {
    $servicos_selecionados = get_post_meta($post->ID, '_agp_servicos_cliente', true);
    if (!is_array($servicos_selecionados)) $servicos_selecionados = [];

    $servicos = get_posts([
        'post_type' => 'agp_servico',
        'numberposts' => -1
    ]);

    wp_nonce_field('agp_salvar_cliente_servicos', 'agp_cliente_servicos_nonce');

    echo '<p>Selecione os serviços que este cliente utiliza:</p>';
    echo '<ul style="margin-left: 0;">';

    foreach ($servicos as $servico) {
        $checked = in_array($servico->ID, $servicos_selecionados) ? 'checked' : '';
        echo '<li><label><input type="checkbox" name="agp_servicos_cliente[]" value="' . $servico->ID . '" ' . $checked . '> ' . esc_html($servico->post_title) . '</label></li>';
    }

    echo '</ul>';
}

//
// SALVA OS DADOS DO CLIENTE
//
add_action('save_post_agp_cliente', function ($post_id) {
    if (!isset($_POST['agp_cliente_nonce']) || !wp_verify_nonce($_POST['agp_cliente_nonce'], 'agp_save_cliente')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $campos = [
        'telemovel' => 'sanitize_text_field',
        'email' => 'sanitize_email',
        'endereco' => 'sanitize_textarea_field',
        'aniversario' => 'sanitize_text_field',
        'preferencias' => 'sanitize_textarea_field'
    ];

    foreach ($campos as $campo => $sanitizador) {
        if (isset($_POST["agp_{$campo}"])) {
            update_post_meta($post_id, "_agp_{$campo}", call_user_func($sanitizador, $_POST["agp_{$campo}"]));
        }
    }

    // SALVA SERVIÇOS VINCULADOS
    if (isset($_POST['agp_cliente_servicos_nonce']) && wp_verify_nonce($_POST['agp_cliente_servicos_nonce'], 'agp_salvar_cliente_servicos')) {
        $servicos = isset($_POST['agp_servicos_cliente']) ? array_map('intval', $_POST['agp_servicos_cliente']) : [];
        update_post_meta($post_id, '_agp_servicos_cliente', $servicos);
    }
});
