<?php
declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');
mb_internal_encoding('UTF-8');

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', (string) (30 * 86400));
session_start();

function pdo(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = require __DIR__ . '/../config/config.php';
        $naInstalacao = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'instalar.php';
        try {
            $pdo = new PDO(
                "mysql:host={$c['db_host']};port=" . ((int) ($c['db_port'] ?? 3306)) . ";dbname={$c['db_name']};charset=utf8mb4",
                $c['db_user'],
                $c['db_pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            if (!$naInstalacao && empty($_SESSION['db_ok'])) {
                if (!$pdo->query("SHOW TABLES LIKE 'usuarios'")->fetchColumn()) {
                    throw new PDOException('Tabelas não instaladas.');
                }
                $_SESSION['db_ok'] = true;
            }
        } catch (PDOException $e) {
            if ($naInstalacao) {
                throw $e;
            }
            if (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/')) {
                json_out(['ok' => false, 'erros' => ['Banco de dados indisponível.']], 500);
            }
            redirect('instalar.php');
        }
    }
    return $pdo;
}

/* ---------- helpers gerais ---------- */

function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function brl(float $v): string
{
    return 'R$ ' . number_format($v, 2, ',', '.');
}

function data_br(?string $d): string
{
    return $d ? date('d/m/Y', strtotime($d)) : '';
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function json_out(array $dados, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Converte "1.234,56", "R$ 12,5" ou "12.50" em float; null se inválido. */
function parse_valor(string $s): ?float
{
    $s = trim(str_replace(['R$', ' ', "\xC2\xA0"], '', $s));
    if ($s === '') {
        return null;
    }
    if (str_contains($s, ',')) {
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
    }
    if (!preg_match('/^\d+(\.\d{1,2})?$/', $s)) {
        return null;
    }
    return round((float) $s, 2);
}

function data_valida(string $d): bool
{
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt && $dt->format('Y-m-d') === $d;
}

function mes_valido(?string $m): ?string
{
    return ($m !== null && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $m)) ? $m : null;
}

function nome_mes(string $ym): string
{
    $nomes = ['', 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    [$a, $m] = explode('-', $ym);
    return $nomes[(int) $m] . ' de ' . $a;
}

/* ---------- flash ---------- */

function flash(string $tipo, string $msg): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'msg' => $msg];
}

function flash_pegar(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_ok(?string $t): bool
{
    return is_string($t) && $t !== '' && hash_equals($_SESSION['csrf'] ?? '', $t);
}

/* ---------- autenticação ---------- */

function usuario_id(): int
{
    return (int) ($_SESSION['uid'] ?? 0);
}

function logado(): bool
{
    return usuario_id() > 0;
}

function exigir_login(): void
{
    if (!logado()) {
        redirect('login.php');
    }
}

function exigir_visitante(): void
{
    if (logado()) {
        redirect('dashboard.php');
    }
}

function usuario_atual(): array
{
    static $u = null;
    if ($u === null) {
        $st = pdo()->prepare('SELECT id, nome, email FROM usuarios WHERE id = ?');
        $st->execute([usuario_id()]);
        $u = $st->fetch();
        if (!$u) {
            session_destroy();
            redirect('login.php');
        }
    }
    return $u;
}

function iniciar_sessao(int $uid, bool $lembrar): void
{
    session_regenerate_id(true);
    $_SESSION['uid'] = $uid;
    if ($lembrar) {
        $p = session_get_cookie_params();
        setcookie(session_name(), session_id(), [
            'expires' => time() + 30 * 86400,
            'path' => $p['path'],
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

/* ---------- API ---------- */

function api_iniciar(): int
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_out(['ok' => false, 'msg' => 'Método inválido.'], 405);
    }
    if (!logado()) {
        json_out(['ok' => false, 'msg' => 'Sessão expirada. Entre novamente.'], 401);
    }
    if (!csrf_ok($_POST['csrf'] ?? null)) {
        json_out(['ok' => false, 'msg' => 'Requisição inválida. Recarregue a página.'], 403);
    }
    return usuario_id();
}

function categorias(): array
{
    static $c = null;
    if ($c === null) {
        $c = pdo()->query('SELECT id, nome, icone, cor, tipo FROM categorias ORDER BY tipo DESC, nome')->fetchAll();
    }
    return $c;
}
