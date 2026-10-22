(function () {
  'use strict';

  const csrf = () => (document.querySelector('meta[name="csrf"]') || {}).content || '';
  const $ = (s, r) => (r || document).querySelector(s);
  const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));

  async function enviar(url, formData) {
    try {
      const r = await fetch(url, { method: 'POST', body: formData, credentials: 'same-origin' });
      const j = await r.json().catch(() => ({ ok: false, erros: ['Resposta inválida do servidor.'] }));
      if (r.status === 401) { location.href = 'login.php'; }
      return j;
    } catch (e) {
      return { ok: false, erros: ['Falha de conexão. Tente novamente.'] };
    }
  }
  window.MD = { enviar, csrf, $, $$ };

  function mostrarErros(form, j) {
    const box = $('.form-erro', form);
    if (!box) return;
    const lista = j.erros || [j.msg || 'Erro inesperado.'];
    box.innerHTML = lista.map((t) => '<div>' + t.replace(/[<>&]/g, '') + '</div>').join('');
    box.classList.remove('d-none');
  }

  /* ---- tema: claro | escuro | auto ---- */
  const escuroSistema = window.matchMedia('(prefers-color-scheme: dark)');
  function temaSalvo() {
    try { return localStorage.getItem('md_tema') || 'auto'; } catch (e) { return 'auto'; }
  }
  function aplicarTema(t) {
    const escuro = t === 'dark' || (t === 'auto' && escuroSistema.matches);
    document.documentElement.setAttribute('data-bs-theme', escuro ? 'dark' : 'light');
    const b = $('#btnTema i');
    if (b) b.className = 'bi ' + (t === 'dark' ? 'bi-moon-stars' : t === 'light' ? 'bi-sun' : 'bi-circle-half');
    $$('input[name=tema_opcao]').forEach((r) => { r.checked = r.value === t; });
    document.dispatchEvent(new CustomEvent('md:tema'));
  }
  function salvarTema(t) {
    try { localStorage.setItem('md_tema', t); } catch (e) { /* navegador bloqueou o armazenamento */ }
    aplicarTema(t);
  }
  aplicarTema(temaSalvo());
  escuroSistema.addEventListener('change', () => { if (temaSalvo() === 'auto') aplicarTema('auto'); });
  const btnTema = $('#btnTema');
  if (btnTema) btnTema.addEventListener('click', () => {
    const ordem = ['auto', 'light', 'dark'];
    salvarTema(ordem[(ordem.indexOf(temaSalvo()) + 1) % 3]);
  });
  $$('input[name=tema_opcao]').forEach((r) => r.addEventListener('change', () => salvarTema(r.value)));

  /* ---- banner de cookies (LGPD) ---- */
  const bannerCookies = document.getElementById('bannerCookies');
  if (bannerCookies) {
    let aceito = false;
    try {
      aceito = localStorage.getItem('md_cookie_consent') === '1';
    } catch (e) {
      aceito = false;
    }
    if (!aceito) {
      bannerCookies.style.display = 'block';
    }
    const btnAceitar = document.getElementById('btnAceitarCookies');
    if (btnAceitar) {
      btnAceitar.addEventListener('click', () => {
        try {
          localStorage.setItem('md_cookie_consent', '1');
        } catch (e) {}
        bannerCookies.style.transition = 'opacity .25s ease, transform .25s ease';
        bannerCookies.style.opacity = '0';
        bannerCookies.style.transform = 'translateY(15px)';
        setTimeout(() => {
          bannerCookies.style.display = 'none';
        }, 260);
      });
    }
  }

  /* ---- mostrar/ocultar senha ---- */
  $$('.ver-senha').forEach((b) => b.addEventListener('click', () => {
    const inp = b.parentElement.querySelector('input');
    const mostrar = inp.type === 'password';
    inp.type = mostrar ? 'text' : 'password';
    b.querySelector('i').className = mostrar ? 'bi bi-eye-slash' : 'bi bi-eye';
  }));

  /* ---- formulário de movimentação (inline e modal) ---- */
  $$('form.form-mov').forEach((form) => {
    form.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const btn = form.querySelector('[type=submit], button:not([type])');
      if (btn) btn.disabled = true;
      const fd = new FormData(form);
      fd.set('action', 'salvar');
      const j = await enviar('api/movimentacao.php', fd);
      if (j.ok) { location.reload(); return; }
      mostrarErros(form, j);
      if (btn) btn.disabled = false;
    });
  });

  const modalMovEl = $('#modalMov');
  if (modalMovEl) {
    const modalMov = bootstrap.Modal.getOrCreateInstance(modalMovEl);
    const form = $('form', modalMovEl);
    const selCat = form.categoria_id;

    function filtrarCategorias(tipo) {
      Array.from(selCat.options).forEach((o) => {
        const ok = !o.value || o.dataset.tipo === tipo;
        o.hidden = !ok;
        o.disabled = !ok;
      });
    }

    function abrir(dados, tipo) {
      form.reset();
      $('.form-erro', form).classList.add('d-none');
      const t = dados ? dados.tipo : tipo;
      form.tipo.value = t;
      filtrarCategorias(t);
      form.elements['id'].value = dados ? dados.id : '';
      $('#modalMovTitulo').textContent = dados
        ? (t === 'entrada' ? 'Editar entrada' : 'Editar gasto')
        : (t === 'entrada' ? 'Nova entrada' : 'Novo gasto');
      form.descricao.placeholder = t === 'entrada' ? 'Ex.: Salário, mesada, freelancer...' : 'Ex.: Lanche, ônibus, mercado...';
      if (dados) {
        form.descricao.value = dados.descricao;
        form.valor.value = dados.valor;
        form.data.value = dados.data;
        form.categoria_id.value = dados.categoria_id;
        form.observacao.value = dados.observacao || '';
      }
      modalMov.show();
    }

    $$('[data-nova-mov]').forEach((b) => b.addEventListener('click', () => abrir(null, b.dataset.novaMov)));
    $$('.btn-editar').forEach((b) => b.addEventListener('click', () => abrir(JSON.parse(b.dataset.mov))));

    const modalVer = bootstrap.Modal.getOrCreateInstance($('#modalVer'));
    $$('.btn-ver').forEach((b) => b.addEventListener('click', () => {
      const d = JSON.parse(b.dataset.mov);
      const linhas = [
        ['Tipo', d.tipo === 'entrada' ? 'Entrada' : 'Saída'],
        ['Descrição', d.descricao],
        ['Valor', d.valor_fmt],
        ['Categoria', d.categoria],
        ['Data', d.data_br],
        ['Observação', d.observacao || '—'],
      ];
      const corpo = $('#verCorpo');
      corpo.innerHTML = '';
      linhas.forEach(([k, v]) => {
        const dt = document.createElement('dt'); dt.className = 'col-4'; dt.textContent = k;
        const dd = document.createElement('dd'); dd.className = 'col-8'; dd.textContent = v;
        corpo.append(dt, dd);
      });
      modalVer.show();
    }));
  }

  /* ---- exclusão com confirmação (movimentação e meta) ---- */
  const modalExcluirEl = $('#modalExcluir');
  if (modalExcluirEl) {
    const modalExcluir = bootstrap.Modal.getOrCreateInstance(modalExcluirEl);
    let alvo = null;
    document.addEventListener('click', (ev) => {
      const b = ev.target.closest('.btn-excluir');
      if (!b) return;
      alvo = { id: b.dataset.id, api: b.dataset.api };
      modalExcluir.show();
    });
    $('#confirmarExcluir').addEventListener('click', async () => {
      if (!alvo) return;
      const fd = new FormData();
      fd.set('action', 'excluir'); fd.set('id', alvo.id); fd.set('csrf', csrf());
      const j = await enviar(alvo.api, fd);
      if (j.ok) location.reload();
      else { alert((j.erros || ['Erro ao excluir.']).join('\n')); modalExcluir.hide(); }
    });
  }

  /* ---- formulários de meta (nova/editar/guardar/retirar) ---- */
  $$('form.form-meta').forEach((form) => {
    form.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const btn = form.querySelector('button:not([type=button]):not(.btn-close)');
      if (btn) btn.disabled = true;
      const j = await enviar('api/meta.php', new FormData(form));
      if (j.ok) { location.reload(); return; }
      mostrarErros(form, j);
      if (btn) btn.disabled = false;
    });
  });
})();
