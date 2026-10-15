<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
exigir_login();

$uid = usuario_id();
$u = usuario_atual();
$ym = mes_valido($_GET['mes'] ?? null) ?? date('Y-m');
$t = totais_mes($uid, $ym);
$cats = gastos_por_categoria($uid, $ym);
$metas = array_values(array_filter(listar_metas($uid), fn($m) => !$m['concluida_em']));
$economizado = total_economizado($uid);
$ultimas = ultimas_movimentacoes($uid, 5);
$alertas = gerar_alertas($uid, $ym);
$cq = conquistas($uid);
$maior = $cats[0] ?? null;

$mesAnt = date('Y-m', strtotime($ym . '-01 -1 month'));
$mesProx = date('Y-m', strtotime($ym . '-01 +1 month'));

layout_topo('Início', 'dashboard');
?>
<div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
  <div>
    <h1 class="titulo">Olá, <?= e(explode(' ', trim($u['nome']))[0]) ?>!</h1>
    <p class="text-muted mb-0">Como está o seu mês até agora.</p>
  </div>
  <div class="seletor-mes">
    <a href="?mes=<?= $mesAnt ?>" aria-label="Mês anterior"><i class="bi bi-chevron-left"></i></a>
    <span><?= e(ucfirst(nome_mes($ym))) ?></span>
    <a href="?mes=<?= $mesProx ?>" class="<?= $mesProx > date('Y-m') ? 'disabled' : '' ?>" aria-label="Próximo mês"><i class="bi bi-chevron-right"></i></a>
  </div>
</div>

<div class="row g-4">
  <div class="col-xl-9">
    <div class="row g-3 mb-3">
      <div class="col-6 col-md-3"><div class="kpi kpi-saldo"><i class="bi bi-wallet2"></i><span>Saldo disponível</span><strong class="<?= $t['saldo'] < 0 ? 'text-gasto' : '' ?>"><?= brl($t['saldo']) ?></strong></div></div>
      <div class="col-6 col-md-3"><div class="kpi kpi-entrada"><i class="bi bi-arrow-up-circle"></i><span>Total de entradas</span><strong><?= brl($t['entradas']) ?></strong></div></div>
      <div class="col-6 col-md-3"><div class="kpi kpi-gasto"><i class="bi bi-arrow-down-circle"></i><span>Total de gastos</span><strong><?= brl($t['gastos']) ?></strong></div></div>
      <div class="col-6 col-md-3"><div class="kpi kpi-meta"><i class="bi bi-piggy-bank"></i><span>Guardado nas metas</span><strong><?= brl($economizado) ?></strong></div></div>
    </div>

    <div class="row g-2 g-md-3 mb-3">
      <div class="col-4"><button class="btn btn-acao-grande verde w-100" data-nova-mov="entrada"><i class="bi bi-plus-lg me-1 me-md-2"></i><span class="d-none d-md-inline">Adicionar </span>entrada</button></div>
      <div class="col-4"><button class="btn btn-acao-grande vermelho w-100" data-nova-mov="gasto"><i class="bi bi-dash-lg me-1 me-md-2"></i><span class="d-none d-md-inline">Adicionar </span>despesa</button></div>
      <div class="col-4"><a href="metas.php?nova=1" class="btn btn-acao-grande amarelo w-100"><i class="bi bi-bullseye me-1 me-md-2"></i>Nova<span class="d-none d-sm-inline"> meta</span></a></div>
    </div>

    <?php foreach ($alertas as $a): ?>
      <div class="alert alert-<?= e($a['nivel']) ?> py-2 small d-flex align-items-center gap-2"><i class="bi bi-info-circle"></i><?= e($a['msg']) ?></div>
    <?php endforeach; ?>

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <div class="cartao h-100">
          <h5 class="cartao-titulo">Seus gastos por categoria</h5>
          <?php if (!$cats): ?>
            <p class="text-muted text-center py-5 mb-0">Nenhum gasto lançado em <?= e(nome_mes($ym)) ?>.</p>
          <?php else: ?>
            <div class="row align-items-center g-2">
              <div class="col-sm-6"><div class="rosca"><canvas id="graficoGastos" data-dados="<?= e(json_encode($cats, JSON_UNESCAPED_UNICODE)) ?>"></canvas>
                <div class="rosca-centro"><strong><?= brl($t['gastos']) ?></strong><small>Total de despesas</small></div></div></div>
              <div class="col-sm-6">
                <?php foreach ($cats as $c): ?>
                  <div class="legenda"><span class="cat-dot" style="background:<?= e($c['cor']) ?>"></span><span><?= e($c['icone'] . ' ' . $c['nome']) ?></span><span class="ms-auto text-muted small"><?= number_format($c['pct'], 0) ?>%</span></div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php if ($maior): ?><p class="small text-muted mt-3 mb-0">Maior volume: <strong><?= e($maior['nome']) ?></strong> (<?= brl($maior['total']) ?>)</p><?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-md-6">
        <div class="cartao h-100">
          <div class="d-flex justify-content-between"><h5 class="cartao-titulo">Minhas metas</h5><a href="metas.php" class="small">Ver todas →</a></div>
          <?php if (!$metas): ?><p class="text-muted text-center py-5 mb-0">Nenhuma meta em andamento. <a href="metas.php?nova=1">Criar uma</a></p><?php endif; ?>
          <?php foreach (array_slice($metas, 0, 3) as $m): ?>
            <div class="meta-mini">
              <span class="meta-ico"><?= e($m['icone']) ?></span>
              <div class="flex-grow-1">
                <div class="fw-semibold small"><?= e($m['nome']) ?></div>
                <div class="text-muted small"><?= brl($m['valor_atual']) ?> / <?= brl($m['valor_alvo']) ?></div>
                <div class="progress"><div class="progress-bar" style="width:<?= $m['pct'] ?>%"></div></div>
              </div>
              <span class="small fw-bold"><?= $m['pct'] ?>%</span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="cartao">
      <div class="d-flex justify-content-between"><h5 class="cartao-titulo">Últimas movimentações</h5><a href="historico.php" class="small">Ver todas →</a></div>
      <?php if (!$ultimas): ?><p class="text-muted text-center py-4 mb-0">Ainda não lançou nada. Comece por uma entrada ou um gasto.</p><?php endif; ?>
      <div class="table-responsive"><table class="table tabela align-middle mb-0"><tbody>
        <?php foreach ($ultimas as $l): ?>
          <tr>
            <td class="text-nowrap text-muted"><?= data_br($l['data']) ?></td>
            <td><?= e($l['descricao']) ?></td>
            <td class="d-none d-sm-table-cell"><span class="cat-dot" style="background:<?= e($l['cor']) ?>"></span><?= e($l['categoria']) ?></td>
            <td class="text-end fw-semibold text-nowrap <?= $l['tipo'] === 'entrada' ? 'text-entrada' : 'text-gasto' ?>"><?= $l['tipo'] === 'entrada' ? '+' : '-' ?> <?= brl((float) $l['valor']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody></table></div>
    </div>
  </div>

  <div class="col-xl-3">
    <div class="cartao dica mb-3">
      <h6><i class="bi bi-lightbulb me-2"></i>Dica do dia</h6>
      <p class="mb-2"><?= e(dica_do_dia()) ?></p>
      
    </div>
    <div class="cartao conquistas mb-3">
      <h6><i class="bi bi-trophy me-2"></i>Suas conquistas</h6>
      <div class="d-flex justify-content-between mt-3"><span>Nível <?= $cq['nivel'] ?> — <strong class="dourado"><?= e($cq['titulo']) ?></strong></span><span class="small"><?= $cq['progresso'] ?>%</span></div>
      <div class="progress mb-3"><div class="progress-bar" style="width:<?= $cq['progresso'] ?>%"></div></div>
      <div class="conq-item"><i class="bi bi-trophy"></i><?= $cq['concluidas'] ?> <?= $cq['concluidas'] === 1 ? 'meta concluída' : 'metas concluídas' ?></div>
      <div class="conq-item"><i class="bi bi-coin"></i><?= brl($cq['economizado']) ?> economizados</div>
      <div class="conq-item"><i class="bi bi-calendar-check"></i><?= $cq['sequencia'] ?> <?= $cq['sequencia'] === 1 ? 'dia seguido' : 'dias seguidos' ?> anotando os gastos</div>
    </div>
    <div class="cartao frase-card"><span class="cursiva">disciplina hoje,<br>liberdade amanhã</span></div>
  </div>
</div>
<?php layout_fim(['https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', 'assets/js/dashboard.js']); ?>
