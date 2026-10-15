(function () {
  const { $, $$ } = window.MD;
  const modalMeta = bootstrap.Modal.getOrCreateInstance($('#modalMeta'));
  const formMeta = $('#modalMeta form');

  function abrirMeta(d) {
    formMeta.reset();
    $('.form-erro', formMeta).classList.add('d-none');
    formMeta.elements['id'].value = d ? d.id : '';
    $('#modalMetaTitulo').textContent = d ? 'Editar meta' : 'Nova meta';
    if (d) {
      formMeta.nome.value = d.nome;
      formMeta.valor_alvo.value = d.valor_alvo;
      formMeta.prazo.value = d.prazo || '';
      const r = $$('input[name=icone]', formMeta).find((i) => i.value === d.icone);
      if (r) r.checked = true;
    }
    modalMeta.show();
  }

  $('#btnNovaMeta').addEventListener('click', () => abrirMeta(null));
  $$('.btn-editar-meta').forEach((b) => b.addEventListener('click', () => abrirMeta(JSON.parse(b.dataset.meta))));
  if (new URLSearchParams(location.search).has('nova')) abrirMeta(null);

  const modalDep = bootstrap.Modal.getOrCreateInstance($('#modalDeposito'));
  const formDep = $('#modalDeposito form');
  $$('.btn-deposito').forEach((b) => b.addEventListener('click', () => {
    formDep.reset();
    $('.form-erro', formDep).classList.add('d-none');
    formDep.elements['action'].value = b.dataset.acao;
    formDep.elements['id'].value = b.dataset.id;
    $('#modalDepositoTitulo').textContent = (b.dataset.acao === 'guardar' ? 'Guardar em ' : 'Retirar de ') + b.dataset.nome;
    modalDep.show();
  }));
})();
