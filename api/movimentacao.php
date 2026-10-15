<?php
require __DIR__ . '/../includes/bootstrap.php';
$uid = api_iniciar();
$pdo = pdo();
$acao = $_POST['action'] ?? '';

if ($acao === 'salvar') {
    $id = (int) ($_POST['id'] ?? 0);
    $tipo = $_POST['tipo'] ?? '';
    $desc = trim((string) ($_POST['descricao'] ?? ''));
    $valor = parse_valor((string) ($_POST['valor'] ?? ''));
    $data = (string) ($_POST['data'] ?? '');
    $catId = (int) ($_POST['categoria_id'] ?? 0);
    $obs = trim((string) ($_POST['observacao'] ?? ''));
    $erros = [];

    if (!in_array($tipo, ['entrada', 'gasto'], true)) {
        $erros[] = 'Tipo de movimentação inválido.';
    }
    if (mb_strlen($desc) < 2 || mb_strlen($desc) > 120) {
        $erros[] = 'Informe uma descrição de 2 a 120 caracteres.';
    }
    if ($valor === null || $valor <= 0 || $valor > 9999999.99) {
        $erros[] = 'Informe um valor válido maior que zero.';
    }
    if (!data_valida($data)) {
        $erros[] = 'Informe uma data válida.';
    } elseif ($data > date('Y-m-d')) {
        $erros[] = 'A data não pode ser futura.';
    }
    if (mb_strlen($obs) > 255) {
        $erros[] = 'A observação pode ter no máximo 255 caracteres.';
    }
    if (!$erros) {
        $st = $pdo->prepare('SELECT COUNT(*) FROM categorias WHERE id = ? AND tipo = ?');
        $st->execute([$catId, $tipo]);
        if (!$st->fetchColumn()) {
            $erros[] = 'Selecione uma categoria válida.';
        }
    }
    if ($erros) {
        json_out(['ok' => false, 'erros' => $erros], 422);
    }

    if ($id > 0) {
        $st = $pdo->prepare('SELECT COUNT(*) FROM movimentacoes WHERE id = ? AND usuario_id = ?');
        $st->execute([$id, $uid]);
        if (!$st->fetchColumn()) {
            json_out(['ok' => false, 'erros' => ['Registro não encontrado.']], 404);
        }
        $pdo->prepare('UPDATE movimentacoes SET tipo=?, categoria_id=?, descricao=?, valor=?, data=?, observacao=? WHERE id=? AND usuario_id=?')
            ->execute([$tipo, $catId, $desc, $valor, $data, $obs ?: null, $id, $uid]);
        flash('success', 'Movimentação atualizada com sucesso.');
    } else {
        $pdo->prepare('INSERT INTO movimentacoes (usuario_id, tipo, categoria_id, descricao, valor, data, observacao) VALUES (?,?,?,?,?,?,?)')
            ->execute([$uid, $tipo, $catId, $desc, $valor, $data, $obs ?: null]);
        flash('success', ($tipo === 'entrada' ? 'Entrada' : 'Gasto') . ' registrado com sucesso.');
    }
    json_out(['ok' => true]);
}

if ($acao === 'excluir') {
    $id = (int) ($_POST['id'] ?? 0);
    $st = $pdo->prepare('DELETE FROM movimentacoes WHERE id = ? AND usuario_id = ?');
    $st->execute([$id, $uid]);
    if (!$st->rowCount()) {
        json_out(['ok' => false, 'erros' => ['Registro não encontrado.']], 404);
    }
    flash('success', 'Movimentação excluída.');
    json_out(['ok' => true]);
}

json_out(['ok' => false, 'erros' => ['Ação inválida.']], 400);
