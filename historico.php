<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
exigir_login();
layout_topo('Histórico', 'historico');
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <h1 class="titulo mb-0">Histórico financeiro</h1>
  <div class="d-flex gap-2">
    <button class="btn btn-md-primary" data-nova-mov="entrada"><i class="bi bi-plus-lg me-1"></i>Entrada</button>
    <button class="btn btn-danger" data-nova-mov="gasto"><i class="bi bi-dash-lg me-1"></i>Gasto</button>
  </div>
</div>
<?php $tipo_lista = ''; require __DIR__ . '/includes/lista_mov.php'; ?>
<?php layout_fim(); ?>
