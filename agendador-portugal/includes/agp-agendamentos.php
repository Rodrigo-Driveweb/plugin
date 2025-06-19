<?php
/**
 * Agendamentos - Módulo do Plugin Gestão de Serviços
 * @author DriveWeb - Rodrigo Soares
 * @link https://www.driveweb.pt
 * @date 18/06/2025
 */

if (!defined('ABSPATH')) exit;

//
// REGISTRA O CUSTOM POST TYPE AGENDAMENTO
//
add_action('init', function () {
    register_post_type('agp_agendamento', [
        'labels' => [
            'name' => 'Agendamentos',
            'singular_name' => 'Agendamento',
            'add_new' => 'Novo Agendamento',
            'add_new_item' => 'Adicionar Agendamento',
            'edit_item' => 'Editar Agendamento',
            'new_item' => 'Novo Agendamento',
            'view_item' => 'Ver Agendamento',
            'search_items' => 'Buscar Agendamentos',
            'not_found' => 'Nenhum agendamento encontrado',
            'not_found_in_trash' => 'Nenhum agendamento na lixeira',
            'all_items' => 'Todos os Agendamentos',
            'menu_name' => 'Agendamentos',
            'name_admin_bar' => 'Agendamento',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => ['title'],
        'rewrite' => false
    ]);
});

//
// ADICIONA METABOX PERSONALIZADO
//
add_action('add_meta_boxes', function () {
    add_meta_box(
        'agp_agendamento_info',
        'Informações do Agendamento',
        'agp_render_agendamento_metabox',
        'agp_agendamento',
        'normal',
        'default'
    );
});

function agp_render_agendamento_metabox($post) {
    $cliente_id     = get_post_meta($post->ID, '_agp_cliente', true);
    $profissional_id = get_post_meta($post->ID, '_agp_profissional', true);
    $servico_id     = get_post_meta($post->ID, '_agp_servico', true);
    $data           = get_post_meta($post->ID, '_agp_data', true);
    $hora           = get_post_meta($post->ID, '_agp_hora', true);
    $status         = get_post_meta($post->ID, '_agp_status', true);

    if ($post->post_status === 'auto-draft') {
        if (isset($_GET['data'])) $data = sanitize_text_field($_GET['data']);
        if (isset($_GET['hora'])) $hora = sanitize_text_field($_GET['hora']);
    }

    $clientes = get_posts(['post_type' => 'agp_cliente', 'numberposts' => -1]);
    $profissionais = get_posts(['post_type' => 'agp_profissional', 'numberposts' => -1]);
    $servicos = get_posts(['post_type' => 'agp_servico', 'numberposts' => -1]);

    wp_nonce_field('agp_save_agendamento', 'agp_agendamento_nonce');
    ?>

    <p><label>Cliente:</label><br>
        <select name="agp_cliente" class="widefat" required>
            <option value="">-- Selecione --</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?= $cliente->ID ?>" <?= selected($cliente_id, $cliente->ID) ?>>
                    <?= esc_html($cliente->post_title) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p><label>Profissional:</label><br>
        <select name="agp_profissional" class="widefat" required>
            <option value="">-- Selecione --</option>
            <?php foreach ($profissionais as $prof): ?>
                <option value="<?= $prof->ID ?>" <?= selected($profissional_id, $prof->ID) ?>>
                    <?= esc_html($prof->post_title) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p><label>Serviço:</label><br>
        <select name="agp_servico" class="widefat" required>
            <option value="">-- Selecione --</option>
            <?php foreach ($servicos as $serv): ?>
                <option value="<?= $serv->ID ?>" <?= selected($servico_id, $serv->ID) ?>>
                    <?= esc_html($serv->post_title) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p><label>Data:</label><br>
        <input type="date" name="agp_data" value="<?= esc_attr($data) ?>" class="widefat" required />
    </p>

    <p><label>Hora:</label><br>
        <input type="time" name="agp_hora" value="<?= esc_attr($hora) ?>" class="widefat" required />
    </p>

    <p><label>Status:</label><br>
        <select name="agp_status" class="widefat">
            <?php foreach (['pendente', 'confirmado', 'concluido', 'cancelado'] as $op): ?>
                <option value="<?= $op ?>" <?= selected($status, $op) ?>><?= ucfirst($op) ?></option>
            <?php endforeach; ?>
        </select>
    </p>

    <?php
}

//
// SALVA OS METADADOS
//
add_action('save_post_agp_agendamento', function ($post_id) {
    if (!isset($_POST['agp_agendamento_nonce']) || !wp_verify_nonce($_POST['agp_agendamento_nonce'], 'agp_save_agendamento')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $cliente     = intval($_POST['agp_cliente']);
    $profissional = intval($_POST['agp_profissional']);
    $servico     = intval($_POST['agp_servico']);
    $data        = sanitize_text_field($_POST['agp_data']);
    $hora        = sanitize_text_field($_POST['agp_hora']);
    $status      = sanitize_text_field($_POST['agp_status']);

    // Verifica conflito
    $conflito = get_posts([
        'post_type'   => 'agp_agendamento',
        'post_status' => 'publish',
        'exclude'     => [$post_id],
        'meta_query'  => [
            ['key' => '_agp_profissional', 'value' => $profissional],
            ['key' => '_agp_data', 'value' => $data],
            ['key' => '_agp_hora', 'value' => $hora],
        ]
    ]);

    if (!empty($conflito)) {
        wp_die('⚠️ Conflito de horário: este profissional já possui um agendamento nesta data e hora.');
    }

    update_post_meta($post_id, '_agp_cliente', $cliente);
    update_post_meta($post_id, '_agp_profissional', $profissional);
    update_post_meta($post_id, '_agp_servico', $servico);
    update_post_meta($post_id, '_agp_data', $data);
    update_post_meta($post_id, '_agp_hora', $hora);
    update_post_meta($post_id, '_agp_status', $status);
});
