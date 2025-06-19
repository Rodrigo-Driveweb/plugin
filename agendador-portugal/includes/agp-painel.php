<?php
/**
 * Notificações - Plugin Gestão de Serviços
 * @author DriveWeb - Rodrigo Soares | www.driveweb.pt
 * @copyright 2025 DriveWeb. Todos os direitos reservados.
 * @date 18/06/2025
 */


if (!defined('ABSPATH')) exit;

// Submenu "Notificações" no painel
add_action('admin_menu', function () {
    add_submenu_page(
        'agp_painel',                // Slug do menu pai (Painel)
        'Notificações',             // Título da página
        'Notificações',             // Nome no menu lateral
        'manage_options',           // Permissão
        'agp_notificacoes',         // Slug do submenu
        'agp_notificacoes_painel'   // Função callback
    );
});

// Conteúdo da página de Notificações
function agp_notificacoes_painel() {
    echo '<div class="wrap">';
    echo '<h1>Notificações</h1>';
    echo '<p>Este módulo envia notificações automáticas por e-mail aos clientes e profissionais.</p>';
    echo '<ul>';
    echo '<li>✅ Confirmação de agendamento</li>';
    echo '<li>✅ Notificações de aniversário</li>';
    echo '<li>✅ (Em breve) Lembrete prévio do agendamento</li>';
    echo '<li>✅ (Em breve) Notificação de pagamento pendente</li>';
    echo '</ul>';
    echo '</div>';
}

// Confirmação por e-mail após publicação de agendamento
add_action('publish_agp_agendamento', 'agp_notificar_confirmacao_agendamento', 10, 2);
function agp_notificar_confirmacao_agendamento($post_id, $post) {
    $cliente = get_post_meta($post_id, '_agp_cliente', true);
    $profissional = get_post_meta($post_id, '_agp_profissional', true);
    $servico = get_post_meta($post_id, '_agp_servico', true);
    $datahora = get_post_meta($post_id, '_agp_datahora', true);
    $email = get_post_meta($post_id, '_agp_email', true);

    if ($email) {
        $assunto = 'Confirmação do Agendamento';
        $mensagem = "Olá $cliente,\n\nSeu agendamento foi confirmado com os detalhes abaixo:\n"
            . "Profissional: $profissional\n"
            . "Serviço: $servico\n"
            . "Data e hora: $datahora\n\nObrigado!";
        wp_mail($email, $assunto, $mensagem);
    }
}

// Notificações de aniversário (executada por cron ou manualmente)
add_action('agp_notificar_aniversariantes', function () {
    $hoje = date('m-d');
    $clientes = get_posts(['post_type' => 'agp_cliente', 'posts_per_page' => -1]);

    foreach ($clientes as $cliente) {
        $aniv = get_post_meta($cliente->ID, '_agp_aniversario', true);
        $email = get_post_meta($cliente->ID, '_agp_email', true);
        $nome = $cliente->post_title;

        if ($aniv && substr($aniv, 5) === $hoje && $email) {
            $assunto = "Feliz Aniversário, $nome!";
            $mensagem = "Olá $nome,\n\nToda a equipa deseja-lhe um feliz aniversário!\n\n— Equipa DriveWeb";
            wp_mail($email, $assunto, $mensagem);
        }
    }
});
