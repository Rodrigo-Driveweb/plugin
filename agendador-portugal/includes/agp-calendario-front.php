<?php
/**
 * Calendário Front - Plugin Gestão de Serviços
 * @author DriveWeb - Rodrigo Soares | www.driveweb.pt
 * @copyright 2025 DriveWeb
 * @date 18/06/2025
 */

if (!defined('ABSPATH')) exit;

add_shortcode('agp_calendario_cliente', 'agp_render_calendario_cliente');

function agp_render_calendario_cliente() {
    // Busca profissionais e serviços para popular selects no modal
    $profissionais = get_posts(['post_type' => 'agp_profissional', 'numberposts' => -1, 'post_status' => 'publish']);
    $servicos = get_posts(['post_type' => 'agp_servico', 'numberposts' => -1, 'post_status' => 'publish']);

    ob_start(); ?>

    <div id="agp-calendar-front" style="background: #fff; padding: 20px; border: 1px solid #ccc;"></div>

    <div id="agp-modal-front" style="display:none;">
        <div id="agp-modal-front-content">
            <span id="agp-modal-front-close" style="cursor:pointer;">&times;</span>
            <h2>Agendar Horário</h2>
            <form id="agp-form-agendar">
                <input type="hidden" name="data" id="agp-data">
                <input type="hidden" name="hora" id="agp-hora">

                <p><label>Nome:</label><br><input type="text" name="nome" required></p>
                <p><label>Telefone:</label><br><input type="text" name="telefone" required></p>

                <p><label>Profissional:</label><br>
                    <select name="profissional" required>
                        <option value="">Selecione profissional</option>
                        <?php foreach ($profissionais as $prof): ?>
                            <option value="<?php echo esc_attr($prof->ID); ?>">
                                <?php echo esc_html($prof->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p><label>Serviço:</label><br>
                    <select name="servico" required>
                        <option value="">Selecione serviço</option>
                        <?php foreach ($servicos as $serv): ?>
                            <option value="<?php echo esc_attr($serv->ID); ?>">
                                <?php echo esc_html($serv->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p><button type="submit">Confirmar Agendamento</button></p>
            </form>
        </div>
    </div>

    <style>
        #agp-modal-front {
            position: fixed;
            z-index: 9999;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #agp-modal-front-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
            position: relative;
        }
        #agp-modal-front-close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const calendarEl = document.getElementById("agp-calendar-front");
            const modal = document.getElementById("agp-modal-front");
            const close = document.getElementById("agp-modal-front-close");
            const form = document.getElementById("agp-form-agendar");

            close.onclick = () => modal.style.display = "none";
            window.onclick = (e) => { if (e.target === modal) modal.style.display = "none"; };

            // Definir calendar como variável global para poder atualizar
            window.calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: "dayGridMonth",
                locale: "pt-br",
                events: {
                    url: agp_vars.ajaxurl,
                    method: "POST",
                    extraParams: { action: "agp_get_agendamentos" }
                },
                dateClick: function(info) {
                    document.getElementById("agp-data").value = info.dateStr;
                    document.getElementById("agp-hora").value = "10:00"; // pode alterar para dinâmica se quiser
                    modal.style.display = "flex";
                }
            });

            window.calendar.render();

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                formData.append('action', 'agp_salvar_agendamento');
                formData.append('nonce', agp_vars.nonce);

                // Como backend espera IDs para serviço e profissional, enviamos os valores do select
                // Para o agp_salvar_agendamento, ajuste backend para receber IDs do profissional e serviço

                fetch(agp_vars.ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message || (data.success ? 'Agendamento realizado!' : 'Erro ao agendar.'));
                    if (data.success) {
                        modal.style.display = "none";
                        window.calendar.refetchEvents();
                        form.reset();
                    }
                })
                .catch(() => {
                    alert('Erro inesperado ao enviar o formulário.');
                });
            });
        });
    </script>

    <?php
    return ob_get_clean();
}

// Enfileiramento scripts/styles frontend
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('fullcalendar-style', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css');

    wp_enqueue_script('fullcalendar-core', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js', [], null, true);
    wp_enqueue_script('fullcalendar-ptbr', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.global.min.js', ['fullcalendar-core'], null, true);

    wp_enqueue_script('agp-frontend-js', AGP_URL . 'js/a952f3a2-f43d-43c8-a151-9309e16fdff5.js', ['jquery'], '1.0', true);

    wp_localize_script('agp-frontend-js', 'agp_vars', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('agp_front_agendamento'),
    ]);
});
