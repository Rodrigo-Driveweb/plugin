<?php
if (!defined('ABSPATH')) exit;

/**
 * Criar papéis personalizados ao ativar o plugin
 */
register_activation_hook(AGP_PATH . 'agendador-portugal.php', function () {
    add_role('agp_profissional', 'Profissional', [
        'read' => true,
        'edit_posts' => false
    ]);

    add_role('agp_recepcionista', 'Recepcionista', [
        'read' => true,
        'edit_agp_agendamento' => true,
        'edit_agp_cliente' => true,
        'edit_agp_financeiro' => true
    ]);

    add_role('agp_cliente', 'Cliente', [
        'read' => true
    ]);
});

/**
 * Remover papéis ao desativar o plugin
 */
register_deactivation_hook(AGP_PATH . 'agendador-portugal.php', function () {
    remove_role('agp_profissional');
    remove_role('agp_recepcionista');
    remove_role('agp_cliente');
});

/**
 * Ocultar menus com base no perfil do usuário
 */
add_action('admin_menu', function () {
    if (!current_user_can('administrator')) {
        remove_menu_page('tools.php');
        remove_menu_page('options-general.php');
    }

    if (current_user_can('agp_profissional')) {
        remove_menu_page('edit.php?post_type=agp_cliente');
        remove_menu_page('edit.php?post_type=agp_financeiro');
        remove_menu_page('tools.php');
    }

    if (current_user_can('agp_cliente')) {
        remove_menu_page('edit.php?post_type=agp_profissional');
        remove_menu_page('edit.php?post_type=agp_financeiro');
        remove_menu_page('tools.php');
    }
});
