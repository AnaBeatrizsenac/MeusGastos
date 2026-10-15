<?php
// Configuração do banco. Valores gerados pela tela de instalação ficam em config.local.php.
$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    return require $local;
}
return [
    'db_host' => getenv('MD_DB_HOST') ?: '127.0.0.1',
    'db_port' => (int) (getenv('MD_DB_PORT') ?: 3306),
    'db_name' => getenv('MD_DB_NAME') ?: 'meu_dinheiro',
    'db_user' => getenv('MD_DB_USER') ?: 'root',
    'db_pass' => getenv('MD_DB_PASS') !== false ? getenv('MD_DB_PASS') : '',
];
