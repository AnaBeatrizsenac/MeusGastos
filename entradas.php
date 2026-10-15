<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
exigir_login();
layout_topo('Entradas', 'entradas');
?>
<h1 class="titulo mb-3">Nova Entrada</h1>
<div class="row g-4 mb-4">
  <div class="col-lg-7">
    <form class="cartao form-mov" autocomplete="off">
      <?= csrf_campo() ?>
      <?php campos_movimentacao('entrada'); ?>
      <button class="btn btn-md-primary px-5">Salvar</button>
    </form>
  </div>
  <div class="col-lg-5 d-none d-lg-block">
    <div class="cartao h-100 painel-verde text-center d-flex flex-column justify-content-center">
      <i class="bi bi-cash-coin display-3"></i>
      <p class="fs-5 mt-3 mb-0">Registre suas entradas e mantenha seu controle sempre em dia!</p>
    </div>
  </div>
</div>

<h2 class="h4 mb-3">Minhas entradas</h2>
<?php $tipo_lista = 'entrada'; require __DIR__ . '/includes/lista_mov.php'; ?>
<?php layout_fim(); ?>
