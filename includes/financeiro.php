<?php
declare(strict_types=1);

/** Intervalo [inicio, fim) de um mês 'YYYY-MM'. */
function intervalo_mes(string $ym): array
{
    $ini = $ym . '-01';
    return [$ini, date('Y-m-d', strtotime($ini . ' +1 month'))];
}

function totais_mes(int $uid, string $ym): array
{
    [$ini, $fim] = intervalo_mes($ym);
    $st = pdo()->prepare(
        "SELECT
            COALESCE(SUM(CASE WHEN tipo='entrada' AND data >= :i1 AND data < :f1 THEN valor END),0) AS entradas,
            COALESCE(SUM(CASE WHEN tipo='gasto'   AND data >= :i2 AND data < :f2 THEN valor END),0) AS gastos,
            COALESCE(SUM(CASE WHEN tipo='entrada' AND data < :f3 THEN valor WHEN tipo='gasto' AND data < :f4 THEN -valor END),0) AS saldo,
            COALESCE(SUM(data >= :i3 AND data < :f5),0) AS qtd
         FROM movimentacoes WHERE usuario_id = :u"
    );
    $st->execute([
        'i1' => $ini, 'f1' => $fim, 'i2' => $ini, 'f2' => $fim, 'f3' => $fim,
        'f4' => $fim, 'i3' => $ini, 'f5' => $fim, 'u' => $uid,
    ]);
    $r = $st->fetch();
    return [
        'entradas' => (float) $r['entradas'],
        'gastos' => (float) $r['gastos'],
        'saldo' => (float) $r['saldo'],
        'qtd' => (int) $r['qtd'],
    ];
}

function gastos_por_categoria(int $uid, string $ym): array
{
    [$ini, $fim] = intervalo_mes($ym);
    return gastos_categoria_intervalo($uid, $ini, $fim);
}

/** Gastos agrupados por categoria em [ini, fim). */
function gastos_categoria_intervalo(int $uid, string $ini, string $fim): array
{
    $st = pdo()->prepare(
        "SELECT c.nome, c.icone, c.cor, SUM(m.valor) AS total
           FROM movimentacoes m JOIN categorias c ON c.id = m.categoria_id
          WHERE m.usuario_id = ? AND m.tipo = 'gasto' AND m.data >= ? AND m.data < ?
          GROUP BY c.id, c.nome, c.icone, c.cor
          ORDER BY total DESC"
    );
    $st->execute([$uid, $ini, $fim]);
    $linhas = $st->fetchAll();
    $soma = array_sum(array_map(fn($l) => (float) $l['total'], $linhas));
    foreach ($linhas as &$l) {
        $l['total'] = (float) $l['total'];
        $l['pct'] = $soma > 0 ? round($l['total'] / $soma * 100, 1) : 0;
    }
    return $linhas;
}

/** Entradas e gastos por mês, dos últimos $n meses (o atual incluso). */
function serie_mensal(int $uid, int $n): array
{
    $meses = [];
    for ($i = $n - 1; $i >= 0; $i--) {
        $meses[date('Y-m', strtotime(date('Y-m-01') . " -$i month"))] = ['entradas' => 0.0, 'gastos' => 0.0];
    }
    $ini = array_key_first($meses) . '-01';
    $st = pdo()->prepare(
        "SELECT DATE_FORMAT(data,'%Y-%m') ym, tipo, SUM(valor) total
           FROM movimentacoes WHERE usuario_id = ? AND data >= ?
          GROUP BY ym, tipo"
    );
    $st->execute([$uid, $ini]);
    foreach ($st->fetchAll() as $r) {
        if (isset($meses[$r['ym']])) {
            $meses[$r['ym']][$r['tipo'] === 'entrada' ? 'entradas' : 'gastos'] = (float) $r['total'];
        }
    }
    $saida = [];
    foreach ($meses as $ym => $v) {
        $saida[] = ['ym' => $ym] + $v;
    }
    return $saida;
}

function maiores_gastos(int $uid, string $ini, string $fim, int $n = 5): array
{
    $st = pdo()->prepare(
        "SELECT m.descricao, m.valor, m.data, c.nome AS categoria, c.icone
           FROM movimentacoes m JOIN categorias c ON c.id = m.categoria_id
          WHERE m.usuario_id = ? AND m.tipo = 'gasto' AND m.data >= ? AND m.data < ?
          ORDER BY m.valor DESC LIMIT " . (int) $n
    );
    $st->execute([$uid, $ini, $fim]);
    return $st->fetchAll();
}

/**
 * Monta o WHERE das listagens/exportações a partir de $g (normalmente $_GET).
 * Retorna [where, parametros, valores_normalizados_para_o_formulario].
 */
function filtros_movimentacao(int $uid, array $g, string $tipoFixo = ''): array
{
    $where = ['m.usuario_id = :u'];
    $par = ['u' => $uid];
    $tipo = $tipoFixo !== '' ? $tipoFixo : (in_array($g['tipo'] ?? '', ['entrada', 'gasto'], true) ? $g['tipo'] : '');
    $cat = (int) ($g['categoria'] ?? 0);
    $mes = mes_valido($g['mes'] ?? null);
    $q = trim((string) ($g['q'] ?? ''));
    $de = data_valida((string) ($g['de'] ?? '')) ? $g['de'] : '';
    $ate = data_valida((string) ($g['ate'] ?? '')) ? $g['ate'] : '';

    if ($tipo !== '') { $where[] = 'm.tipo = :t'; $par['t'] = $tipo; }
    if ($cat > 0) { $where[] = 'm.categoria_id = :c'; $par['c'] = $cat; }
    if ($mes) { [$i, $f] = intervalo_mes($mes); $where[] = 'm.data >= :i AND m.data < :f'; $par['i'] = $i; $par['f'] = $f; }
    if ($de !== '') { $where[] = 'm.data >= :de'; $par['de'] = $de; }
    if ($ate !== '') { $where[] = 'm.data <= :ate'; $par['ate'] = $ate; }
    if ($q !== '') {
        $where[] = '(m.descricao LIKE :q OR m.observacao LIKE :q2)';
        $par['q'] = $par['q2'] = '%' . addcslashes($q, '%_' . chr(92)) . '%';
    }
    return [implode(' AND ', $where), $par, ['tipo' => $tipo, 'categoria' => $cat, 'mes' => $mes, 'q' => $q]];
}

function ultimas_movimentacoes(int $uid, int $n = 5): array
{
    $st = pdo()->prepare(
        "SELECT m.*, c.nome AS categoria, c.icone, c.cor
           FROM movimentacoes m JOIN categorias c ON c.id = m.categoria_id
          WHERE m.usuario_id = ? ORDER BY m.data DESC, m.id DESC LIMIT " . (int) $n
    );
    $st->execute([$uid]);
    return $st->fetchAll();
}

function listar_metas(int $uid): array
{
    $st = pdo()->prepare('SELECT * FROM metas WHERE usuario_id = ? ORDER BY concluida_em IS NOT NULL, criado_em DESC');
    $st->execute([$uid]);
    $metas = $st->fetchAll();
    foreach ($metas as &$m) {
        $m['valor_alvo'] = (float) $m['valor_alvo'];
        $m['valor_atual'] = (float) $m['valor_atual'];
        $m['pct'] = $m['valor_alvo'] > 0 ? min(100, (int) floor($m['valor_atual'] / $m['valor_alvo'] * 100)) : 0;
        $m['falta'] = max(0.0, $m['valor_alvo'] - $m['valor_atual']);
    }
    return $metas;
}

function total_economizado(int $uid): float
{
    $st = pdo()->prepare('SELECT COALESCE(SUM(valor_atual),0) FROM metas WHERE usuario_id = ?');
    $st->execute([$uid]);
    return (float) $st->fetchColumn();
}

/** Alertas informativos, calculados por regras fixas. */
function gerar_alertas(int $uid, string $ym): array
{
    $t = totais_mes($uid, $ym);
    $alertas = [];

    if ($t['saldo'] < 0) {
        $alertas[] = ['nivel' => 'danger', 'msg' => 'Seu saldo está negativo (' . brl($t['saldo']) . '). Revise seus gastos.'];
    }
    if ($t['gastos'] > 0 && $t['gastos'] > $t['entradas']) {
        $alertas[] = ['nivel' => 'danger', 'msg' => 'Neste mês você gastou mais do que recebeu.'];
    } elseif ($t['entradas'] > 0 && $t['gastos'] >= $t['entradas'] * 0.8) {
        $alertas[] = ['nivel' => 'warning', 'msg' => 'Você já usou mais de 80% das suas entradas deste mês.'];
    }

    foreach (gastos_por_categoria($uid, $ym) as $c) {
        if ($c['pct'] > 40) {
            $alertas[] = ['nivel' => 'warning', 'msg' => 'Atenção! Seus gastos com ' . mb_strtolower($c['nome']) . ' representam ' . number_format($c['pct'], 0) . '% das despesas registradas no período.'];
            break;
        }
    }

    foreach (listar_metas($uid) as $m) {
        if ($m['concluida_em']) {
            continue;
        }
        if ($m['pct'] >= 80) {
            $alertas[] = ['nivel' => 'success', 'msg' => 'Falta pouco! A meta "' . $m['nome'] . '" está em ' . $m['pct'] . '%.'];
        }
        if ($m['prazo'] && $m['prazo'] < date('Y-m-d')) {
            $alertas[] = ['nivel' => 'info', 'msg' => 'O prazo da meta "' . $m['nome'] . '" já passou. Que tal ajustar a data?'];
        }
    }

    if ($t['qtd'] === 0) {
        $alertas[] = ['nivel' => 'info', 'msg' => 'Nenhuma movimentação neste mês. Registre suas entradas e gastos!'];
    }
    return $alertas;
}

function alertas_qtd_relevantes(array $alertas): int
{
    return count(array_filter($alertas, fn($a) => in_array($a['nivel'], ['danger', 'warning'], true)));
}

/** Nível, pontos e sequência de dias registrando movimentações. */
function conquistas(int $uid): array
{
    $pdo = pdo();
    $st = $pdo->prepare('SELECT COUNT(*) FROM movimentacoes WHERE usuario_id = ?');
    $st->execute([$uid]);
    $qtd = (int) $st->fetchColumn();

    $st = $pdo->prepare('SELECT COUNT(*) FROM metas WHERE usuario_id = ? AND concluida_em IS NOT NULL');
    $st->execute([$uid]);
    $concluidas = (int) $st->fetchColumn();

    $pontos = $qtd + 5 * $concluidas;
    $nivel = intdiv($pontos, 10) + 1;
    $nomes = [1 => 'Iniciante', 2 => 'Aprendiz', 3 => 'Poupador', 4 => 'Organizador', 5 => 'Planejador'];

    $st = $pdo->prepare('SELECT DISTINCT data FROM movimentacoes WHERE usuario_id = ? ORDER BY data DESC LIMIT 400');
    $st->execute([$uid]);
    $dias = $st->fetchAll(PDO::FETCH_COLUMN);
    $seq = 0;
    if ($dias) {
        $cursor = new DateTime($dias[0] >= date('Y-m-d') ? $dias[0] : date('Y-m-d'));
        if ($dias[0] < date('Y-m-d', strtotime('-1 day'))) {
            $cursor = null; // sequência quebrada
        }
        if ($cursor) {
            $set = array_flip($dias);
            if (!isset($set[$cursor->format('Y-m-d')])) {
                $cursor->modify('-1 day');
            }
            while (isset($set[$cursor->format('Y-m-d')])) {
                $seq++;
                $cursor->modify('-1 day');
            }
        }
    }

    return [
        'nivel' => $nivel,
        'titulo' => $nomes[$nivel] ?? 'Mestre das Finanças',
        'progresso' => ($pontos % 10) * 10,
        'concluidas' => $concluidas,
        'economizado' => total_economizado($uid),
        'sequencia' => $seq,
    ];
}

function dica_do_dia(): string
{
    $dicas = [
        'Antes de comprar algo, espere 24 horas. Se ainda fizer sentido depois disso, coloque a compra no seu planejamento.',
        'Anote todos os gastos, até os pequenos. São eles que mais passam despercebidos.',
        'Separe uma parte de toda entrada para suas metas assim que receber.',
        'Compare preços antes de comprar. Uma pesquisa rápida pode render uma boa economia.',
        'Revise suas assinaturas todo mês e cancele as que você não usa.',
        'Leve lanche de casa alguns dias por semana: o gasto com alimentação cai bastante.',
        'Defina um limite de lazer por mês e acompanhe pelo gráfico de categorias.',
        'Uma reserva de emergência evita que imprevistos virem dívidas.',
        'Metas pequenas e frequentes são mais fáceis de cumprir do que uma grande e distante.',
        'Antes de parcelar, pergunte-se: eu compraria isso à vista?',
    ];
    return $dicas[(int) date('z') % count($dicas)];
}
