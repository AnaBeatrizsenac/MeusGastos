<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/publico.php';
exigir_visitante();

$erro = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');
    $agora = time();
    $bloqueadoAte = $_SESSION['login_bloqueio'] ?? 0;

    if (!csrf_ok($_POST['csrf'] ?? null)) {
        $erro = 'Requisição inválida. Tente novamente.';
    } elseif ($bloqueadoAte > $agora) {
        $erro = 'Muitas tentativas. Aguarde ' . ($bloqueadoAte - $agora) . ' segundos.';
    } else {
        $st = pdo()->prepare('SELECT id, senha_hash FROM usuarios WHERE email = ?');
        $st->execute([$email]);
        $u = $st->fetch();
        if ($u && password_verify($senha, $u['senha_hash'])) {
            unset($_SESSION['login_falhas'], $_SESSION['login_bloqueio']);
            iniciar_sessao((int) $u['id'], !empty($_POST['lembrar']));
            redirect('dashboard.php');
        }
        $_SESSION['login_falhas'] = ($_SESSION['login_falhas'] ?? 0) + 1;
        if ($_SESSION['login_falhas'] >= 5) {
            $_SESSION['login_bloqueio'] = $agora + 60;
            $_SESSION['login_falhas'] = 0;
        }
        $erro = 'E-mail ou senha incorretos.';
    }
}
publico_topo('Entrar', 'auth');
?>
<div class="auth-wrap">
  <aside class="sidebar auth-side d-none d-lg-flex">
    <a class="brand" href="index.php"><?= logo_svg(38) ?><span><strong>Meu Dinheiro</strong><small>Controle Financeiro para Estudantes</small></span></a>
    <p class="side-frase mt-auto">Anotou, controlou.</p>
  </aside>
  <main class="auth-main">
    <div class="auth-card">
      <a class="brand d-lg-none mb-4" href="index.php"><?= logo_svg(36) ?><strong>Meu Dinheiro</strong></a>
      <h1>Entrar</h1>
      <p class="text-muted">Acesse sua conta para continuar</p>
      <?php if (isset($_GET['cadastrado'])): ?><div class="alert alert-success py-2">Conta criada. Agora é só entrar.</div><?php endif; ?>
      <?php if (isset($_GET['senha'])): ?><div class="alert alert-success py-2">Senha alterada. Entre com a nova.</div><?php endif; ?>
      <?php if ($erro): ?><div class="alert alert-danger py-2"><?= e($erro) ?></div><?php endif; ?>
      <form method="post" autocomplete="off" novalidate>
        <?= csrf_campo() ?>
        <div class="input-icone mb-3"><i class="bi bi-envelope"></i>
          <input type="email" name="email" class="form-control" placeholder="E-mail" value="<?= e($email) ?>" required autocomplete="off" autofocus></div>
        <div class="input-icone mb-3"><i class="bi bi-lock"></i>
          <input type="password" name="senha" class="form-control" placeholder="Senha" required autocomplete="off">
          <button type="button" class="ver-senha" aria-label="Mostrar senha"><i class="bi bi-eye"></i></button></div>
        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="lembrar" id="lembrar"><label class="form-check-label" for="lembrar">Lembrar de mim</label><a href="esqueci.php" class="float-end small">Esqueci a senha</a></div>
        <button class="btn btn-md-primary w-100 py-2">Entrar</button>
      </form>
      <p class="text-center small mt-4 mb-0">Não tem uma conta? <a href="cadastro.php" class="fw-bold">Cadastre-se</a></p>
    </div>
  </main>
</div>
<?php publico_fim(); ?>
