document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('agp-form-agendamento');
  if (!form) return;

  let retorno = document.getElementById('agp-msg-retorno');
  if (!retorno) {
    retorno = document.createElement('div');
    retorno.id = 'agp-msg-retorno';
    retorno.style.marginTop = '10px';
    form.appendChild(retorno);
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(form);
    formData.append('action', 'agp_salvar_agendamento');
    formData.append('nonce', agpAjax.nonce);

    fetch(agpAjax.ajax_url, {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      retorno.innerHTML = data.message;
      retorno.style.color = data.success ? 'green' : 'red';
      if (data.success) form.reset();
    })
    .catch(() => {
      retorno.innerHTML = 'Erro inesperado.';
      retorno.style.color = 'red';
    });
  });
});

