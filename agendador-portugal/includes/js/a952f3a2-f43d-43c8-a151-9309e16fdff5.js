document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('agp-form-agendar');
  if (!form) return;

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(form);
    formData.append('action', 'agp_salvar_agendamento');
    formData.append('nonce', agp_vars.nonce);

    fetch(agp_vars.ajaxurl, {
      method: 'POST',
      body: formData
    })
    .then(res => {
      console.log('Resposta recebida do servidor:', res);
      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }
      return res.json();
    })
    .then(data => {
      console.log('Dados JSON recebidos:', data);
      alert(data.message || (data.success ? 'Agendamento realizado!' : 'Erro ao agendar.'));
      if (data.success) {
        // Atualiza o calendário, se existir e estiver global
        if (window.calendar && typeof window.calendar.refetchEvents === 'function') {
          window.calendar.refetchEvents();
        }
        form.reset();
        const modal = document.getElementById('agp-modal-front');
        if (modal) modal.style.display = 'none';
      }
    })
    .catch(error => {
      console.error('Erro na requisição fetch:', error);
      alert('Erro inesperado no envio.');
    });
  });
});
