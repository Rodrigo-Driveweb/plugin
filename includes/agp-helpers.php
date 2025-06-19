 <?php
if (!defined('ABSPATH')) exit;

/**
 * Formata número como euro (pt-PT)
 */
function agp_formatar_euro($valor) {
    return number_format(floatval($valor), 2, ',', '.') . ' €';
}

/**
 * Verifica se uma data já passou
 */
function agp_data_passada($data_iso) {
    $hoje = date('Y-m-d H:i');
    return strtotime($data_iso) < strtotime($hoje);
}

/**
 * Busca ID de cliente pelo nome
 */
function agp_cliente_id_por_nome($nome) {
    $clientes = get_posts([
        'post_type' => 'agp_cliente',
        'posts_per_page' => -1
    ]);
    foreach ($clientes as $cliente) {
        if (strtolower(trim($cliente->post_title)) === strtolower(trim($nome))) {
            return $cliente->ID;
        }
    }
    return false;
}

/**
 * Gera URL de edição no admin
 */
function agp_admin_editar_url($post_id) {
    return admin_url('post.php?post=' . intval($post_id) . '&action=edit');
}
