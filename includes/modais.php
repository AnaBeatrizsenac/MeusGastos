<!-- Modal: nova / editar movimentação -->
<div class="modal fade" id="modalMov" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down modal-dialog-scrollable">
    <form class="modal-content form-mov" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="modalMovTitulo">Nova movimentação</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?= csrf_campo() ?>
        <?php campos_movimentacao(); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-md-primary">Salvar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: visualizar movimentação -->
<div class="modal fade" id="modalVer" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalhes da movimentação</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <dl class="row mb-0" id="verCorpo"></dl>
      </div>
    </div>
  </div>
</div>

<!-- Modal: confirmar exclusão (movimentação ou meta) -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
        <h6 class="mt-2">Excluir este registro?</h6>
        <p class="small text-muted mb-3" id="excluirTexto">Essa ação não pode ser desfeita.</p>
        <div class="d-flex gap-2 justify-content-center">
          <button class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-danger" id="confirmarExcluir">Excluir</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: versão / changelog -->
<div class="modal fade" id="modalVersao" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Novidades</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <?php foreach (CHANGELOG as $v): ?>
          <h6 class="mb-1">Versão <?= e($v['versao']) ?> <small class="text-muted fw-normal">— <?= data_br($v['data']) ?></small></h6>
          <ul class="ps-3 mb-3"><?php foreach ($v['itens'] as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
