<?php
/**
 * Plugin Name: Agendador Portugal
 * Plugin URI: https://driveweb.pt
 * Description: Plugin de agendamento modular para negócios em Portugal. Compatível com qualquer tema.
 * Version: 1.0.0
 * Author: DriveWeb | Rodrigo Soares
 * Author URI: https://driveweb.pt
 * Text Domain: agendador-portugal
 * Domain Path: /languages
 * License: GPL2
 */

if (!defined('ABSPATH')) exit;

define('AGP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AGP_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once AGP_PLUGIN_DIR . 'includes/functions.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-painel-admin.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-ajax.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-servicos.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-profissionais.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-clientes.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-agendamentos.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-painel.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-financeiro.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-permissoes.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-notificacoes.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-relatorios.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-shortcodes.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-calendario.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-calendario-front.php';
require_once AGP_PLUGIN_DIR . 'includes/agp_salvar_agendamento.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-funcoes-gerais.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-helpers.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-integracoes.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-avaliacoes.php';
require_once AGP_PLUGIN_DIR . 'includes/agp-controle-permissoes.php';

// Inicializar plugin
function agp_init() {
    // Código de inicialização, se necessário
}
add_action('init', 'agp_init');

?>
