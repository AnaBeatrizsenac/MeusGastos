<?php
// Copie este arquivo para config/mail.local.php e preencha com os dados do seu provedor de e-mail.
// Gmail: smtp.gmail.com, porta 587, segurança 'tls', usuário = seu e-mail, senha = "senha de app".
return [
    'smtp_host'      => 'smtp.exemplo.com',
    'smtp_porta'     => 587,
    'smtp_seguranca' => 'tls',            // 'tls' (porta 587), 'ssl' (porta 465) ou 'none'
    'smtp_usuario'   => 'usuario@exemplo.com',
    'smtp_senha'     => 'senha-ou-senha-de-app',
    'de_email'       => 'usuario@exemplo.com',
    'de_nome'        => 'Meu Dinheiro',
    // Endereço público do sistema (evita links montados a partir do cabeçalho Host):
    'url_base'       => 'https://seu-dominio.com.br/meudinheiro',
];
