<?php
/**
 * Módulo Calendário - Gestão de Serviços
 * Autor: DriveWeb - Rodrigo Soares | www.driveweb.pt
 * Data: 18/06/2025
 */

if (!defined('ABSPATH')) exit;

// Inclui os scripts e estilos do FullCalendar
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style('fullcalendar-css', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css');
    wp_enqueue_script('fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js', [], null, true);
});
?>

<div class="wrap">
    <h1>Calendário de Agendamentos</h1>
    <div id="agp-calendar" style="max-width: 100%; margin: 20px auto;"></div>

    <!-- Modal -->
    <div id="agp-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:9999;">
        <div style="background:#fff; padding:20px; border-radius:8px; max-width:500px; width:90%;">
            <span id="agp-modal-close" style="float:right; cursor:pointer; font-size:20px;">&times;</span>
            <div id="agp-modal-body"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('agp-calendar');
    const modal = document.getElementById('agp-modal');
    const modalBody = document.getElementById('agp-modal-body');
    const modalClose = document.getElementById('agp-modal-close');

    modalClose.onclick = function () {
        modal.style.display = 'none';
    };
    window.onclick = function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        locale: 'pt',
        selectable: true,
        events: {
            url: ajaxurl,
            method: 'POST',
            extraParams: {
                action: 'agp_get_agendamentos'
            },
            failure: function () {
                alert('Erro ao carregar os agendamentos.');
            }
        },
        eventClick: function (info) {
            const partes = info.event.title.match(/^(.+) - (.+) \((.+)\)$/);
            if (partes) {
                const servico = partes[1];
                const cliente = partes[2];
                const profissional = partes[3];

                modalBody.innerHTML =
                    '<p><strong>Serviço:</strong> ' + servico + '</p>' +
                    '<p><strong>Cliente:</strong> ' + cliente + '</p>' +
                    '<p><strong>Profissional:</strong> ' + profissional + '</p>' +
                    '<p><strong>Data e Hora:</strong> ' + info.event.start.toLocaleString() + '</p>';
            } else {
                modalBody.innerHTML = '<p>Detalhes indisponíveis.</p>';
            }
            modal.style.display = 'flex';
        },
        dateClick: function (info) {
            const dataSelecionada = info.dateStr;
            const novaUrl = `/wp-admin/post-new.php?post_type=agp_agendamento&data=${dataSelecionada}`;
            window.location.href = novaUrl;
        }
    });

    calendar.render();
});
</script>
