<?php
/**
 * Módulo Relatórios - Plugin Gestão de Serviços
 * Autor: DriveWeb - Rodrigo Soares | www.driveweb.pt | 17/06/2025
 */

if (!defined('ABSPATH')) exit;

// Adicionar submenu Relatórios
add_action('admin_menu', function () {
    add_submenu_page(
        'agp_painel',
        'Relatórios',
        'Relatórios',
        'manage_options',
        'agp_relatorios',
        'agp_render_pagina_relatorios'
    );
});

// Renderizar página de relatórios
function agp_render_pagina_relatorios() {
    $total_agendamentos = wp_count_posts('agp_agendamento')->publish ?? 0;
    $total_clientes = wp_count_posts('agp_cliente')->publish ?? 0;
    $total_profissionais = wp_count_posts('agp_profissional')->publish ?? 0;
    $total_servicos = wp_count_posts('agp_servico')->publish ?? 0;
    $aniversariantes = agp_clientes_aniversariantes_do_mes();

    echo '<div class="wrap">';
    echo '<h1>Relatórios</h1>';
    echo '<p><strong>Agendamentos:</strong> ' . $total_agendamentos . '</p>';
    echo '<p><strong>Clientes:</strong> ' . $total_clientes . '</p>';
    echo '<p><strong>Profissionais:</strong> ' . $total_profissionais . '</p>';
    echo '<p><strong>Serviços:</strong> ' . $total_servicos . '</p>';

    echo '<h2>Aniversariantes do Mês</h2>';
    if ($aniversariantes) {
        echo '<ul>';
        foreach ($aniversariantes as $c) {
            echo '<li>' . esc_html($c['nome']) . ' – ' . esc_html($c['data']) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>Nenhum aniversariante neste mês.</p>';
    }

    // Filtros e formulário
    echo '<hr><h2>Gerar Relatório</h2>';
    echo '<form method="post">';
    echo '<p><label>Tipo de Relatório:</label><br>';
    echo '<select name="tipo_relatorio" required>';
    echo '<option value="atendimentos">Atendimentos</option>';
    echo '<option value="financeiro">Financeiro</option>';
    echo '</select></p>';

    echo '<p><label>Período:</label><br>';
    echo '<input type="date" name="data_inicio" /> até ';
    echo '<input type="date" name="data_fim" /></p>';

    echo '<p><label>Cliente:</label><br><select name="cliente_id"><option value="">Todos</option>';
    $clientes = get_posts(['post_type' => 'agp_cliente', 'posts_per_page' => -1]);
    foreach ($clientes as $c) {
        echo '<option value="' . esc_attr($c->ID) . '">' . esc_html($c->post_title) . '</option>';
    }
    echo '</select></p>';

    echo '<p><label>Profissional:</label><br><select name="profissional_id"><option value="">Todos</option>';
    $profs = get_posts(['post_type' => 'agp_profissional', 'posts_per_page' => -1]);
    foreach ($profs as $p) {
        echo '<option value="' . esc_attr($p->ID) . '">' . esc_html($p->post_title) . '</option>';
    }
    echo '</select></p>';

    echo '<p><label>Serviço:</label><br><select name="servico"><option value="">Todos</option>';
    $servicos = get_terms(['taxonomy' => 'agp_servico', 'hide_empty' => false]);
    foreach ($servicos as $s) {
        echo '<option value="' . esc_attr($s->name) . '">' . esc_html($s->name) . '</option>';
    }
    echo '</select></p>';

    echo '<p><input type="submit" class="button button-primary" value="Gerar Relatório" /></p>';
    echo '</form>';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tipo = $_POST['tipo_relatorio'] ?? '';
        $inicio = $_POST['data_inicio'] ?: '2000-01-01';
        $fim = $_POST['data_fim'] ?: '2100-12-31';
        $cliente_id = $_POST['cliente_id'] ?? '';
        $profissional_id = $_POST['profissional_id'] ?? '';
        $servico = $_POST['servico'] ?? '';

        if ($tipo === 'atendimentos') {
            agp_exibir_relatorio_atendimentos($inicio, $fim, $cliente_id, $profissional_id, $servico);
        } elseif ($tipo === 'financeiro') {
            agp_exibir_relatorio_financeiro($inicio, $fim, $cliente_id);
        }
    }

    echo '</div>';
}

// Relatório de atendimentos
function agp_exibir_relatorio_atendimentos($inicio, $fim, $cliente_id, $prof_id, $servico) {
    $meta_query = [
        [
            'key' => '_agp_data',
            'value' => [$inicio, $fim],
            'compare' => 'BETWEEN',
            'type' => 'DATE'
        ]
    ];
    if ($cliente_id) {
        $meta_query[] = ['key' => '_agp_cliente', 'value' => $cliente_id];
    }
    if ($prof_id) {
        $meta_query[] = ['key' => '_agp_profissional', 'value' => $prof_id];
    }
    if ($servico) {
        $meta_query[] = ['key' => '_agp_servico', 'value' => $servico];
    }

    $args = [
        'post_type' => 'agp_agendamento',
        'posts_per_page' => -1,
        'meta_query' => $meta_query
    ];

    $query = new WP_Query($args);

    echo '<h3>Relatório de Atendimentos</h3>';
    echo '<table class="widefat"><thead><tr><th>Data</th><th>Cliente</th><th>Profissional</th><th>Serviço</th><th>Status</th></tr></thead><tbody>';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $id = get_the_ID();
            echo '<tr>';
            echo '<td>' . esc_html(get_post_meta($id, '_agp_data', true)) . '</td>';
            echo '<td>' . get_the_title((int) get_post_meta($id, '_agp_cliente', true)) . '</td>';
            echo '<td>' . get_the_title((int) get_post_meta($id, '_agp_profissional', true)) . '</td>';
            echo '<td>' . esc_html(get_post_meta($id, '_agp_servico', true)) . '</td>';
            echo '<td>' . esc_html(get_post_meta($id, '_agp_status', true)) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5">Nenhum atendimento encontrado.</td></tr>';
    }

    echo '</tbody></table>';
    wp_reset_postdata();
}

// Relatório financeiro
function agp_exibir_relatorio_financeiro($inicio, $fim, $cliente_id = '') {
    $meta_query = [
        [
            'key' => '_agp_vencimento',
            'value' => [$inicio, $fim],
            'compare' => 'BETWEEN',
            'type' => 'DATE'
        ]
    ];
    if ($cliente_id) {
        $meta_query[] = ['key' => '_agp_cliente', 'value' => $cliente_id];
    }

    $args = [
        'post_type' => 'agp_financeiro',
        'posts_per_page' => -1,
        'meta_query' => $meta_query
    ];

    $query = new WP_Query($args);

    echo '<h3>Relatório Financeiro</h3>';
    echo '<table class="widefat"><thead><tr><th>Tipo</th><th>Valor (€)</th><th>Status</th><th>Cliente</th><th>Vencimento</th></tr></thead><tbody>';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $id = get_the_ID();
            echo '<tr>';
            echo '<td>' . esc_html(get_post_meta($id, '_agp_tipo', true)) . '</td>';
            echo '<td>€' . number_format((float)get_post_meta($id, '_agp_valor', true), 2, ',', '.') . '</td>';
            echo '<td>' . esc_html(get_post_meta($id, '_agp_status', true)) . '</td>';
            echo '<td>' . get_the_title((int) get_post_meta($id, '_agp_cliente', true)) . '</td>';
            echo '<td>' . esc_html(get_post_meta($id, '_agp_vencimento', true)) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5">Nenhum lançamento financeiro encontrado.</td></tr>';
    }

    echo '</tbody></table>';
    wp_reset_postdata();
}

// Função auxiliar para aniversariantes
function agp_clientes_aniversariantes_do_mes() {
    $mes_atual = date('m');
    $clientes = get_posts([
        'post_type' => 'agp_cliente',
        'posts_per_page' => -1
    ]);

    $aniversariantes = [];
    foreach ($clientes as $cliente) {
        $data = get_post_meta($cliente->ID, '_agp_aniversario', true);
        if ($data && substr($data, 5, 2) === $mes_atual) {
            $aniversariantes[] = [
                'nome' => $cliente->post_title,
                'data' => date('d/m', strtotime($data))
            ];
        }
    }
    return $aniversariantes;
}
