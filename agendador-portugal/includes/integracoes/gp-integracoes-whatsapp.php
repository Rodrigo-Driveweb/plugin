<?php
/**
 * Integração com WhatsApp
 * @author DriveWeb - Rodrigo Soares
 * @link https://www.driveweb.pt
 * @date 18/06/2025
 */

if (!defined('ABSPATH')) exit;

/**
 * Gera link de envio por WhatsApp (número em formato internacional)
 *
 * @param string $telefone
 * @param string $mensagem
 * @return string
 */
function agp_gerar_link_whatsapp($telefone, $mensagem) {
    $tel = preg_replace('/[^0-9]/', '', $telefone);

    if (strlen($tel) < 9) {
        return '#erro_numero_invalido';
    }

    $msg = urlencode(trim($mensagem));
    return "https://wa.me/{$tel}?text={$msg}";
}
