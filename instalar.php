<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/publico.php';

$arquivoLocal = __DIR__ . '/config/config.local.php';
$arquivoLock = __DIR__ . '/config/instalado.lock';

/** Já instalado e funcionando? Então a tela fica bloqueada permanentemente. */
function instalacao_bloqueada(string $arquivoLock): bool
{
    if (file_exists($arquivoLock)) {
        return true;
    }
    try {
        if ((bool) pdo()->query("SHOW TABLES LIKE 'usuarios'")->fetchColumn()) {
            @file_put_contents($arquivoLock, date('c'));
            return true;
        }
    } catch (Throwable $e) {
        // Se o banco estiver temporariamente indisponível mas a instalação já foi feita
    }
    return false;
}

if (instalacao_bloqueada($arquivoLock)) {
    redirect('index.php');
}

$c = require __DIR__ . '/config/config.php';
$v = [
    'host' => $c['db_host'], 'porta' => (string) ($c['db_port'] ?? 3306),
    'usuario' => $c['db_user'], 'banco' => $c['db_name'],
];
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v['host'] = trim((string) ($_POST['host'] ?? ''));
    $v['porta'] = trim((string) ($_POST['porta'] ?? '3306'));
    $v['usuario'] = trim((string) ($_POST['usuario'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');
    $v['banco'] = trim((string) ($_POST['banco'] ?? ''));

    if (!csrf_ok($_POST['csrf'] ?? null)) {
        $erro = 'Requisição inválida. Recarregue a página.';
    } elseif ($v['host'] === '' || $v['usuario'] === '') {
        $erro = 'Informe o servidor e o usuário.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{1,64}$/', $v['banco'])) {
        $erro = 'O nome do banco deve ter só letras, números e _ (até 64 caracteres).';
    } elseif (!ctype_digit($v['porta']) || (int) $v['porta'] < 1 || (int) $v['porta'] > 65535) {
        $erro = 'Porta inválida.';
    } else {
        try {
            $opt = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false];
            $srv = new PDO("mysql:host={$v['host']};port={$v['porta']};charset=utf8mb4", $v['usuario'], $senha, $opt);
            $srv->exec("CREATE DATABASE IF NOT EXISTS `{$v['banco']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $srv->exec("USE `{$v['banco']}`");

            $sql = file_get_contents(__DIR__ . '/sql/schema.sql');
            $sql = preg_replace('/^\s*--.*$/m', '', $sql);
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $cmd) {
                if (preg_match('/^(CREATE DATABASE|USE)\b/i', $cmd)) {
                    continue; // o banco escolhido nesta tela já foi criado e selecionado
                }
                $srv->exec($cmd);
            }

            $conteudo = "<?php\n// Gerado pela tela de instalação. Não versionar.\nreturn " . var_export([
                'db_host' => $v['host'], 'db_port' => (int) $v['porta'],
                'db_name' => $v['banco'], 'db_user' => $v['usuario'], 'db_pass' => $senha,
            ], true) . ";\n";
            if (file_put_contents($arquivoLocal, $conteudo, LOCK_EX) === false) {
                throw new RuntimeException('Não foi possível gravar config/config.local.php. Verifique as permissões da pasta config.');
            }
            @file_put_contents($arquivoLock, date('c'));
            $_SESSION['db_ok'] = true;
            flash('success', 'Instalação concluída! Crie sua conta para começar.');
            redirect('cadastro.php');
        } catch (PDOException $e) {
            $erro = 'Falha ao conectar/instalar: ' . $e->getMessage();
        } catch (RuntimeException $e) {
            $erro = $e->getMessage();
        }
    }
}

publico_topo('Instalação', 'auth');
?>
<div class="auth-wrap">
  <main class="auth-main" style="margin-left:0">
    <div class="auth-card" style="max-width:520px">
      <a class="brand mb-3" href="index.php"><?= logo_svg(36) ?><strong>Meu Dinheiro</strong></a>
      <h1>Configurar banco de dados</h1>
      <p class="text-muted">Informe o acesso ao MySQL. O banco e as tabelas serão criados automaticamente.</p>
      <?php if ($erro): ?><div class="alert alert-danger py-2 small"><?= e($erro) ?></div><?php endif; ?>
      <form method="post" autocomplete="off">
        <?= csrf_campo() ?>
        <div class="row g-3 mb-3">
          <div class="col-8"><label class="form-label">Servidor</label><input name="host" class="form-control" value="<?= e($v['host']) ?>" required autocomplete="off"></div>
          <div class="col-4"><label class="form-label">Porta</label><input name="porta" class="form-control" value="<?= e($v['porta']) ?>" required autocomplete="off"></div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6"><label class="form-label">Usuário</label><input name="usuario" class="form-control" value="<?= e($v['usuario']) ?>" required autocomplete="off"></div>
          <div class="col-6"><label class="form-label">Senha</label><input type="password" name="senha" class="form-control" autocomplete="new-password"></div>
        </div>
        <div class="mb-4"><label class="form-label">Nome do banco</label><input name="banco" class="form-control" value="<?= e($v['banco']) ?>" required autocomplete="off"></div>
        <button class="btn btn-md-primary w-100 py-2">Testar e instalar</button>
      </form>
    </div>
  </main>
</div>
<?php publico_fim(); ?>
