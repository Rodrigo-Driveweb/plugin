<?php
/**
 * Agendamento Público - Plugin Gestão de Serviços
 * @author DriveWeb - Rodrigo Soares | www.driveweb.pt
 * @copyright 2025 DriveWeb. Todos os direitos reservados.
 * @date 18/06/2025
 */

if (!defined('ABSPATH')) exit;

// Shortcode de agendamento público
add_shortcode('agp_form_agendamento', 'agp_render_form_agendamento');

function agp_render_form_agendamento() {
    ob_start();

    if ($_POST && isset($_POST['agp_public_form_nonce']) && wp_verify_nonce($_POST['agp_public_form_nonce'], 'agp_submit_public_agendamento')) {
        $nome = sanitize_text_field($_POST['agp_nome']);
        $email = sanitize_email($_POST['agp_email']);
        $tel = sanitize_text_field($_POST['agp_telemovel']);
        $profissional_id = intval($_POST['agp_profissional']);
        $servico_id = intval($_POST['agp_servico']);
        $datahora = sanitize_text_field($_POST['agp_datahora']);

        if (!$nome || !$email || !$tel || !$profissional_id || !$servico_id || !$datahora) {
            echo '<p><strong>Por favor, preencha todos os campos corretamente.</strong></p>';
        } else {
            $post_id = wp_insert_post([
                'post_type' => 'agp_agendamento',
                'post_status' => 'publish',
                'post_title' => 'Agendamento de ' . $nome,
            ]);

            if ($post_id) {
                update_post_meta($post_id, '_agp_cliente', $nome);
                update_post_meta($post_id, '_agp_email', $email);
                update_post_meta($post_id, '_agp_telemovel', $tel);
                update_post_meta($post_id, '_agp_profissional', $profissional_id);
                update_post_meta($post_id, '_agp_servico', $servico_id);
                update_post_meta($post_id, '_agp_datahora', $datahora);

                wp_mail(
                    $email,
                    'Confirmação de Agendamento',
                    "Olá $nome,\n\nO seu agendamento foi recebido com sucesso!\nProfissional: " . get_the_title($profissional_id) . "\nServiço: " . get_the_title($servico_id) . "\nData/Hora: $datahora"
                );

                echo '<p><strong>Agendamento recebido com sucesso! Verifique seu e-mail.</strong></p>';
            } else {
                echo '<p><strong>Erro ao salvar agendamento, tente novamente.</strong></p>';
            }
        }
    }

    $profissionais = get_posts(['post_type' => 'agp_profissional', 'posts_per_page' => -1]);
    $servicos = get_posts(['post_type' => 'agp_servico', 'posts_per_page' => -1]);

    ?>
    <form method="post">
        <?php wp_nonce_field('agp_submit_public_agendamento', 'agp_public_form_nonce'); ?>
        <p><label>Nome:</label><br><input type="text" name="agp_nome" required></p>
        <p><label>E-mail:</label><br><input type="email" name="agp_email" required></p>
        <p><label>Telemóvel:</label><br><input type="text" name="agp_telemovel" required></p>
        <p><label>Profissional:</label><br>
            <select name="agp_profissional" required>
                <option value="">Escolha</option>
                <?php foreach ($profissionais as $p): ?>
                    <option value="<?php echo esc_attr($p->ID); ?>"><?php echo esc_html($p->post_title); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p><label>Serviço:</label><br>
            <select name="agp_servico" required>
                <option value="">Escolha</option>
                <?php foreach ($servicos as $s): ?>
                    <option value="<?php echo esc_attr($s->ID); ?>"><?php echo esc_html($s->post_title); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p><label>Data e hora:</label><br><input type="datetime-local" name="agp_datahora" required></p>
        <p><button type="submit">Agendar</button></p>
    </form>
    <?php

    return ob_get_clean();
}

// Shortcode: [agp_meus_agendamentos email="cliente@email.com"]
add_shortcode('agp_meus_agendamentos', function ($atts) {
    $atts = shortcode_atts(['email' => ''], $atts);
    $email = sanitize_email($atts['email']);

    if (!$email) return '<p><strong>Por favor, forneça um e-mail válido.</strong></p>';

    $agendamentos = get_posts([
        'post_type' => 'agp_agendamento',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => '_agp_email',
                'value' => $email,
                'compare' => '='
            ]
        ],
        'orderby' => 'meta_value',
        'meta_key' => '_agp_datahora',
        'order' => 'DESC'
    ]);

    if (!$agendamentos) return '<p><strong>Nenhum agendamento encontrado para este e-mail.</strong></p>';

    ob_start();
    echo '<div class="agp-lista-agendamentos">';
    echo '<ul>';
    foreach ($agendamentos as $ag) {
        $servico = get_post_meta($ag->ID, '_agp_servico', true);
        $prof = get_post_meta($ag->ID, '_agp_profissional', true);
        $data = get_post_meta($ag->ID, '_agp_datahora', true);
        $status = get_post_status($ag->ID);

        echo '<li>';
        echo '<strong>Serviço:</strong> ' . esc_html(get_the_title($servico)) . '<br>';
        echo '<strong>Profissional:</strong> ' . esc_html(get_the_title($prof)) . '<br>';
        echo '<strong>Data/Hora:</strong> ' . esc_html(date('d/m/Y H:i', strtotime($data))) . '<br>';
        echo '<strong>Status:</strong> ' . esc_html(ucfirst($status));
        echo '</li><hr>';
    }
    echo '</ul>';
    echo '</div>';

    return ob_get_clean();
});
