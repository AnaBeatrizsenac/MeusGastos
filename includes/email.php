<?php
declare(strict_types=1);

/**
 * Envio de e-mail por SMTP, sem dependências.
 * Configuração em config/mail.local.php (modelo em config/mail.exemplo.php).
 */

function mail_config(): ?array
{
    $f = __DIR__ . '/../config/mail.local.php';
    if (!is_file($f)) {
        return null;
    }
    $c = require $f;
    return (is_array($c) && !empty($c['smtp_host'])) ? $c : null;
}

function mail_configurado(): bool
{
    return mail_config() !== null;
}

/** Lê uma resposta SMTP (pode ter várias linhas "250-..."). */
function smtp_ler($fp): array
{
    $texto = '';
    while (($linha = fgets($fp, 1024)) !== false) {
        $texto .= $linha;
        if (strlen($linha) < 4 || $linha[3] === ' ') {
            break;
        }
    }
    return [(int) substr($texto, 0, 3), $texto];
}

function smtp_cmd($fp, string $cmd, array $esperados): string
{
    fwrite($fp, $cmd . "\r\n");
    [$cod, $txt] = smtp_ler($fp);
    if (!in_array($cod, $esperados, true)) {
        throw new RuntimeException('SMTP recusou o comando: ' . trim($txt));
    }
    return $txt;
}

function limpar_cabecalho(string $s): string
{
    return trim(str_replace(["\r", "\n", "\0"], ' ', $s));
}

/** Retorna true se o servidor SMTP aceitou a mensagem. */
function enviar_email(string $para, string $assunto, string $texto): bool
{
    $c = mail_config();
    if (!$c || !filter_var($para, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $porta = (int) ($c['smtp_porta'] ?? 587);
    $seg = $c['smtp_seguranca'] ?? 'tls'; // tls (STARTTLS) | ssl | none
    $de = limpar_cabecalho($c['de_email'] ?? $c['smtp_usuario'] ?? '');
    $nome = limpar_cabecalho($c['de_nome'] ?? 'Meu Dinheiro');

    try {
        $fp = @stream_socket_client(($seg === 'ssl' ? 'ssl://' : 'tcp://') . $c['smtp_host'] . ':' . $porta, $en, $es, 12);
        if (!$fp) {
            throw new RuntimeException("Sem conexão com o SMTP ($es)");
        }
        stream_set_timeout($fp, 12);
        [$cod] = smtp_ler($fp);
        if ($cod !== 220) {
            throw new RuntimeException('SMTP não respondeu.');
        }
        $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
        smtp_cmd($fp, 'EHLO ' . $host, [250]);
        if ($seg === 'tls') {
            smtp_cmd($fp, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Falha ao iniciar TLS.');
            }
            smtp_cmd($fp, 'EHLO ' . $host, [250]);
        }
        if (!empty($c['smtp_usuario'])) {
            smtp_cmd($fp, 'AUTH LOGIN', [334]);
            smtp_cmd($fp, base64_encode($c['smtp_usuario']), [334]);
            smtp_cmd($fp, base64_encode($c['smtp_senha'] ?? ''), [235]);
        }
        smtp_cmd($fp, 'MAIL FROM:<' . $de . '>', [250]);
        smtp_cmd($fp, 'RCPT TO:<' . $para . '>', [250, 251]);
        smtp_cmd($fp, 'DATA', [354]);

        $cab = [
            'From: =?UTF-8?B?' . base64_encode($nome) . '?= <' . $de . '>',
            'To: <' . $para . '>',
            'Subject: =?UTF-8?B?' . base64_encode(limpar_cabecalho($assunto)) . '?=',
            'Date: ' . date('r'),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ];
        $corpo = chunk_split(base64_encode($texto), 76, "\r\n");
        smtp_cmd($fp, implode("\r\n", $cab) . "\r\n\r\n" . $corpo . '.', [250]);
        smtp_cmd($fp, 'QUIT', [221]);
        fclose($fp);
        return true;
    } catch (Throwable $e) {
        error_log('[MeuDinheiro] e-mail não enviado: ' . $e->getMessage());
        return false;
    }
}

/** URL base do sistema, para montar links nos e-mails. */
function url_base(): string
{
    $c = mail_config();
    if ($c && !empty($c['url_base'])) {
        return rtrim($c['url_base'], '/');
    }
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (!preg_match('/^[A-Za-z0-9.\-]+(:\d+)?$/', $host)) {
        $host = 'localhost';
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return ($https ? 'https' : 'http') . '://' . $host . $dir;
}

function garantir_tabela_recuperacoes(): void
{
    pdo()->exec(
        "CREATE TABLE IF NOT EXISTS recuperacoes (
           id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
           usuario_id  INT UNSIGNED NOT NULL,
           token_hash  CHAR(64) NOT NULL,
           expira_em   DATETIME NOT NULL,
           usado_em    DATETIME NULL,
           criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
           PRIMARY KEY (id),
           UNIQUE KEY uq_rec_token (token_hash),
           KEY idx_rec_usuario (usuario_id),
           CONSTRAINT fk_rec_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}
