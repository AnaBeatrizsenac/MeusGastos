<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
exigir_login();

$uid = usuario_id();
$n = (int) ($_GET['p'] ?? 6);
if (!in_array($n, [3, 6, 12], true)) {
    $n = 6;
}

$serie = serie_mensal($uid, $n);
$ini = $serie[0]['ym'] . '-01';
$fim = date('Y-m-d', strtotime(end($serie)['ym'] . '-01 +1 month'));
$de = $ini;
$ate = date('Y-m-d', strtotime($fim . ' -1 day'));

$totEnt = array_sum(array_column($serie, 'entradas'));
$totGas = array_sum(array_column($serie, 'gastos'));
$saldo = $totEnt - $totGas;
$poupanca = $totEnt > 0 ? round($saldo / $totEnt * 100) : null;
$mediaGasto = $totGas / $n;

$atual = end($serie);
$anterior = $serie[count($serie) - 2] ?? null;
$variacao = ($anterior && $anterior['gastos'] > 0) ? round(($atual['gastos'] - $anterior['gastos']) / $anterior['gastos'] * 100) : null;

$cats = gastos_categoria_intervalo($uid, $ini, $fim);
$maiores = maiores_gastos($uid, $ini, $fim, 5);
$exp = http_build_query(['de' => $de, 'ate' => $ate]);

layout_topo('Relatórios', 'relatorios');
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
  <div>
    <h1 class="titulo mb-0">Relatórios</h1>
    <p class="text-muted mb-0">De <?= e(nome_mes($serie[0]['ym'])) ?> até agora.</p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <div class="btn-group" role="group" aria-label="Período">
      <?php foreach ([3, 6, 12] as $o): ?>
        <a href="?p=<?= $o ?>" class="btn btn-sm <?= $o === $n ? 'btn-md-primary' : 'btn-outline-secondary' ?>"><?= $o ?> meses</a>
      <?php endforeach; ?>
    </div>
    <div class="dropdown">
      <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown"><i class="bi bi-download me-1"></i>Exportar período</button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="exportar.php?formato=csv&amp;<?= e($exp) ?>">Planilha (CSV)</a></li>
        <li><a class="dropdown-item" href="exportar.php?formato=pdf&amp;<?= e($exp) ?>" target="_blank" rel="noopener">PDF (imprimir)</a></li>
      </ul>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3"><div class="kpi kpi-entrada"><span>Recebido no período</span><strong><?= brl($totEnt) ?></strong></div></div>
  <div class="col-6 col-md-3"><div class="kpi kpi-gasto"><span>Gasto no período</span><strong><?= brl($totGas) ?></strong></div></div>
  <div class="col-6 col-md-3"><div class="kpi kpi-saldo"><span>Sobrou</span><strong class="<?= $saldo < 0 ? 'text-gasto' : '' ?>"><?= brl($saldo) ?></strong>
    <small class="text-muted"><?= $poupanca === null ? 'sem entradas' : $poupanca . '% do que entrou' ?></small></div></div>
  <div class="col-6 col-md-3"><div class="kpi kpi-meta"><span>Média de gastos por mês</span><strong><?= brl($mediaGasto) ?></strong>
    <small class="text-muted"><?= $variacao === null ? '—' : ($variacao > 0 ? '+' : '') . $variacao . '% este mês vs. o anterior' ?></small></div></div>
</div>

<div class="cartao mb-3">
  <h5 class="cartao-titulo">Mês a mês</h5>
  <div class="grafico-barras"><canvas id="graficoMeses" data-serie="<?= e(json_encode($serie)) ?>"></canvas></div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="cartao h-100">
      <h5 class="cartao-titulo">Para onde foi o dinheiro</h5>
      <?php if (!$cats): ?><p class="text-muted mb-0">Nenhum gasto no período.</p><?php endif; ?>
      <?php foreach ($cats as $c): ?>
        <div class="linha-cat">
          <div class="d-flex justify-content-between small"><span><?= e($c['icone'] . ' ' . $c['nome']) ?></span><span><strong><?= brl($c['total']) ?></strong> <span class="text-muted">· <?= number_format($c['pct'], 0) ?>%</span></span></div>
          <div class="barra-cat"><span style="width:<?= max(2, $c['pct']) ?>%;background:<?= e($c['cor']) ?>"></span></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="cartao h-100">
      <h5 class="cartao-titulo">Cinco maiores gastos</h5>
      <?php if (!$maiores): ?><p class="text-muted mb-0">Nada por aqui ainda.</p><?php endif; ?>
      <?php foreach ($maiores as $i => $m): ?>
        <div class="d-flex gap-3 align-items-baseline py-2 <?= $i ? 'borda-topo' : '' ?>">
          <span class="ranking"><?= $i + 1 ?></span>
          <div class="flex-grow-1"><div><?= e($m['descricao']) ?></div><div class="small text-muted"><?= e($m['categoria']) ?> · <?= data_br($m['data']) ?></div></div>
          <strong class="text-gasto text-nowrap"><?= brl((float) $m['valor']) ?></strong>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php layout_fim(['https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', 'assets/js/relatorios.js']); ?>
