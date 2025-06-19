<?php
/**
 * Notificações - Plugin Gestão de Serviços
 * @author DriveWeb - Rodrigo Soares | www.driveweb.pt
 * @copyright 2025 DriveWeb. Todos os direitos reservados.
 * @date 18/06/2025
 */

if (!defined('ABSPATH')) exit;

// 1. Cria item no menu do admin para visualizar o módulo
add_action('admin_menu', function () {
    add_menu_page(
        'Notificações',
        'Notificações',
        'manage_options',
        'agp_notificacoes',
        'agp_notificacoes_painel',
        'dashicons-email-alt',
        26
    );
});

// 2. Tela de painel informativo das notificações
function agp_notificacoes_painel() {
    ?>
    <div class="wrap">
        <h1>📬 Notificações</h1>
        <p>Este módulo envia automaticamente mensagens por e-mail com base nos eventos de agendamento e financeiro.</p>
        <hr>
        <ul style="list-style: disc; padding-left: 20px;">
            <li><strong>Confirmação de Agendamento:</strong> Enviada automaticamente ao cliente.</li>
            <li><strong>Aniversário do Cliente:</strong> Felicitação no dia do aniversário.</li>
            <li><strong>Em breve:</strong> Notificações de pagamento pendente e WhatsApp/SMS.</li>
        </ul>

        <h3>🔜 Em breve:</h3>
        <ul>
            <li>Notificações de <strong>pagamento pendente</strong>.</li>
            <li>Envio de <strong>WhatsApp</strong> e <strong>SMS</strong>.</li>
            <li>Integrações com <strong>PIX</strong>, <strong>MB WAY</strong> e <strong>Multibanco</strong> para notificações financeiras automáticas.</li>
        </ul>
    </div>
    <?php
}

// 3. Notificação por e-mail após confirmação de agendamento
add_action('publish_agp_agendamento', 'agp_notificar_confirmacao_agendamento', 10, 2);
function agp_notificar_confirmacao_agendamento($post_id, $post) {
    $cliente      = get_post_meta($post_id, '_agp_cliente', true);
    $profissional = get_post_meta($post_id, '_agp_profissional', true);
    $servico      = get_post_meta($post_id, '_agp_servico', true);
    $data         = get_post_meta($post_id, '_agp_data', true);
    $hora         = get_post_meta($post_id, '_agp_hora', true);
    $email        = get_post_meta($post_id, '_agp_email', true);

    if ($email && is_email($email)) {
        $datahora = $data . ' às ' . $hora;
        $assunto = 'Confirmação do Agendamento';
        $mensagem = "Olá $cliente,\n\nSeu agendamento foi confirmado com os detalhes abaixo:\n\n" .
                    "Profissional: $profissional\n" .
                    "Serviço: $servico\n" .
                    "Data e Hora: $datahora\n\n" .
                    "Obrigado pela preferência,\nDriveWeb";

        wp_mail($email, $assunto, $mensagem);
    }
}

// 4. Notificação de Aniversário
add_action('agp_notificar_aniversariantes', function () {
    $hoje = date('m-d');
    $clientes = get_posts([
        'post_type' => 'agp_cliente',
        'posts_per_page' => -1
    ]);

    foreach ($clientes as $cliente) {
        $aniversario = get_post_meta($cliente->ID, '_agp_aniversario', true);
        $email       = get_post_meta($cliente->ID, '_agp_email', true);
        $nome        = $cliente->post_title;

        if ($aniversario && substr($aniversario, 5) === $hoje && $email && is_email($email)) {
            $assunto = "🎉 Feliz Aniversário, $nome!";
            $mensagem = "Olá $nome,\n\nToda a equipa da DriveWeb deseja-lhe um feliz aniversário!\n\nCom os melhores cumprimentos,\nDriveWeb";
            wp_mail($email, $assunto, $mensagem);
        }
    }
});
