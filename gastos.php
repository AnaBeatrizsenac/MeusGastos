<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
exigir_login();
layout_topo('Gastos', 'gastos');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="titulo mb-0">Meus Gastos</h1>
  <button class="btn btn-md-primary" data-nova-mov="gasto"><i class="bi bi-plus-lg me-1"></i>Novo gasto</button>
</div>
<?php $tipo_lista = 'gasto'; require __DIR__ . '/includes/lista_mov.php'; ?>
<?php layout_fim(); ?>
