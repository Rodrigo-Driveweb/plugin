<?php
/*
Plugin Name: Agendador Portugal
Plugin URI: https://driveweb.pt
Description: Plugin de agendamento modular para negócios em Portugal. Compatível com qualquer tema, incluindo Hello (Elementor).
Version: 1.0.0
Author: DriveWeb | Rodrigo Soares
Author URI: https://driveweb.pt
Text Domain: agendador-portugal
Domain Path: /languages
License: GPL2
*/

// Impede acesso direto ao arquivo
if (!defined('ABSPATH')) exit;

// Créditos
// DriveWeb - Rodrigo Soares | www.driveweb.pt | 17/06/2025

// Define caminhos e URLs (definidos apenas uma vez)
if (!defined('AGP_PATH')) define('AGP_PATH', plugin_dir_path(__FILE__));
if (!defined('AGP_URL')) define('AGP_URL', plugin_dir_url(__FILE__));

// Lista dos arquivos de módulos incluídos
$agp_modules = [
    'includes/agp-agendamentos.php',
    'includes/agp-clientes.php',
    'includes/agp-profissionais.php',
    'includes/agp-servicos.php',
    'includes/agp-financeiro.php',
    'includes/agp-calendario.php',
    'includes/agp-notificacoes.php',
    'includes/agp-relatorios.php',
    'includes/agp-painel-admin.php',
    'includes/agp-avaliacoes.php',
    'includes/agp-integracoes.php',
    'includes/agp-permissoes.php',
    'includes/agp-funcoes-gerais.php',
    'includes/agp-calendario-front.php', // frontend calendar para shortcode
    'includes/agp-ajax.php' // ajax handlers
];

// Inclui todos os arquivos listados se existirem
foreach ($agp_modules as $file) {
    $path = AGP_PATH . $file;
    if (file_exists($path)) {
        require_once $path;
    }
}

// Hooks de ativação e desativação do plugin
register_activation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

// Criação do menu no admin do WordPress
add_action('admin_menu', function () {
    add_menu_page(
        'Agendador Portugal',
        'Agendador',
        'manage_options',
        'agp_painel',
        '',
        'dashicons-calendar-alt',
        2
    );

    add_submenu_page('agp_painel', 'Dashboard',      'Dashboard',      'manage_options', 'agp_painel', 'agp_render_painel');
    add_submenu_page('agp_painel', 'Calendário',     'Calendário',     'manage_options', 'agp_calendario', 'agp_render_calendario_page');
    add_submenu_page('agp_painel', 'Agendamentos',   'Agendamentos',   'edit_posts',     'edit.php?post_type=agp_agendamento');
    add_submenu_page('agp_painel', 'Clientes',       'Clientes',       'edit_posts',     'edit.php?post_type=agp_cliente');
    add_submenu_page('agp_painel', 'Profissionais',  'Profissionais',  'edit_posts',     'edit.php?post_type=agp_profissional');
    add_submenu_page('agp_painel', 'Serviços',       'Serviços',       'edit_posts',     'edit.php?post_type=agp_servico');
    add_submenu_page('agp_painel', 'Financeiro',     'Financeiro',     'edit_posts',     'edit.php?post_type=agp_financeiro');
    add_submenu_page('agp_painel', 'Avaliações',     'Avaliações',     'edit_posts',     'edit.php?post_type=agp_avaliacao');
    add_submenu_page('agp_painel', 'Notificações',   'Notificações',   'manage_options', 'agp_notificacoes', 'agp_notificacoes_painel');
    add_submenu_page('agp_painel', 'Relatórios',     'Relatórios',     'manage_options', 'agp_relatorios', 'agp_render_pagina_relatorios');
    add_submenu_page('agp_painel', 'Integrações',    'Integrações',    'manage_options', 'agp_integracoes', 'agp_render_pagina_integracoes');
});

// Enfileiramento de scripts e estilos no frontend (site público)
add_action('wp_enqueue_scripts', function () {
    // FullCalendar CSS e JS
    wp_enqueue_style('fullcalendar-style', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css');

    wp_enqueue_script('fullcalendar-core', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js', [], null, true);
    wp_enqueue_script('fullcalendar-ptbr', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.global.min.js', ['fullcalendar-core'], null, true);

    // Script principal do frontend que fará chamadas AJAX
    wp_enqueue_script('agp-frontend-js', AGP_URL . 'js/a952f3a2-f43d-43c8-a151-9309e16fdff5.js', ['jquery'], '1.0', true);

    // Passa ajax_url e nonce para o JS, com a variável JS "agp_vars"
    wp_localize_script('agp-frontend-js', 'agp_vars', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('agp_front_agendamento'),
    ]);
});
