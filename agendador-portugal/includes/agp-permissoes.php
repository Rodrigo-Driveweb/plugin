<?php
/**
 * Controle de permissões e capacidades
 * DriveWeb - Rodrigo Soares | www.driveweb.pt | 17/06/2025
 */

if (!defined('ABSPATH')) exit;

// Cria funções personalizadas ao ativar
register_activation_hook(__FILE__, 'agp_adicionar_capacidades');

function agp_adicionar_capacidades() {
    $roles = ['administrator', 'editor', 'author'];

    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if ($role) {
            $role->add_cap('agp_gerenciar_clientes');
            $role->add_cap('agp_gerenciar_agendamentos');
            $role->add_cap('agp_gerenciar_financeiro');
            $role->add_cap('agp_visualizar_relatorios');
        }
    }
}

// Remoção opcional de capacidades ao desativar
register_deactivation_hook(__FILE__, 'agp_remover_capacidades');

function agp_remover_capacidades() {
    $roles = ['administrator', 'editor', 'author'];

    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if ($role) {
            $role->remove_cap('agp_gerenciar_clientes');
            $role->remove_cap('agp_gerenciar_agendamentos');
            $role->remove_cap('agp_gerenciar_financeiro');
            $role->remove_cap('agp_visualizar_relatorios');
        }
    }
}
