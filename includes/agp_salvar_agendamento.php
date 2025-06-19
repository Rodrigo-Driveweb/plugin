<?php
/**
 * Agendamentos - Salvamento via AJAX
 * Parte do Plugin Gestão de Serviços
 * @author DriveWeb - Rodrigo Soares | www.driveweb.pt
 * @copyright 2025 DriveWeb. Todos os direitos reservados.
 * @date 18/06/2025
 */

if (!defined('ABSPATH')) exit;

// Registro da ação AJAX para visitantes e usuários logados
add_action('wp_ajax_nopriv_agp_salvar_agendamento', 'agp_salvar_agendamento');
add_action('wp_ajax_agp_salvar_agendamento', 'agp_salvar_agendamento');

/**
 * Função que salva o agendamento enviado via formulário do front-end
 */
function agp_salvar_agendamento() {
    // Verifica nonce para segurança (espera o campo 'nonce' no POST)
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'agp_front_agendamento')) {
        wp_send_json_error(['message' => 'Falha na verificação de segurança.']);
    }

    // Sanitiza dados recebidos
    $nome     = sanitize_text_field($_POST['nome']     ?? '');
    $telefone = sanitize_text_field($_POST['telefone'] ?? '');
    $servico  = sanitize_text_field($_POST['servico']  ?? '');
    $data     = sanitize_text_field($_POST['data']     ?? '');
    $hora     = sanitize_text_field($_POST['hora']     ?? '');

    // Validação dos campos obrigatórios
    if (!$nome || !$telefone || !$servico || !$data || !$hora) {
        wp_send_json_error(['message' => 'Preencha todos os campos obrigatórios.']);
    }

    // Busca o serviço pelo título para validar existência
    $servico_obj = get_page_by_title($servico, OBJECT, 'agp_servico');
    if (!$servico_obj) {
        wp_send_json_error(['message' => 'Serviço não encontrado.']);
    }

    // Verifique se já existe agendamento no mesmo dia, hora e serviço para evitar duplicidade
    $args = [
        'post_type'      => 'agp_agendamento',
        'post_status'    => 'publish',
        'meta_query'     => [
            ['key' => '_agp_data', 'value' => $data],
            ['key' => '_agp_hora', 'value' => $hora],
            ['key' => '_agp_servico', 'value' => $servico_obj->ID],
        ],
        'fields'         => 'ids',
        'posts_per_page' => 1,
    ];
    $existing = get_posts($args);
    if (!empty($existing)) {
        wp_send_json_error(['message' => 'Este horário já está reservado para o serviço selecionado.']);
    }

    // Cria o post do agendamento
    $post_id = wp_insert_post([
        'post_type'    => 'agp_agendamento',
        'post_status'  => 'publish',
        'post_title'   => sprintf('%s - %s', $servico, $nome),
        'post_content' => sprintf('Agendamento feito via site em %s às %s.', $data, $hora),
    ]);

    // Verifica se houve erro na criação do post
    if (is_wp_error($post_id) || !$post_id) {
        wp_send_json_error(['message' => 'Erro ao salvar o agendamento.']);
    }

    // Salva os metadados relacionados ao agendamento
    update_post_meta($post_id, '_agp_data', $data);
    update_post_meta($post_id, '_agp_hora', $hora);
    update_post_meta($post_id, '_agp_nome_cliente', $nome);
    update_post_meta($post_id, '_agp_telefone_cliente', $telefone);
    update_post_meta($post_id, '_agp_servico', $servico_obj->ID);

    // Retorna sucesso para o AJAX
    wp_send_json_success(['message' => 'Agendamento realizado com sucesso!']);
}
