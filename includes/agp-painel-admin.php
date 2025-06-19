<?php
/**
 * Painel Administrativo - Dashboard principal com visão geral
 * DriveWeb - Rodrigo Soares | www.driveweb.pt | 17/06/2025
 */

if (!defined('ABSPATH')) exit;

/**
 * Renderiza o painel administrativo
 */
function agp_render_painel() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Dashboard - Agendador Portugal', 'agendador-portugal'); ?></h1>

        <div class="agp-dashboard-metricas">
            <ul>
                <li><strong><?php echo agp_total_agendamentos(); ?></strong> Agendamentos</li>
                <li><strong><?php echo agp_total_clientes(); ?></strong> Clientes</li>
                <li><strong><?php echo agp_total_profissionais(); ?></strong> Profissionais</li>
                <li><strong><?php echo agp_total_receita(); ?></strong> Receita (€)</li>
            </ul>
        </div>

        <p><?php esc_html_e('Use o menu lateral para gerir os módulos.', 'agendador-portugal'); ?></p>
    </div>
    <?php
}
