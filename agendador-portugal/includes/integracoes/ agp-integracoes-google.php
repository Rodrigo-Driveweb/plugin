<?php
/**
 * Integração com Google Calendar
 * Autor: DriveWeb - Rodrigo Soares | www.driveweb.pt | 17/06/2025
 */

if (!defined('ABSPATH')) exit;

/**
 * Gera link de evento para Google Calendar
 */
function agp_gerar_link_google_calendar($titulo, $descricao, $inicio, $fim) {
    $start = date('Ymd\THis', strtotime($inicio));
    $end = date('Ymd\THis', strtotime($fim));
    $titulo = urlencode($titulo);
    $descricao = urlencode($descricao);
    return "https://www.google.com/calendar/render?action=TEMPLATE&text={$titulo}&details={$descricao}&dates={$start}/{$end}";
}
