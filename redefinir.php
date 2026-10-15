<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/publico.php';
require __DIR__ . '/includes/email.php';
exigir_visitante();

garantir_tabela_recuperacoes();
$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$registro = null;
if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $st = pdo()->prepare('SELECT id, usuario_id FROM recuperacoes WHERE token_hash = ? AND usado_em IS NULL AND expira_em > NOW()');
    $st->execute([hash('sha256', $token)]);
    $registro = $st->fetch() ?: null;
}

$erros = [];
if ($registro && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova = (string) ($_POST['senha'] ?? '');
    $conf = (string) ($_POST['confirmar'] ?? '');
    if (!csrf_ok($_POST['csrf'] ?? null)) {
        $erros[] = 'Requisição inválida. Recarregue a página.';
    }
    if (strlen($nova) < 8 || !preg_match('/[A-Za-z]/', $nova) || !preg_match('/\d/', $nova)) {
        $erros[] = 'A senha deve ter pelo menos 8 caracteres, com letras e números.';
    }
    if ($nova !== $conf) {
        $erros[] = 'A confirmação não confere com a senha.';
    }
    if (!$erros) {
        $pdo = pdo();
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')->execute([password_hash($nova, PASSWORD_DEFAULT), $registro['usuario_id']]);
        $pdo->prepare('UPDATE recuperacoes SET usado_em = NOW() WHERE usuario_id = ? AND usado_em IS NULL')->execute([$registro['usuario_id']]);
        $pdo->commit();
        flash('success', 'Senha alterada. Já pode entrar com a nova.');
        redirect('login.php?senha=1');
    }
}
publico_topo('Nova senha', 'auth');
?>
<div class="auth-wrap">
  <aside class="sidebar auth-side d-none d-lg-flex">
    <a class="brand" href="index.php"><?= logo_svg(38) ?><span><strong>Meu Dinheiro</strong><small>Controle Financeiro para Estudantes</small></span></a>
  </aside>
  <main class="auth-main">
    <div class="auth-card">
      <?php if (!$registro): ?>
        <h1>Link inválido</h1>
        <p class="text-muted">Esse link já foi usado ou expirou. Peça um novo para continuar.</p>
        <a href="esqueci.php" class="btn btn-md-primary w-100">Pedir novo link</a>
      <?php else: ?>
        <h1>Nova senha</h1>
        <p class="text-muted">Escolha uma senha nova para a sua conta.</p>
        <?php if ($erros): ?><div class="alert alert-danger py-2 small"><ul class="mb-0 ps-3"><?php foreach ($erros as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="post" autocomplete="off">
          <?= csrf_campo() ?><input type="hidden" name="token" value="<?= e($token) ?>">
          <div class="input-icone mb-3"><i class="bi bi-lock"></i><input type="password" name="senha" class="form-control" placeholder="Nova senha" required autocomplete="new-password">
            <button type="button" class="ver-senha" aria-label="Mostrar senha"><i class="bi bi-eye"></i></button></div>
          <div class="input-icone mb-3"><i class="bi bi-lock-fill"></i><input type="password" name="confirmar" class="form-control" placeholder="Repita a nova senha" required autocomplete="new-password"></div>
          <button class="btn btn-md-primary w-100 py-2">Salvar senha</button>
        </form>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php publico_fim(); ?>
