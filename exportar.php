<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/financeiro.php';
exigir_login();

const LIMITE_EXPORTACAO = 5000;

$uid = usuario_id();
$u = usuario_atual();
$formato = ($_GET['formato'] ?? 'csv') === 'pdf' ? 'pdf' : 'csv';
[$W, $par, $fv] = filtros_movimentacao($uid, $_GET);

$st = pdo()->prepare(
    "SELECT m.data, m.tipo, m.descricao, m.valor, m.observacao, c.nome AS categoria
       FROM movimentacoes m JOIN categorias c ON c.id = m.categoria_id
      WHERE $W ORDER BY m.data DESC, m.id DESC LIMIT " . LIMITE_EXPORTACAO
);
$st->execute($par);
$linhas = $st->fetchAll();

$entradas = $gastos = 0.0;
foreach ($linhas as $l) {
    if ($l['tipo'] === 'entrada') { $entradas += (float) $l['valor']; } else { $gastos += (float) $l['valor']; }
}

/** Evita que planilhas interpretem texto do usuário como fórmula. */
function csv_seguro(string $t): string
{
    return preg_match('/^[=+\-@\t\r]/', $t) ? "'" . $t : $t;
}

/** Evita que planilhas interpretem texto do usuário como fórmula. */

if ($formato === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="meu-dinheiro-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM: o Excel abre em UTF-8
    fputcsv($out, ['Data', 'Tipo', 'Categoria', 'Descrição', 'Valor', 'Observação'], ';');
    foreach ($linhas as $l) {
        fputcsv($out, [
            data_br($l['data']),
            $l['tipo'] === 'entrada' ? 'Entrada' : 'Saída',
            $l['categoria'],
            csv_seguro($l['descricao']),
            number_format((float) $l['valor'], 2, ',', ''),
            csv_seguro((string) $l['observacao']),
        ], ';');
    }
    fclose($out);
    exit;
}

/* ---- "PDF": página limpa para imprimir / salvar como PDF pelo navegador ---- */
$partes = [];
if ($fv['tipo'] !== '') { $partes[] = $fv['tipo'] === 'entrada' ? 'somente entradas' : 'somente gastos'; }
if ($fv['mes']) { $partes[] = nome_mes($fv['mes']); }
if (!empty($_GET['de']) && data_valida((string) $_GET['de'])) { $partes[] = 'a partir de ' . data_br($_GET['de']); }
if (!empty($_GET['ate']) && data_valida((string) $_GET['ate'])) { $partes[] = 'até ' . data_br($_GET['ate']); }
if ($fv['q'] !== '') { $partes[] = 'busca "' . $fv['q'] . '"'; }
$filtroTxt = $partes ? implode(' · ', $partes) : 'todas as movimentações';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Extrato — Meu Dinheiro</title>
<style>
  body { font: 13px/1.45 Georgia, 'Times New Roman', serif; color: #222; margin: 0; padding: 28px 34px; }
  h1 { font-size: 22px; margin: 0 0 2px; }
  .sub { color: #666; margin-bottom: 18px; }
  .resumo { display: flex; gap: 28px; margin: 0 0 18px; padding: 10px 0; border-top: 1px solid #999; border-bottom: 1px solid #999; }
  .resumo div span { display: block; font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: .05em; }
  .resumo div strong { font-size: 16px; }
  table { width: 100%; border-collapse: collapse; }
  th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1.5px solid #333; padding: 5px 6px; }
  td { padding: 5px 6px; border-bottom: 1px solid #ddd; vertical-align: top; }
  td.v, th.v { text-align: right; white-space: nowrap; }
  .barra { background: #f2f2f2; padding: 10px 14px; margin: -28px -34px 22px; display: flex; justify-content: space-between; align-items: center; font-family: sans-serif; }
  .barra button { padding: 6px 14px; cursor: pointer; }
  .rodape { margin-top: 16px; font-size: 11px; color: #777; }
  @media print { .barra { display: none; } body { padding: 0; } }
</style>
</head>
<body>
<div class="barra"><span>Na janela de impressão, escolha <strong>Salvar como PDF</strong>.</span><button onclick="window.print()">Imprimir / salvar PDF</button></div>
<h1>Extrato financeiro</h1>
<div class="sub"><?= e($u['nome']) ?> — <?= e($filtroTxt) ?> — emitido em <?= date('d/m/Y H:i') ?></div>
<div class="resumo">
  <div><span>Entradas</span><strong><?= brl($entradas) ?></strong></div>
  <div><span>Gastos</span><strong><?= brl($gastos) ?></strong></div>
  <div><span>Resultado</span><strong><?= brl($entradas - $gastos) ?></strong></div>
  <div><span>Registros</span><strong><?= count($linhas) ?></strong></div>
</div>
<table>
  <thead><tr><th>Data</th><th>Descrição</th><th>Categoria</th><th>Tipo</th><th class="v">Valor</th></tr></thead>
  <tbody>
  <?php foreach ($linhas as $l): ?>
    <tr>
      <td><?= data_br($l['data']) ?></td>
      <td><?= e($l['descricao']) ?><?= $l['observacao'] ? '<br><small>' . e($l['observacao']) . '</small>' : '' ?></td>
      <td><?= e($l['categoria']) ?></td>
      <td><?= $l['tipo'] === 'entrada' ? 'Entrada' : 'Saída' ?></td>
      <td class="v"><?= $l['tipo'] === 'entrada' ? '+' : '−' ?> <?= brl((float) $l['valor']) ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$linhas): ?><tr><td colspan="5">Nenhum registro para os filtros escolhidos.</td></tr><?php endif; ?>
  </tbody>
</table>
<?php if (count($linhas) >= LIMITE_EXPORTACAO): ?><p class="rodape">Exibindo os <?= LIMITE_EXPORTACAO ?> registros mais recentes.</p><?php endif; ?>
</body>
</html>
