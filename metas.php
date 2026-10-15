<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
exigir_login();

$metas = listar_metas(usuario_id());
$andamento = array_filter($metas, fn($m) => !$m['concluida_em']);
$concluidas = array_filter($metas, fn($m) => $m['concluida_em']);
$icones = ['🎯', '🎧', '💻', '✈️', '📱', '📚', '🚲', '🎁', '🏠', '🎓', '🎮', '👕'];

layout_topo('Metas', 'metas');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="titulo mb-0">Minhas Metas</h1>
  <button class="btn btn-md-primary" id="btnNovaMeta"><i class="bi bi-plus-lg me-1"></i>Nova meta</button>
</div>

<?php if (!$andamento): ?>
  <div class="cartao text-center text-muted py-5 mb-4">Nenhuma meta em andamento. Que tal criar a primeira?</div>
<?php endif; ?>
<div class="row g-3 mb-4">
<?php foreach ($andamento as $m): $j = e(json_encode(['id' => (int) $m['id'], 'nome' => $m['nome'], 'icone' => $m['icone'], 'valor_alvo' => number_format($m['valor_alvo'], 2, ',', '.'), 'prazo' => $m['prazo'] ?? ''], JSON_UNESCAPED_UNICODE)); ?>
  <div class="col-lg-6">
    <div class="cartao meta-card">
      <div class="d-flex align-items-start gap-3">
        <span class="meta-ico grande"><?= e($m['icone']) ?></span>
        <div class="flex-grow-1">
          <h5 class="mb-0"><?= e($m['nome']) ?></h5>
          <div class="text-muted small"><?= brl($m['valor_atual']) ?> de <?= brl($m['valor_alvo']) ?><?= $m['prazo'] ? ' · até ' . data_br($m['prazo']) : '' ?></div>
        </div>
        <div class="dropdown">
          <button class="btn-acao" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item btn-editar-meta" data-meta="<?= $j ?>"><i class="bi bi-pencil me-2"></i>Editar</button></li>
            <li><button class="dropdown-item text-danger btn-excluir" data-id="<?= (int) $m['id'] ?>" data-api="api/meta.php"><i class="bi bi-trash me-2"></i>Excluir</button></li>
          </ul>
        </div>
      </div>
      <div class="d-flex align-items-center gap-3 mt-3">
        <div class="progress flex-grow-1"><div class="progress-bar" style="width:<?= $m['pct'] ?>%"></div></div>
        <strong><?= $m['pct'] ?>%</strong>
      </div>
      <div class="d-flex justify-content-between align-items-center mt-2">
        <span class="small text-muted">Falta: <?= brl($m['falta']) ?></span>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-secondary btn-deposito" data-id="<?= (int) $m['id'] ?>" data-acao="retirar" data-nome="<?= e($m['nome']) ?>">Retirar</button>
          <button class="btn btn-sm btn-md-primary btn-deposito" data-id="<?= (int) $m['id'] ?>" data-acao="guardar" data-nome="<?= e($m['nome']) ?>">Guardar</button>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<div class="cartao concluidas">
  <h5 class="cartao-titulo">Metas concluídas</h5>
  <?php if (!$concluidas): ?>
    <div class="text-center text-muted py-4"><i class="bi bi-trophy fs-1 d-block mb-2"></i>Nenhuma meta concluída ainda.</div>
  <?php endif; ?>
  <?php foreach ($concluidas as $m): ?>
    <div class="meta-mini">
      <span class="meta-ico"><?= e($m['icone']) ?></span>
      <div class="flex-grow-1"><div class="fw-semibold"><?= e($m['nome']) ?></div><div class="small text-muted"><?= brl($m['valor_alvo']) ?> · concluída em <?= data_br($m['concluida_em']) ?></div></div>
      <span class="badge text-bg-success">100%</span>
      <button class="btn-acao btn-excluir" data-id="<?= (int) $m['id'] ?>" data-api="api/meta.php"><i class="bi bi-trash"></i></button>
    </div>
  <?php endforeach; ?>
</div>

<!-- Modal: nova / editar meta -->
<div class="modal fade" id="modalMeta" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down modal-dialog-scrollable">
    <form class="modal-content form-meta" autocomplete="off">
      <div class="modal-header"><h5 class="modal-title" id="modalMetaTitulo">Nova meta</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <?= csrf_campo() ?>
        <input type="hidden" name="action" value="salvar"><input type="hidden" name="id" value="">
        <div class="mb-3"><label class="form-label">Nome da meta</label><input type="text" name="nome" class="form-control" maxlength="100" required autocomplete="off" placeholder="Ex.: Comprar um fone de ouvido"></div>
        <div class="row g-3 mb-3">
          <div class="col-6"><label class="form-label">Valor da meta</label><input type="text" name="valor_alvo" class="form-control" inputmode="decimal" required autocomplete="off" placeholder="R$ 0,00"></div>
          <div class="col-6"><label class="form-label">Prazo <span class="text-muted">(opcional)</span></label><input type="date" name="prazo" class="form-control"></div>
        </div>
        <div class="mb-3"><label class="form-label">Ícone</label>
          <div class="icones"><?php foreach ($icones as $i => $ic): ?><label><input type="radio" name="icone" value="<?= e($ic) ?>" <?= $i === 0 ? 'checked' : '' ?>><span><?= e($ic) ?></span></label><?php endforeach; ?></div>
        </div>
        <div class="form-erro alert alert-danger py-2 small d-none"></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-md-primary">Salvar</button></div>
    </form>
  </div>
</div>

<!-- Modal: guardar / retirar -->
<div class="modal fade" id="modalDeposito" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <form class="modal-content form-meta" autocomplete="off">
      <div class="modal-header"><h5 class="modal-title" id="modalDepositoTitulo">Guardar</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <?= csrf_campo() ?>
        <input type="hidden" name="action" value=""><input type="hidden" name="id" value="">
        <label class="form-label">Valor</label><input type="text" name="valor" class="form-control mb-3" inputmode="decimal" required autocomplete="off" placeholder="R$ 0,00">
        <div class="form-erro alert alert-danger py-2 small d-none"></div>
      </div>
      <div class="modal-footer"><button class="btn btn-md-primary w-100">Confirmar</button></div>
    </form>
  </div>
</div>
<?php layout_fim(['assets/js/metas.js']); ?>
