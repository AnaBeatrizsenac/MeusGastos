<?php
require __DIR__ . '/../includes/bootstrap.php';
$uid = api_iniciar();
$pdo = pdo();
$acao = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

function meta_do_usuario(PDO $pdo, int $id, int $uid): array
{
    $st = $pdo->prepare('SELECT * FROM metas WHERE id = ? AND usuario_id = ?');
    $st->execute([$id, $uid]);
    $m = $st->fetch();
    if (!$m) {
        json_out(['ok' => false, 'erros' => ['Meta não encontrada.']], 404);
    }
    return $m;
}

if ($acao === 'salvar') {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $icone = trim((string) ($_POST['icone'] ?? '🎯'));
    $alvo = parse_valor((string) ($_POST['valor_alvo'] ?? ''));
    $prazo = trim((string) ($_POST['prazo'] ?? ''));
    $erros = [];

    if (mb_strlen($nome) < 2 || mb_strlen($nome) > 100) {
        $erros[] = 'Informe um nome de 2 a 100 caracteres.';
    }
    if ($alvo === null || $alvo <= 0 || $alvo > 9999999.99) {
        $erros[] = 'Informe um valor de meta válido maior que zero.';
    }
    if ($prazo !== '' && !data_valida($prazo)) {
        $erros[] = 'Prazo inválido.';
    }
    if ($icone === '' || mb_strlen($icone) > 8) {
        $icone = '🎯';
    }
    if ($erros) {
        json_out(['ok' => false, 'erros' => $erros], 422);
    }

    if ($id > 0) {
        $m = meta_do_usuario($pdo, $id, $uid);
        $conclui = (float) $m['valor_atual'] >= $alvo ? ($m['concluida_em'] ?: date('Y-m-d H:i:s')) : null;
        $pdo->prepare('UPDATE metas SET nome=?, icone=?, valor_alvo=?, prazo=?, concluida_em=? WHERE id=? AND usuario_id=?')
            ->execute([$nome, $icone, $alvo, $prazo ?: null, $conclui, $id, $uid]);
        flash('success', 'Meta atualizada.');
    } else {
        $pdo->prepare('INSERT INTO metas (usuario_id, nome, icone, valor_alvo, prazo) VALUES (?,?,?,?,?)')
            ->execute([$uid, $nome, $icone, $alvo, $prazo ?: null]);
        flash('success', 'Meta criada.');
    }
    json_out(['ok' => true]);
}

if ($acao === 'guardar' || $acao === 'retirar') {
    $m = meta_do_usuario($pdo, $id, $uid);
    $valor = parse_valor((string) ($_POST['valor'] ?? ''));
    if ($valor === null || $valor <= 0 || $valor > 9999999.99) {
        json_out(['ok' => false, 'erros' => ['Informe um valor válido maior que zero.']], 422);
    }
    $novo = (float) $m['valor_atual'] + ($acao === 'guardar' ? $valor : -$valor);
    if ($novo < 0) {
        json_out(['ok' => false, 'erros' => ['Você não pode retirar mais do que já guardou (' . brl((float) $m['valor_atual']) . ').']], 422);
    }
    $conclui = $novo >= (float) $m['valor_alvo'] ? ($m['concluida_em'] ?: date('Y-m-d H:i:s')) : null;
    $pdo->prepare('UPDATE metas SET valor_atual=?, concluida_em=? WHERE id=? AND usuario_id=?')
        ->execute([$novo, $conclui, $id, $uid]);
    if ($conclui && !$m['concluida_em']) {
        flash('success', 'Meta "' . $m['nome'] . '" concluída. Boa!');
    } else {
        flash('success', $acao === 'guardar' ? 'Valor guardado na meta.' : 'Valor retirado da meta.');
    }
    json_out(['ok' => true]);
}

if ($acao === 'excluir') {
    meta_do_usuario($pdo, $id, $uid);
    $pdo->prepare('DELETE FROM metas WHERE id = ? AND usuario_id = ?')->execute([$id, $uid]);
    flash('success', 'Meta excluída.');
    json_out(['ok' => true]);
}

json_out(['ok' => false, 'erros' => ['Ação inválida.']], 400);
