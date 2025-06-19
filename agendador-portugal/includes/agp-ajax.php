<?php
/**
 * Agendamentos - Salvamento via AJAX
 * Parte do Plugin Gestão de Serviços
 * @author DriveWeb - Rodrigo Soares | www.driveweb.pt
 * @copyright 2025 DriveWeb. Todos os direitos reservados.
 * @date 18/06/2025
 */

if (!defined('ABSPATH')) exit;

// Registra as ações AJAX para usuários logados e visitantes
add_action('wp_ajax_nopriv_agp_salvar_agendamento', 'agp_salvar_agendamento');
add_action('wp_ajax_agp_salvar_agendamento', 'agp_salvar_agendamento');

/**
 * Função que salva o agendamento enviado via formulário do front-end
 */
function agp_salvar_agendamento() {
    // Verifica nonce para segurança
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'agp_front_agendamento')) {
        wp_send_json_error(['message' => 'Falha na verificação de segurança.']);
    }

    // Sanitiza os dados recebidos
    $nome           = sanitize_text_field($_POST['nome'] ?? '');
    $telefone       = sanitize_text_field($_POST['telefone'] ?? '');
    $profissional_id = intval($_POST['profissional'] ?? 0);
    $servico_id     = intval($_POST['servico'] ?? 0);
    $data           = sanitize_text_field($_POST['data'] ?? '');
    $hora           = sanitize_text_field($_POST['hora'] ?? '');

    // Validação dos campos obrigatórios
    if (empty($nome) || empty($telefone) || !$profissional_id || !$servico_id || empty($data) || empty($hora)) {
        wp_send_json_error(['message' => 'Preencha todos os campos obrigatórios corretamente.']);
    }

    // Validação se os IDs de profissional e serviço existem
    $profissional_post = get_post($profissional_id);
    if (!$profissional_post || $profissional_post->post_type !== 'agp_profissional') {
        wp_send_json_error(['message' => 'Profissional inválido.']);
    }

    $servico_post = get_post($servico_id);
    if (!$servico_post || $servico_post->post_type !== 'agp_servico') {
        wp_send_json_error(['message' => 'Serviço inválido.']);
    }

    // Verifica conflito para não permitir agendamento duplicado no mesmo horário, serviço e profissional
    $conflitos = get_posts([
        'post_type'   => 'agp_agendamento',
        'post_status' => 'publish',
        'meta_query'  => [
            ['key' => '_agp_data', 'value' => $data],
            ['key' => '_agp_hora', 'value' => $hora],
            ['key' => '_agp_servico', 'value' => $servico_id],
            ['key' => '_agp_profissional', 'value' => $profissional_id],
        ],
        'fields'      => 'ids',
        'posts_per_page' => 1,
    ]);

    if (!empty($conflitos)) {
        wp_send_json_error(['message' => 'Já existe um agendamento para este profissional e serviço na data e hora selecionadas.']);
    }

    // Cria o post do agendamento
    $post_title = sprintf('%s - %s - %s', $servico_post->post_title, $profissional_post->post_title, $nome);

    $post_id = wp_insert_post([
        'post_type'    => 'agp_agendamento',
        'post_status'  => 'publish',
        'post_title'   => $post_title,
        'post_content' => sprintf('Agendamento feito via site em %s às %s.', $data, $hora),
    ]);

    // Verifica erro na criação do post
    if (is_wp_error($post_id) || !$post_id) {
        wp_send_json_error(['message' => 'Erro ao salvar o agendamento.']);
    }

    // Salva os metadados do agendamento
    update_post_meta($post_id, '_agp_data', $data);
    update_post_meta($post_id, '_agp_hora', $hora);
    update_post_meta($post_id, '_agp_nome_cliente', $nome);
    update_post_meta($post_id, '_agp_telefone_cliente', $telefone);
    update_post_meta($post_id, '_agp_profissional', $profissional_id);
    update_post_meta($post_id, '_agp_servico', $servico_id);

    // Retorna resposta de sucesso para o AJAX
    wp_send_json_success(['message' => 'Agendamento realizado com sucesso!']);
}
