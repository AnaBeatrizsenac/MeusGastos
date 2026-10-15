# Meu Dinheiro — Documentação

Controle financeiro pessoal para estudantes. Projeto de Ana Beatriz e Ana Júlia.

## 1. Visão geral

O sistema permite que cada estudante registre o que recebe e o que gasta, acompanhe o saldo, veja para onde o dinheiro vai e guarde valores para metas. Cada usuário enxerga apenas os próprios dados.

**Tecnologias:** HTML5, CSS3, Bootstrap 5, JavaScript, PHP 8 (PDO) e MySQL. Gráficos com Chart.js.

## 2. Instalação

Requisitos: XAMPP (ou qualquer Apache + PHP 8.0+ + MySQL/MariaDB) com as extensões `pdo_mysql` e `mbstring`.

1. Coloque a pasta do projeto em `htdocs` (ex.: `C:\xampp\htdocs\MeusGastos`).
2. Inicie Apache e MySQL no painel do XAMPP.
3. Abra `http://localhost/MeusGastos/`. No primeiro acesso o sistema abre a **tela de instalação**.
4. Informe servidor (`127.0.0.1`), porta (`3306`), usuário, senha e o nome do banco. Clique em **Testar e instalar**.
5. Crie sua conta e comece a usar.

A instalação cria o banco, as tabelas e as categorias iniciais, e grava os dados de acesso em `config/config.local.php` (fora do Git, inacessível pelo navegador). Para reinstalar, apague esse arquivo e o banco.

Instalação manual alternativa: importe `sql/schema.sql` no phpMyAdmin e crie `config/config.local.php` copiando o formato de `config/config.php`.

### E-mail (recuperação de senha)

Copie `config/mail.exemplo.php` para `config/mail.local.php` e preencha com os dados SMTP do provedor. Exemplo com Gmail: `smtp.gmail.com`, porta `587`, segurança `tls`, usuário = e-mail, senha = "senha de app" (exige verificação em duas etapas). Preencha também `url_base` com o endereço público do sistema.

Sem SMTP configurado, quem estiver acessando pelo próprio computador vê o link de redefinição na tela (modo desenvolvimento). Em servidor, o link **não** é mostrado.

## 3. Como usar

| Tela | Para que serve |
|---|---|
| **Início** | Resumo do mês: saldo, entradas, gastos, valor guardado nas metas, gráfico por categoria, últimas movimentações, alertas, dica do dia e conquistas. Use as setas para trocar o mês. |
| **Entradas** | Registrar dinheiro recebido (mesada, salário, bolsa, estágio, freelancer, outros) e ver a lista. |
| **Gastos** | Registrar despesas por categoria e ver a lista com filtros. |
| **Metas** | Criar objetivos, **guardar** ou **retirar** valores e acompanhar a porcentagem. Ao atingir 100%, a meta vai para "concluídas". |
| **Histórico** | Todas as movimentações, entradas e gastos juntos, com filtros por tipo, categoria, mês e busca. |
| **Relatórios** | Evolução mês a mês, gastos por categoria, maiores gastos, taxa de poupança. Escolha 3, 6 ou 12 meses. |
| **Configurações** | Tema (claro/escuro/automático), nome e troca de senha. |

### Lançar uma movimentação
Use o botão **Adicionar entrada / despesa** no Início, o formulário da tela Entradas ou **Novo gasto** na tela Gastos. Campos: descrição, valor (aceita `12,50` ou `1.250,00`), data (não pode ser futura), categoria e observação opcional.

### Editar, ver e excluir
Nas listas, os ícones à direita abrem os detalhes, a edição ou a exclusão (sempre com confirmação).

### Exportar
Em qualquer lista, o botão **Exportar** gera:
- **CSV**: abre direto no Excel (separador `;`, acentos corretos).
- **PDF**: abre uma página de extrato; na janela de impressão escolha **Salvar como PDF**.

A exportação respeita os filtros aplicados. Em Relatórios, exporta o período escolhido.

### Esqueci a senha
Na tela de login, **Esqueci a senha** → informe o e-mail → abra o link recebido (vale 1 hora e só funciona uma vez) → escolha a nova senha.

## 4. Regras de negócio

- **Saldo** = todas as entradas − todos os gastos até o fim do mês selecionado. Entradas, gastos e gráfico mostram só aquele mês.
- **Guardado nas metas** = soma do valor atual de todas as metas. Guardar em uma meta não altera o saldo (é um controle à parte).
- **Alertas** (informativos):
  - saldo negativo;
  - gastos maiores que as entradas do mês;
  - gastos a partir de 80% das entradas;
  - uma categoria acima de 40% dos gastos do mês;
  - meta a partir de 80%;
  - prazo de meta vencido;
  - mês sem nenhum lançamento.
- **Nível:** 1 ponto por movimentação + 5 por meta concluída; a cada 10 pontos sobe um nível (Iniciante, Aprendiz, Poupador, Organizador, Planejador, Mestre das Finanças).
- **Dias seguidos:** quantos dias consecutivos, até hoje ou ontem, têm ao menos um lançamento.
- **Senha:** mínimo de 8 caracteres, com letras e números.
- **Login:** após 5 tentativas erradas, bloqueio de 60 segundos.

## 5. Perguntas frequentes

**Apareceu a tela de instalação de novo.** O sistema não conseguiu conectar ao banco ou as tabelas não existem. Confira se o MySQL está ligado e refaça a instalação.

**"Access denied for user root".** A senha informada não é a do MySQL. No XAMPP padrão a senha do `root` é vazia, mas pode ter sido alterada.

**O e-mail de recuperação não chega.** Verifique `config/mail.local.php`, a caixa de spam e o log de erros do PHP (`error_log`), onde o motivo da falha é registrado.

**O gráfico não aparece.** Chart.js e Bootstrap são carregados de uma CDN; é preciso estar conectado à internet.

**Perdi o acesso ao e-mail cadastrado.** Não há recuperação alternativa; é preciso alterar o e-mail diretamente no banco.
