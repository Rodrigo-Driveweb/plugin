add_action('wp_ajax_nopriv_agp_salvar_agendamento', 'agp_salvar_agendamento');
add_action('wp_ajax_agp_salvar_agendamento', 'agp_salvar_agendamento'); // Para logados

function agp_salvar_agendamento() {
    $nome = sanitize_text_field($_POST['nome'] ?? '');
    $telefone = sanitize_text_field($_POST['telefone'] ?? '');
    $servico = sanitize_text_field($_POST['servico'] ?? '');
    $data = sanitize_text_field($_POST['data'] ?? '');
    $hora = sanitize_text_field($_POST['hora'] ?? '');

    if (!$nome || !$telefone || !$servico || !$data || !$hora) {
        wp_send_json_error(['message' => 'Dados incompletos.']);
    }

    $post_id = wp_insert_post([
        'post_type' => 'agp_agendamento',
        'post_status' => 'publish',
        'post_title' => "$servico - $nome",
        'post_content' => "Agendamento feito pelo cliente via site",
    ]);

    if ($post_id) {
        update_post_meta($post_id, '_agp_data', $data);
        update_post_meta($post_id, '_agp_hora', $hora);
        update_post_meta($post_id, '_agp_nome_cliente', $nome);
        update_post_meta($post_id, '_agp_telefone_cliente', $telefone);
        update_post_meta($post_id, '_agp_servico', $servico); // compatível com agendamentos existentes
        wp_send_json_success(['message' => 'Agendamento realizado com sucesso!']);
    }

    wp_send_json_error(['message' => 'Erro ao salvar agendamento.']);
}
