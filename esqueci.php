<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/publico.php';
require __DIR__ . '/includes/email.php';
exigir_visitante();

$erro = '';
$enviado = false;
$linkDev = null;
$emailDigitado = '';
const MAX_PEDIDOS_POR_HORA = 3;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailDigitado = strtolower(trim((string) ($_POST['email'] ?? '')));
    if (!csrf_ok($_POST['csrf'] ?? null)) {
        $erro = 'Requisição inválida. Recarregue a página.';
    } elseif (!filter_var($emailDigitado, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um e-mail válido.';
    } else {
        garantir_tabela_recuperacoes();
        $st = pdo()->prepare('SELECT id, nome FROM usuarios WHERE email = ?');
        $st->execute([$emailDigitado]);
        $u = $st->fetch();

        if ($u) {
            $st = pdo()->prepare('SELECT COUNT(*) FROM recuperacoes WHERE usuario_id = ? AND criado_em > (NOW() - INTERVAL 1 HOUR)');
            $st->execute([$u['id']]);
            if ((int) $st->fetchColumn() < MAX_PEDIDOS_POR_HORA) {
                $token = bin2hex(random_bytes(32));
                pdo()->prepare('INSERT INTO recuperacoes (usuario_id, token_hash, expira_em) VALUES (?, ?, NOW() + INTERVAL 1 HOUR)')
                    ->execute([$u['id'], hash('sha256', $token)]);
                $link = url_base() . '/redefinir.php?token=' . $token;
                $texto = "Olá, {$u['nome']}.\r\n\r\nRecebemos um pedido para redefinir a senha da sua conta no Meu Dinheiro.\r\n"
                    . "Use o link abaixo em até 1 hora:\r\n\r\n$link\r\n\r\n"
                    . "Se não foi você, é só ignorar este e-mail: sua senha continua a mesma.\r\n";
                $ok = enviar_email($emailDigitado, 'Redefinição de senha — Meu Dinheiro', $texto);
                // Sem SMTP configurado, só em máquina local mostramos o link na tela (para desenvolvimento).
                if (!$ok && !mail_configurado() && in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
                    $linkDev = $link;
                }
            }
        }
        // Mesma resposta exista o e-mail ou não, para não revelar quem tem conta.
        $enviado = true;
    }
}
publico_topo('Recuperar senha', 'auth');
?>
<div class="auth-wrap">
  <aside class="sidebar auth-side d-none d-lg-flex">
    <a class="brand" href="index.php"><?= logo_svg(38) ?><span><strong>Meu Dinheiro</strong><small>Controle Financeiro para Estudantes</small></span></a>
  </aside>
  <main class="auth-main">
    <div class="auth-card">
      <h1>Esqueceu a senha?</h1>
      <?php if ($enviado): ?>
        <div class="alert alert-success mt-3">Se esse e-mail tiver cadastro, você vai receber em instantes um link para criar uma senha nova. O link vale por 1 hora.</div>
        <?php if ($linkDev): ?>
          <div class="alert alert-warning small">
            <strong>Modo local:</strong> o envio de e-mail não está configurado (veja <code>config/mail.exemplo.php</code>). Como você está no próprio computador, o link é este:
            <div class="mt-2 text-break"><a href="<?= e($linkDev) ?>"><?= e($linkDev) ?></a></div>
          </div>
        <?php endif; ?>
        <a href="login.php" class="btn btn-md-outline w-100 mt-2">Voltar ao login</a>
      <?php else: ?>
        <p class="text-muted">Digite o e-mail da sua conta e enviaremos um link para você escolher outra.</p>
        <?php if ($erro): ?><div class="alert alert-danger py-2 small"><?= e($erro) ?></div><?php endif; ?>
        <form method="post" autocomplete="off">
          <?= csrf_campo() ?>
          <div class="input-icone mb-3"><i class="bi bi-envelope"></i>
            <input type="email" name="email" class="form-control" placeholder="E-mail" value="<?= e($emailDigitado) ?>" required autocomplete="off" autofocus></div>
          <button class="btn btn-md-primary w-100 py-2">Enviar link</button>
        </form>
        <p class="text-center small mt-4 mb-0"><a href="login.php">Lembrei, voltar ao login</a></p>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php publico_fim(); ?>
