# Changelog

Formato baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/). A lista exibida no sistema (selo de versão no menu) vem de `includes/versao.php`; mantenha os dois em sincronia.

## [1.1.1] — 2026-09-30

### Alterado
- Responsividade: em telas até 575 px as listas de movimentações viram cartões (valor e ações sempre visíveis, sem rolagem horizontal); filtros empilhados; formulários de movimentação e de meta abrem em tela cheia; alvos de toque de 40 px; fonte de 16 px nos campos (evita zoom automático no iOS); botões rápidos do painel numa linha só.
- Tablet (576–991 px): quatro indicadores por linha, gráfico e metas lado a lado, menu inferior com ícone e texto lado a lado.
- Áreas seguras (notch) respeitadas na barra superior e inferior.

### Adicionado
- `manifest.webmanifest` e ícone: o sistema pode ser instalado na tela inicial do celular. Sem modo offline.

## [1.1.0] — 2026-09-30

### Adicionado
- **Relatórios** (`relatorios.php`): evolução mês a mês (3, 6 ou 12 meses), total recebido/gasto, sobra e taxa de poupança, média mensal de gastos, variação do mês atual contra o anterior, gastos por categoria e os cinco maiores gastos.
- **Exportação** do histórico em **CSV** (Excel BR: `;` como separador, UTF-8 com BOM) e em **PDF** (página de extrato pronta para "Salvar como PDF" do navegador). Disponível em Entradas, Gastos, Histórico e Relatórios; respeita os filtros ativos. Limite de 5.000 linhas.
- **Recuperação de senha por e-mail** (`esqueci.php`, `redefinir.php`): token aleatório de 256 bits guardado só como hash SHA-256, validade de 1 hora, uso único, limite de 3 pedidos por hora por usuário, resposta idêntica exista o e-mail ou não. Envio por SMTP próprio (`includes/email.php`), configurado em `config/mail.local.php`. Em ambiente local sem SMTP, o link aparece na tela.
- **Tema** claro, escuro ou automático (segue o sistema), com botão na barra superior e opção em Configurações. Preferência guardada no navegador.
- **Tela de instalação** (`instalar.php`): pede os dados do MySQL no primeiro acesso, cria o banco e as tabelas e grava `config/config.local.php`. Bloqueia-se sozinha depois de instalado.
- **Selo de versão** com histórico de mudanças no menu lateral e em Configurações.
- Tabela `recuperacoes` no banco.

### Alterado
- Visual refeito: paleta mais sóbria, fontes DM Sans + Fraunces, cartões planos, menu lateral em cor sólida, textos revisados.
- Menu ganhou "Relatórios".
- Página inicial: seções "Sobre" e "Funcionalidades" reescritas.
- Mensagens do sistema mais diretas (sem exclamações e emojis de comemoração).

### Corrigido
- Erro de conexão com o banco agora leva à tela de instalação em vez de uma mensagem seca.

## [1.0.0] — 2026-09-30

### Adicionado
- Cadastro e login (senha com `password_hash`, limite de tentativas, "lembrar de mim" por 30 dias).
- Entradas e gastos com categoria, data e observação; edição, exclusão com confirmação e visualização em janela.
- Histórico com filtros (tipo, categoria, mês, busca) e paginação.
- Painel: saldo, entradas, gastos, guardado nas metas, gráfico de rosca por categoria, últimas movimentações, alertas por regras, dica do dia, conquistas e níveis.
- Metas de economia: criar, editar, guardar e retirar valores, conclusão automática.
- Configurações: alterar nome e senha.
- Layout responsivo (menu lateral no computador, barra inferior no celular).
- Segurança: consultas PDO preparadas, filtro por `usuario_id`, token CSRF, escape de saída, validação no servidor.
