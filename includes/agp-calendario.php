<?php
/**
 * Calendário - Plugin Gestão de Serviços
 * @author DriveWeb - Rodrigo Soares | www.driveweb.pt
 * @copyright 2025 DriveWeb. Todos os direitos reservados.
 * @date 18/06/2025
 */

if (!defined('ABSPATH')) exit;

// Adicionar submenu do calendário
add_action('admin_menu', function () {
    add_submenu_page(
        'agp_painel',
        'Calendário de Agendamentos',
        'Calendário',
        'manage_options',
        'agp_calendario',
        'agp_render_calendario_page'
    );
});

// Renderiza página do calendário
function agp_render_calendario_page() {
    ?>
    <div class="wrap">
        <h1>Calendário de Agendamentos</h1>
        <div id="agp-calendar" style="background: #fff; padding: 20px; border: 1px solid #ccc;"></div>

        <!-- Modal -->
        <div id="agp-modal" style="display:none;">
            <div id="agp-modal-content">
                <span id="agp-modal-close" style="cursor:pointer;">&times;</span>
                <h2>Detalhes do Agendamento</h2>
                <div id="agp-modal-body"></div>
            </div>
        </div>

        <style>
            #agp-modal {
                position: fixed;
                z-index: 9999;
                left: 0; top: 0;
                width: 100%; height: 100%;
                overflow: auto;
                background-color: rgba(0,0,0,0.4);
                display: flex;
                justify-content: center;
                align-items: center;
            }
            #agp-modal-content {
                background-color: #fff;
                padding: 20px;
                border-radius: 8px;
                width: 90%;
                max-width: 500px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                position: relative;
            }
            #agp-modal-close {
                position: absolute;
                right: 15px;
                top: 10px;
                font-size: 28px;
                font-weight: bold;
                color: #aaa;
            }
            #agp-modal-close:hover {
                color: #000;
            }
        </style>
    </div>
    <?php
}

// Scripts e calendário com clique para agendamento
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'agendador_page_agp_calendario') return;

    wp_enqueue_script('fullcalendar-core', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js', [], '6.1.8', true);
    wp_enqueue_script('fullcalendar-ptbr', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.global.min.js', ['fullcalendar-core'], null, true);
    wp_enqueue_style('fullcalendar-style', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css');

    wp_add_inline_script('fullcalendar-ptbr', '
        document.addEventListener("DOMContentLoaded", function() {
            const calendarEl = document.getElementById("agp-calendar");
            const modal = document.getElementById("agp-modal");
            const modalBody = document.getElementById("agp-modal-body");
            const modalClose = document.getElementById("agp-modal-close");

            modalClose.onclick = function() { modal.style.display = "none"; };
            window.onclick = function(event) {
                if (event.target === modal) modal.style.display = "none";
            };

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: "dayGridMonth",
                locale: "pt-br",
                headerToolbar: {
                    left: "prev,next today",
                    center: "title",
                    right: "dayGridMonth,timeGridWeek,timeGridDay"
                },
                buttonText: {
                    today: "Hoje",
                    month: "Mês",
                    week: "Semana",
                    day: "Dia"
                },
                events: {
                    url: ajaxurl,
                    method: "POST",
                    extraParams: {
                        action: "agp_get_agendamentos"
                    },
                    failure: function () {
                        alert("Erro ao carregar os agendamentos.");
                    }
                },
                eventClick: function (info) {
                    modalBody.innerHTML = "<p><strong>Título:</strong> " + info.event.title + "</p>" +
                                          "<p><strong>Data e Hora:</strong> " + info.event.start.toLocaleString() + "</p>";
                    modal.style.display = "flex";
                },
                dateClick: function(info) {
                    const dateObj = new Date(info.dateStr);
                    const data = dateObj.toISOString().split("T")[0];
                    const hora = dateObj.toTimeString().substring(0,5);
                    const url = "/wp-admin/post-new.php?post_type=agp_agendamento&data=" + data + "&hora=" + hora;
                    window.open(url, "_blank");
                }
            });

            calendar.render();
        });
    ');
});

// AJAX que envia eventos para o calendário
add_action('wp_ajax_agp_get_agendamentos', 'agp_ajax_get_agendamentos');
function agp_ajax_get_agendamentos() {
    $agendamentos = get_posts([
        'post_type' => 'agp_agendamento',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ]);

    $events = [];

    foreach ($agendamentos as $ag) {
        // Tenta obter nova estrutura
        $data = get_post_meta($ag->ID, "_agp_data", true);
        $hora = get_post_meta($ag->ID, "_agp_hora", true);

        if ($data && $hora) {
            $datahora = $data . ' ' . $hora;
        } else {
            // Se não existir, usa a antiga
            $datahora = get_post_meta($ag->ID, "_agp_datahora", true);
        }

        if (!$datahora) continue;

        $datahora_iso = date('Y-m-d\TH:i:s', strtotime($datahora));

        $cliente_id = get_post_meta($ag->ID, "_agp_cliente", true);
        $prof_id = get_post_meta($ag->ID, "_agp_profissional", true);
        $servico_id = get_post_meta($ag->ID, "_agp_servico", true);

        $cliente = $cliente_id ? get_the_title($cliente_id) : 'Cliente';
        $profissional = $prof_id ? get_the_title($prof_id) : 'Profissional';
        $servico = $servico_id ? get_the_title($servico_id) : 'Serviço';

        $titulo = "$servico - $cliente ($profissional)";

        $events[] = [
            "id" => $ag->ID,
            "title" => $titulo,
            "start" => $datahora_iso,
        ];
    }

    wp_send_json($events);
}
?>
