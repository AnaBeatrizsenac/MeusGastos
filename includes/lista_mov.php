<?php
/**
 * Lista paginada de movimentações com filtros.
 * Entrada: $tipo_lista = 'entrada' | 'gasto' | '' (todas)
 */
$uid = usuario_id();
$porPagina = 10;
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
[$W, $par, $fv] = filtros_movimentacao($uid, $_GET, $tipo_lista);
$q = $fv['q'];
$catFiltro = $fv['categoria'];
$mesFiltro = $fv['mes'];
$tipoFiltro = $fv['tipo'];
$qsExport = http_build_query(array_filter(['tipo' => $tipoFiltro, 'categoria' => $catFiltro ?: null, 'mes' => $mesFiltro, 'q' => $q !== '' ? $q : null]));

$st = pdo()->prepare("SELECT COUNT(*) FROM movimentacoes m WHERE $W");
$st->execute($par);
$total = (int) $st->fetchColumn();
$paginas = max(1, (int) ceil($total / $porPagina));
$pagina = min($pagina, $paginas);

$st = pdo()->prepare(
    "SELECT m.*, c.nome AS categoria, c.icone, c.cor
       FROM movimentacoes m JOIN categorias c ON c.id = m.categoria_id
      WHERE $W ORDER BY m.data DESC, m.id DESC LIMIT $porPagina OFFSET " . (($pagina - 1) * $porPagina)
);
$st->execute($par);
$linhas = $st->fetchAll();

// meses disponíveis para o filtro
$st = pdo()->prepare("SELECT DISTINCT DATE_FORMAT(data,'%Y-%m') ym FROM movimentacoes WHERE usuario_id = ? ORDER BY ym DESC");
$st->execute([$uid]);
$meses = $st->fetchAll(PDO::FETCH_COLUMN);

$catsFiltro = array_filter(categorias(), fn($c) => $tipoFiltro === '' || $c['tipo'] === $tipoFiltro);
$mostrarTipo = $tipo_lista === '';

function url_pagina(int $p): string
{
    $g = $_GET;
    $g['pagina'] = $p;
    return '?' . http_build_query($g);
}
?>
<form method="get" class="filtros row g-2 mb-3">
  <?php if ($mostrarTipo): ?>
  <div class="col-12 col-sm-6 col-md-2">
    <select name="tipo" class="form-select" onchange="this.form.submit()">
      <option value="">Todos os tipos</option>
      <option value="entrada" <?= $tipoFiltro === 'entrada' ? 'selected' : '' ?>>Entradas</option>
      <option value="gasto" <?= $tipoFiltro === 'gasto' ? 'selected' : '' ?>>Gastos</option>
    </select>
  </div>
  <?php endif; ?>
  <div class="col-12 col-sm-6 col-md-3">
    <select name="categoria" class="form-select" onchange="this.form.submit()">
      <option value="0">Todas as categorias</option>
      <?php foreach ($catsFiltro as $c): ?>
        <option value="<?= (int) $c['id'] ?>" <?= $catFiltro === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['icone'] . ' ' . $c['nome']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12 col-sm-6 col-md-3">
    <select name="mes" class="form-select" onchange="this.form.submit()">
      <option value="">Todos os meses</option>
      <?php foreach ($meses as $m): ?>
        <option value="<?= e($m) ?>" <?= $mesFiltro === $m ? 'selected' : '' ?>><?= e(ucfirst(nome_mes($m))) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col col-sm-6 col-md">
    <div class="input-group">
      <input type="search" name="q" value="<?= e($q) ?>" class="form-control" placeholder="Buscar..." autocomplete="off">
      <button class="btn btn-outline-secondary" aria-label="Buscar"><i class="bi bi-search"></i></button>
    </div>
  </div>
  <div class="col-auto">
    <div class="dropdown">
      <button type="button" class="btn btn-outline-secondary" data-bs-toggle="dropdown"><i class="bi bi-download me-1"></i>Exportar</button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="exportar.php?formato=csv&amp;<?= e($qsExport) ?>">Planilha (CSV)</a></li>
        <li><a class="dropdown-item" href="exportar.php?formato=pdf&amp;<?= e($qsExport) ?>" target="_blank" rel="noopener">PDF (imprimir)</a></li>
      </ul>
    </div>
  </div>
</form>

<div class="cartao p-0">
  <div class="table-responsive">
    <table class="table tabela empilhavel align-middle mb-0">
      <thead>
        <tr>
          <th>Data</th><th>Descrição</th>
          <?php if ($mostrarTipo): ?><th>Tipo</th><?php endif; ?>
          <th>Categoria</th><th class="text-end">Valor</th><th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$linhas): ?>
        <tr class="vazio"><td colspan="<?= $mostrarTipo ? 6 : 5 ?>" class="text-center text-muted py-5">Nada por aqui com esses filtros.</td></tr>
      <?php endif; ?>
      <?php foreach ($linhas as $l):
          $dados = [
              'id' => (int) $l['id'], 'tipo' => $l['tipo'], 'descricao' => $l['descricao'],
              'valor' => number_format((float) $l['valor'], 2, ',', '.'), 'data' => $l['data'],
              'categoria_id' => (int) $l['categoria_id'], 'categoria' => $l['icone'] . ' ' . $l['categoria'],
              'observacao' => $l['observacao'] ?? '', 'data_br' => data_br($l['data']), 'valor_fmt' => brl((float) $l['valor']),
          ];
          $j = e(json_encode($dados, JSON_UNESCAPED_UNICODE));
      ?>
        <tr>
          <td class="c-data text-nowrap"><?= data_br($l['data']) ?></td>
          <td class="c-desc"><?= e($l['descricao']) ?></td>
          <?php if ($mostrarTipo): ?>
            <td class="c-tipo"><span class="badge-tipo <?= $l['tipo'] ?>"><?= $l['tipo'] === 'entrada' ? 'Entrada' : 'Saída' ?></span></td>
          <?php endif; ?>
          <td class="c-cat"><span class="cat-dot" style="background:<?= e($l['cor']) ?>"></span><?= e($l['categoria']) ?></td>
          <td class="c-valor text-end text-nowrap fw-semibold <?= $l['tipo'] === 'entrada' ? 'text-entrada' : 'text-gasto' ?>">
            <?= $l['tipo'] === 'entrada' ? '+' : '-' ?> <?= brl((float) $l['valor']) ?>
          </td>
          <td class="c-acoes text-end text-nowrap">
            <button class="btn-acao btn-ver" data-mov="<?= $j ?>" title="Visualizar"><i class="bi bi-eye"></i></button>
            <button class="btn-acao btn-editar" data-mov="<?= $j ?>" title="Editar"><i class="bi bi-pencil"></i></button>
            <button class="btn-acao btn-excluir" data-id="<?= (int) $l['id'] ?>" data-api="api/movimentacao.php" title="Excluir"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($paginas > 1): ?>
<nav class="mt-3 d-flex justify-content-center">
  <ul class="pagination mb-0">
    <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= e(url_pagina($pagina - 1)) ?>">&lsaquo;</a></li>
    <?php for ($p = 1; $p <= $paginas; $p++): ?>
      <li class="page-item <?= $p === $pagina ? 'active' : '' ?>"><a class="page-link" href="<?= e(url_pagina($p)) ?>"><?= $p ?></a></li>
    <?php endfor; ?>
    <li class="page-item <?= $pagina >= $paginas ? 'disabled' : '' ?>"><a class="page-link" href="<?= e(url_pagina($pagina + 1)) ?>">&rsaquo;</a></li>
  </ul>
</nav>
<?php endif; ?>
<p class="text-center small text-muted mt-2"><?= $total ?> registro(s)</p>
