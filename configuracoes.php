<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
exigir_login();

$uid = usuario_id();
$u = usuario_atual();
$errosPerfil = $errosSenha = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf'] ?? null)) {
        redirect('configuracoes.php');
    }
    if (($_POST['acao'] ?? '') === 'perfil') {
        $nome = trim((string) ($_POST['nome'] ?? ''));
        if (mb_strlen($nome) < 2 || mb_strlen($nome) > 100) {
            $errosPerfil[] = 'Informe um nome de 2 a 100 caracteres.';
        } else {
            pdo()->prepare('UPDATE usuarios SET nome = ? WHERE id = ?')->execute([$nome, $uid]);
            flash('success', 'Perfil atualizado.');
            redirect('configuracoes.php');
        }
    } elseif (($_POST['acao'] ?? '') === 'senha') {
        $atual = (string) ($_POST['atual'] ?? '');
        $nova = (string) ($_POST['nova'] ?? '');
        $conf = (string) ($_POST['confirmar'] ?? '');
        $st = pdo()->prepare('SELECT senha_hash FROM usuarios WHERE id = ?');
        $st->execute([$uid]);
        if (!password_verify($atual, (string) $st->fetchColumn())) {
            $errosSenha[] = 'A senha atual está incorreta.';
        }
        if (strlen($nova) < 8 || !preg_match('/[A-Za-z]/', $nova) || !preg_match('/\d/', $nova)) {
            $errosSenha[] = 'A nova senha deve ter pelo menos 8 caracteres, com letras e números.';
        }
        if ($nova !== $conf) {
            $errosSenha[] = 'A confirmação de senha não confere.';
        }
        if (!$errosSenha) {
            pdo()->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')->execute([password_hash($nova, PASSWORD_DEFAULT), $uid]);
            session_regenerate_id(true);
            flash('success', 'Senha alterada com sucesso.');
            redirect('configuracoes.php');
        }
    }
}

layout_topo('Configurações', 'config');
?>
<h1 class="titulo mb-3">Configurações</h1>
<div class="row g-4">
  <div class="col-12">
    <div class="cartao">
      <h5 class="cartao-titulo">Aparência</h5>
      <div class="d-flex flex-wrap gap-3">
        <?php foreach (['auto' => 'Igual ao sistema', 'light' => 'Claro', 'dark' => 'Escuro'] as $val => $rot): ?>
          <div class="form-check"><input class="form-check-input" type="radio" name="tema_opcao" id="tema_<?= $val ?>" value="<?= $val ?>"><label class="form-check-label" for="tema_<?= $val ?>"><?= $rot ?></label></div>
        <?php endforeach; ?>
      </div>
      <p class="small text-muted mt-2 mb-0">Vale só para este navegador.</p>
    </div>
  </div>
  <div class="col-lg-6">
    <form method="post" class="cartao" autocomplete="off">
      <h5 class="cartao-titulo">Meu perfil</h5>
      <?php if ($errosPerfil): ?><div class="alert alert-danger py-2 small"><?= e(implode(' ', $errosPerfil)) ?></div><?php endif; ?>
      <?= csrf_campo() ?><input type="hidden" name="acao" value="perfil">
      <div class="mb-3"><label class="form-label">Nome</label><input type="text" name="nome" class="form-control" value="<?= e($u['nome']) ?>" maxlength="100" required autocomplete="off"></div>
      <div class="mb-3"><label class="form-label">E-mail</label><input type="email" class="form-control" value="<?= e($u['email']) ?>" disabled></div>
      <button class="btn btn-md-primary">Salvar perfil</button>
    </form>
  </div>
  <div class="col-lg-6">
    <form method="post" class="cartao" autocomplete="off">
      <h5 class="cartao-titulo">Alterar senha</h5>
      <?php if ($errosSenha): ?><div class="alert alert-danger py-2 small"><?= e(implode(' ', $errosSenha)) ?></div><?php endif; ?>
      <?= csrf_campo() ?><input type="hidden" name="acao" value="senha">
      <div class="mb-3"><label class="form-label">Senha atual</label><input type="password" name="atual" class="form-control" required autocomplete="off"></div>
      <div class="mb-3"><label class="form-label">Nova senha</label><input type="password" name="nova" class="form-control" required autocomplete="new-password"></div>
      <div class="mb-3"><label class="form-label">Confirmar nova senha</label><input type="password" name="confirmar" class="form-control" required autocomplete="new-password"></div>
      <button class="btn btn-md-primary">Alterar senha</button>
    </form>
  </div>
</div>
<p class="small text-muted mt-4">Meu Dinheiro · versão <?= APP_VERSION ?></p>
<?php layout_fim(); ?>
