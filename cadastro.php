<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/publico.php';
exigir_visitante();

$erros = [];
$nome = $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');
    $conf = (string) ($_POST['confirmar'] ?? '');

    if (!csrf_ok($_POST['csrf'] ?? null)) {
        $erros[] = 'Requisição inválida. Tente novamente.';
    }
    if (mb_strlen($nome) < 2 || mb_strlen($nome) > 100) {
        $erros[] = 'Informe seu nome (2 a 100 caracteres).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $erros[] = 'Informe um e-mail válido.';
    }
    if (strlen($senha) < 8) {
        $erros[] = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif (!preg_match('/[A-Za-z]/', $senha) || !preg_match('/\d/', $senha)) {
        $erros[] = 'A senha deve conter letras e números.';
    }
    if ($senha !== $conf) {
        $erros[] = 'A confirmação de senha não confere.';
    }
    if (!$erros) {
        $st = pdo()->prepare('SELECT COUNT(*) FROM usuarios WHERE email = ?');
        $st->execute([$email]);
        if ($st->fetchColumn()) {
            $erros[] = 'Este e-mail já está cadastrado.';
        }
    }
    if (!$erros) {
        pdo()->prepare('INSERT INTO usuarios (nome, email, senha_hash) VALUES (?,?,?)')
            ->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
        redirect('login.php?cadastrado=1');
    }
}
publico_topo('Criar conta', 'auth');
?>
<div class="auth-wrap">
  <aside class="sidebar auth-side d-none d-lg-flex">
    <a class="brand" href="index.php"><?= logo_svg(38) ?><span><strong>Meu Dinheiro</strong><small>Controle Financeiro para Estudantes</small></span></a>
    <p class="side-frase mt-auto">Um começo simples: anotar o primeiro gasto.</p>
  </aside>
  <main class="auth-main">
    <div class="auth-card">
      <a class="brand d-lg-none mb-4" href="index.php"><?= logo_svg(36) ?><strong>Meu Dinheiro</strong></a>
      <h1>Criar conta</h1>
      <p class="text-muted">Leva menos de um minuto</p>
      <?php if ($erros): ?><div class="alert alert-danger py-2 small"><ul class="mb-0 ps-3"><?php foreach ($erros as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
      <form method="post" autocomplete="off" novalidate>
        <?= csrf_campo() ?>
        <div class="input-icone mb-3"><i class="bi bi-person"></i><input type="text" name="nome" class="form-control" placeholder="Nome completo" value="<?= e($nome) ?>" maxlength="100" required autocomplete="off"></div>
        <div class="input-icone mb-3"><i class="bi bi-envelope"></i><input type="email" name="email" class="form-control" placeholder="E-mail" value="<?= e($email) ?>" maxlength="150" required autocomplete="off"></div>
        <div class="input-icone mb-3"><i class="bi bi-lock"></i><input type="password" name="senha" class="form-control" placeholder="Senha (mín. 8, com letras e números)" required autocomplete="new-password">
          <button type="button" class="ver-senha" aria-label="Mostrar senha"><i class="bi bi-eye"></i></button></div>
        <div class="input-icone mb-3"><i class="bi bi-lock-fill"></i><input type="password" name="confirmar" class="form-control" placeholder="Confirmar senha" required autocomplete="new-password"></div>
        <button class="btn btn-md-primary w-100 py-2">Criar conta</button>
      </form>
      <p class="text-center small mt-4 mb-0">Já tem conta? <a href="login.php" class="fw-bold">Entrar</a></p>
    </div>
  </main>
</div>
<?php publico_fim(); ?>
