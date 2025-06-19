<?php
/**
 * Módulo Profissionais - Gestão de Serviços
 * Autor: DriveWeb - Rodrigo Soares | www.driveweb.pt
 * Data: 18/06/2025
 */

if (!defined('ABSPATH')) exit;

//
// REGISTRA O CUSTOM POST TYPE PROFISSIONAIS
//
add_action('init', function () {
    register_post_type('agp_profissional', [
        'labels' => [
            'name' => 'Profissionais',
            'singular_name' => 'Profissional',
            'add_new' => 'Adicionar Novo',
            'add_new_item' => 'Novo Profissional',
            'edit_item' => 'Editar Profissional',
            'view_item' => 'Ver Profissional',
            'search_items' => 'Buscar Profissionais',
            'not_found' => 'Nenhum profissional encontrado',
            'not_found_in_trash' => 'Nenhum profissional na lixeira'
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false,
        'menu_icon' => 'dashicons-businessperson',
        'supports' => ['title'],
        'rewrite' => false
    ]);
});

//
// METABOX: INFORMAÇÕES DO PROFISSIONAL
//
add_action('add_meta_boxes', function () {
    add_meta_box('agp_prof_info', 'Informações do Profissional', 'agp_render_profissional_metabox', 'agp_profissional', 'normal', 'default');
});

function agp_render_profissional_metabox($post) {
    $especializacoes = get_post_meta($post->ID, '_agp_especializacoes', true);
    $carga_horaria = get_post_meta($post->ID, '_agp_carga_horaria', true);
    $dias_folga = get_post_meta($post->ID, '_agp_dias_folga', true);
    $disponibilidade = get_post_meta($post->ID, '_agp_disponibilidade', true);
    $servicos = get_post_meta($post->ID, '_agp_servicos', true);

    $dias = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
    $turnos = ['Manhã', 'Tarde', 'Noite'];

    // Pega todos os serviços disponíveis
    $servicos_disponiveis = get_posts([
        'post_type' => 'agp_servico',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);

    wp_nonce_field('agp_save_profissional', 'agp_profissional_nonce');
    ?>
    <p><label>Especializações:</label><br>
        <textarea name="agp_especializacoes" class="widefat"><?php echo esc_textarea($especializacoes); ?></textarea></p>

    <p><label>Carga Horária (horas semanais):</label><br>
        <input type="number" name="agp_carga_horaria" value="<?php echo esc_attr($carga_horaria); ?>" class="widefat" /></p>

    <p><label>Dias de Folga:</label><br>
        <?php foreach ($dias as $dia): ?>
            <label><input type="checkbox" name="agp_dias_folga[]" value="<?php echo $dia; ?>" <?php checked(is_array($dias_folga) && in_array($dia, $dias_folga)); ?>> <?php echo $dia; ?></label><br>
        <?php endforeach; ?>
    </p>

    <p><label>Disponibilidade (turnos):</label><br>
        <?php foreach ($turnos as $turno): ?>
            <label><input type="checkbox" name="agp_disponibilidade[]" value="<?php echo $turno; ?>" <?php checked(is_array($disponibilidade) && in_array($turno, $disponibilidade)); ?>> <?php echo $turno; ?></label><br>
        <?php endforeach; ?>
    </p>

    <p><label>Serviços Habilitados:</label><br>
        <select name="agp_servicos[]" multiple class="widefat" style="height:auto;">
            <?php foreach ($servicos_disponiveis as $servico): ?>
                <option value="<?php echo $servico->ID; ?>" <?php selected(is_array($servicos) && in_array($servico->ID, $servicos)); ?>>
                    <?php echo esc_html($servico->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <small>Segure Ctrl (Windows) ou Cmd (Mac) para selecionar vários.</small>
    </p>
    <?php
}

//
// SALVA OS DADOS DO PROFISSIONAL
//
add_action('save_post_agp_profissional', function ($post_id) {
    if (!isset($_POST['agp_profissional_nonce']) || !wp_verify_nonce($_POST['agp_profissional_nonce'], 'agp_save_profissional')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    update_post_meta($post_id, '_agp_especializacoes', sanitize_textarea_field($_POST['agp_especializacoes']));
    update_post_meta($post_id, '_agp_carga_horaria', intval($_POST['agp_carga_horaria']));

    $dias_folga = isset($_POST['agp_dias_folga']) ? array_map('sanitize_text_field', $_POST['agp_dias_folga']) : [];
    $disponibilidade = isset($_POST['agp_disponibilidade']) ? array_map('sanitize_text_field', $_POST['agp_disponibilidade']) : [];
    $servicos = isset($_POST['agp_servicos']) ? array_map('intval', $_POST['agp_servicos']) : [];

    update_post_meta($post_id, '_agp_dias_folga', $dias_folga);
    update_post_meta($post_id, '_agp_disponibilidade', $disponibilidade);
    update_post_meta($post_id, '_agp_servicos', $servicos);
});
