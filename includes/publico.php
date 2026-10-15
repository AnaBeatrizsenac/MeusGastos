<?php
require_once __DIR__ . '/layout.php';

function publico_topo(string $titulo, string $classeBody = ''): void
{
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#17402b">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Meu Dinheiro">
<link rel="manifest" href="manifest.webmanifest">
<link rel="icon" type="image/svg+xml" href="assets/icone.svg">
<link rel="apple-touch-icon" href="assets/icone.svg">
<title><?= e($titulo) ?> — Meu Dinheiro</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Caveat:wght@600&display=swap">
<script>(function(){var t='auto';try{t=localStorage.getItem('md_tema')||'auto'}catch(e){}var d=t==='dark'||(t==='auto'&&window.matchMedia('(prefers-color-scheme: dark)').matches);document.documentElement.setAttribute('data-bs-theme',d?'dark':'light')})();</script>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/responsivo.css">
</head>
<body class="<?= e($classeBody) ?>">
<?php
}

function publico_fim(): void
{
    ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
<?php
}
