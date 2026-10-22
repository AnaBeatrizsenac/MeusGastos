<?php
declare(strict_types=1);

require_once __DIR__ . '/financeiro.php';
require_once __DIR__ . '/versao.php';

function logo_svg(int $t = 36): string
{
    return '<svg width="' . $t . '" height="' . $t . '" viewBox="0 0 48 48" aria-hidden="true">'
        . '<rect x="4" y="14" width="40" height="28" rx="6" fill="#15803d"/>'
        . '<rect x="30" y="24" width="14" height="10" rx="3" fill="#bbf7d0"/><circle cx="35" cy="29" r="1.8" fill="#15803d"/>'
        . '<path d="M22 14C22 6 14 4 8 6c0 7 6 10 14 8z" fill="#22c55e"/>'
        . '<path d="M24 14c0-7 7-10 14-8-1 7-7 10-14 8z" fill="#16a34a"/></svg>';
}

function banner_cookies_html(): void
{
    ?>
<div id="bannerCookies" class="cookie-banner" style="display:none;" role="region" aria-label="Consentimento de Cookies">
  <div class="cookie-banner-box">
    <div class="cookie-banner-icon">
      <i class="bi bi-shield-check"></i>
    </div>
    <div class="cookie-banner-info">
      <strong>Privacidade e Cookies Essenciais</strong>
      <p class="mb-2 text-muted small">
        Utilizamos cookies de sessão (para manter seu login seguro) e salvamento local para lembrar sua preferência de Tema Escuro ou Claro. Não utilizamos cookies de rastreamento publicitário nem compartilhamos seus dados com terceiros.
      </p>
      <div class="cookie-banner-links small">
        <a href="privacidade.php" target="_blank" class="fw-semibold text-decoration-underline me-3">Política de Privacidade</a>
        <a href="termos.php" target="_blank" class="fw-semibold text-decoration-underline">Termos de Uso</a>
      </div>
    </div>
    <div class="cookie-banner-actions">
      <button type="button" id="btnAceitarCookies" class="btn btn-sm btn-md-primary px-3 py-2">
        Entendi e aceito
      </button>
    </div>
  </div>
</div>
    <?php
}

function layout_topo(string $titulo, string $ativo): void
{
    $u = usuario_atual();
    $ym = date('Y-m');
    $alertas = gerar_alertas($u['id'], $ym);
    $relevantes = alertas_qtd_relevantes($alertas);
    $iniciais = mb_strtoupper(mb_substr($u['nome'], 0, 1) . (mb_substr(strrchr(' ' . $u['nome'], ' '), 1, 1) ?: ''));
    $primeiro = explode(' ', trim($u['nome']))[0];

    $itens = [
        'dashboard' => ['dashboard.php', 'bi-house-door', 'Início'],
        'entradas' => ['entradas.php', 'bi-plus-circle', 'Entradas'],
        'gastos' => ['gastos.php', 'bi-bag', 'Gastos'],
        'metas' => ['metas.php', 'bi-bullseye', 'Metas'],
        'historico' => ['historico.php', 'bi-clock-history', 'Histórico'],
        'relatorios' => ['relatorios.php', 'bi-graph-up', 'Relatórios'],
    ];
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#17402b">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Meu Dinheiro">
<link rel="manifest" href="manifest.webmanifest">
<link rel="icon" type="image/svg+xml" href="assets/icone.svg">
<link rel="apple-touch-icon" href="assets/icone.svg">
<meta name="csrf" content="<?= e(csrf_token()) ?>">
<title><?= e($titulo) ?> — Meu Dinheiro</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Caveat:wght@600&display=swap">
<script>(function(){var t='auto';try{t=localStorage.getItem('md_tema')||'auto'}catch(e){}var d=t==='dark'||(t==='auto'&&window.matchMedia('(prefers-color-scheme: dark)').matches);document.documentElement.setAttribute('data-bs-theme',d?'dark':'light')})();</script>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/responsivo.css">
</head>
<body class="app">
<aside class="sidebar d-none d-lg-flex">
  <a class="brand" href="dashboard.php"><?= logo_svg(38) ?><span><strong>Meu Dinheiro</strong><small>Controle Financeiro para Estudantes</small></span></a>
  <nav class="side-nav">
    <?php foreach ($itens as $k => [$href, $ico, $txt]): ?>
      <a href="<?= $href ?>" class="<?= $ativo === $k ? 'active' : '' ?>"><i class="bi <?= $ico ?>"></i><?= $txt ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="side-bottom">
    <a href="configuracoes.php" class="<?= $ativo === 'config' ? 'active' : '' ?>"><i class="bi bi-gear"></i>Configurações</a>
    <a href="logout.php?t=<?= e(csrf_token()) ?>"><i class="bi bi-box-arrow-left"></i>Sair</a>
    <p class="side-frase">Anotou, controlou.</p>
    <button class="versao-badge" data-bs-toggle="modal" data-bs-target="#modalVersao" title="Novidades desta versão">v<?= APP_VERSION ?></button>
  </div>
</aside>

<div class="main-wrap">
  <header class="topbar">
    <a class="brand d-lg-none" href="dashboard.php"><?= logo_svg(32) ?><strong>Meu Dinheiro</strong></a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <button class="icon-btn" id="btnTema" aria-label="Trocar tema" title="Tema"><i class="bi bi-circle-half"></i></button>
      <div class="dropdown">
        <button class="icon-btn" data-bs-toggle="dropdown" aria-label="Alertas">
          <i class="bi bi-bell"></i><?php if ($relevantes): ?><span class="dot"></span><?php endif; ?>
        </button>
        <div class="dropdown-menu dropdown-menu-end p-2 alert-menu">
          <div class="fw-bold px-2 pb-1">Alertas</div>
          <?php if (!$alertas): ?><div class="text-muted small px-2 pb-1">Tudo em ordem por aqui.</div><?php endif; ?>
          <?php foreach ($alertas as $a): ?><div class="alert alert-<?= e($a['nivel']) ?> py-2 px-2 small mb-1"><?= e($a['msg']) ?></div><?php endforeach; ?>
        </div>
      </div>
      <div class="dropdown">
        <button class="user-btn" data-bs-toggle="dropdown">
          <span class="avatar"><?= e($iniciais) ?></span><span class="d-none d-sm-inline"><?= e($primeiro) ?></span><i class="bi bi-chevron-down small"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="configuracoes.php"><i class="bi bi-person me-2"></i>Perfil</a></li>
          <li><a class="dropdown-item" href="logout.php?t=<?= e(csrf_token()) ?>"><i class="bi bi-box-arrow-left me-2"></i>Sair</a></li>
        </ul>
      </div>
    </div>
  </header>
  <main class="content">
    <?php foreach (flash_pegar() as $f): ?>
      <div class="alert alert-<?= e($f['tipo']) ?> alert-dismissible fade show" role="alert"><?= e($f['msg']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endforeach; ?>
<?php
    $GLOBALS['nav_itens'] = $itens;
    $GLOBALS['nav_ativo'] = $ativo;
}

function layout_fim(array $scripts = []): void
{
    require __DIR__ . '/modais.php';
    ?>
  </main>
</div>

<nav class="bottom-nav d-lg-none">
  <?php foreach ($GLOBALS['nav_itens'] as $k => [$href, $ico, $txt]): ?>
    <a href="<?= $href ?>" class="<?= $GLOBALS['nav_ativo'] === $k ? 'active' : '' ?>"><i class="bi <?= $ico ?>"></i><span><?= $txt ?></span></a>
  <?php endforeach; ?>
</nav>
<?php banner_cookies_html(); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
<?php foreach ($scripts as $s): ?><script src="<?= e($s) ?>"></script><?php endforeach; ?>
</body>
</html>
<?php
}

/** Campos comuns do formulário de movimentação. */
function campos_movimentacao(string $tipoFixo = ''): void
{
    $cats = categorias();
    ?>
    <input type="hidden" name="id" value="">
    <input type="hidden" name="tipo" value="<?= e($tipoFixo) ?>">
    <div class="mb-3">
      <label class="form-label">Descrição</label>
      <input type="text" name="descricao" class="form-control" maxlength="120" required autocomplete="off"
             placeholder="<?= $tipoFixo === 'entrada' ? 'Ex.: Salário, mesada, freelancer...' : 'Ex.: Lanche, ônibus, mercado...' ?>">
    </div>
    <div class="row g-3 mb-3">
      <div class="col-6">
        <label class="form-label">Valor</label>
        <input type="text" name="valor" class="form-control" inputmode="decimal" required autocomplete="off" placeholder="R$ 0,00">
      </div>
      <div class="col-6">
        <label class="form-label">Data</label>
        <input type="date" name="data" class="form-control" required max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Categoria</label>
      <select name="categoria_id" class="form-select" required>
        <option value="">Selecione uma categoria</option>
        <?php foreach ($cats as $c): if ($tipoFixo !== '' && $c['tipo'] !== $tipoFixo) continue; ?>
          <option value="<?= (int) $c['id'] ?>" data-tipo="<?= e($c['tipo']) ?>"><?= e($c['icone'] . ' ' . $c['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Observação <span class="text-muted">(opcional)</span></label>
      <input type="text" name="observacao" class="form-control" maxlength="255" autocomplete="off">
    </div>
    <div class="form-erro alert alert-danger py-2 small d-none"></div>
    <?php
}
