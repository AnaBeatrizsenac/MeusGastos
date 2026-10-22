<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/publico.php';

publico_topo('Política de Privacidade', 'legal-page');
?>
<header class="land-nav">
  <div class="container d-flex align-items-center">
    <a class="brand" href="index.php"><?= logo_svg(36) ?><span><strong>Meu Dinheiro</strong><small>Controle Financeiro para Estudantes</small></span></a>
    <nav class="ms-auto d-flex align-items-center gap-3">
      <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Início</a>
      <a href="termos.php" class="btn btn-outline-secondary btn-sm d-none d-sm-inline-flex"><i class="bi bi-file-text me-1"></i>Termos de Uso</a>
      <?php if (logado()): ?>
        <a href="dashboard.php" class="btn btn-md-primary btn-sm rounded-pill px-3">Meu Painel</a>
      <?php else: ?>
        <a href="cadastro.php" class="btn btn-md-primary btn-sm rounded-pill px-3">Criar conta</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container py-4 py-md-5" style="max-width: 860px;">
  <div class="legal-card">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill small">
        <i class="bi bi-shield-lock me-1"></i> LGPD — Lei nº 13.709/2018
      </span>
      <span class="text-muted small">Atualizado em 22 de outubro de 2026</span>
    </div>

    <h1 class="mb-2">Política de Privacidade</h1>
    <p class="lead text-muted">A sua privacidade e a confidencialidade dos seus dados financeiros são compromissos inegociáveis no <strong>Meu Dinheiro</strong>.</p>

    <div class="legal-destaque">
      <div class="d-flex align-items-start gap-2">
        <i class="bi bi-check-circle-fill text-success fs-5 mt-1 flex-shrink-0"></i>
        <div>
          <strong class="d-block mb-1 text-success">Garantia de Não Compartilhamento</strong>
          <span>Nós <strong>NÃO vendemos, NÃO alugamos, NÃO transferimos e NÃO compartilhamos</strong> seus dados cadastrais ou registros financeiros com nenhum terceiro, instituição bancária ou birô de crédito.</span>
        </div>
      </div>
    </div>

    <h2><i class="bi bi-folder-check text-success"></i> 1. Dados que Coletamos</h2>
    <p>Para fornecer o serviço de gestão e organização financeira, coletamos exclusivamente os dados fornecidos diretamente por você:</p>
    <ul>
      <li><strong>Nome completo:</strong> utilizado para identificar sua conta e personalizar os relatórios.</li>
      <li><strong>E-mail:</strong> utilizado como identificador exclusivo de acesso (login), comunicações transacionais essenciais e recuperação de senha.</li>
      <li><strong>Senha com hash criptográfico:</strong> sua senha é protegida por criptografia de ponta a ponta em sentido único (hash seguro). Ninguém, nem mesmo os desenvolvedores, tem acesso à sua senha original.</li>
      <li><strong>Lançamentos de Entradas:</strong> descrição, valores, datas e categorias das suas receitas informadas.</li>
      <li><strong>Lançamentos de Gastos:</strong> descrição, valores, datas, categorias e observações das suas despesas.</li>
      <li><strong>Metas de Economia:</strong> valores estipulados, prazos, quantias guardadas e retiradas para acompanhamento de objetivos.</li>
    </ul>

    <h2><i class="bi bi-gear text-success"></i> 2. Finalidade do Tratamento</h2>
    <p>Os seus dados têm um único propósito legítimo: <strong>permitir que você mesmo organize, visualize e acompanhe suas finanças pessoais</strong>. Utilizamos as informações para:</p>
    <ul>
      <li>Autenticar o seu acesso seguro à sua conta;</li>
      <li>Calcular seus saldos mensais, porcentagens de gastos por categoria e limites de orçamento;</li>
      <li>Exibir gráficos, indicadores estatísticos e progresso de metas;</li>
      <li>Gerar arquivos de exportação sob demanda (como planilhas CSV e relatórios) para que você mantenha seus próprios backups.</li>
    </ul>

    <h2><i class="bi bi-cookie text-success"></i> 3. Cookies e Armazenamento Local</h2>
    <p>Adotamos uma abordagem estritamente funcional e não invasiva:</p>
    <ul>
      <li><strong>Cookies de Sessão:</strong> necessários para manter você conectado com segurança enquanto transita entre as telas do sistema.</li>
      <li><strong>Armazenamento Local (Tema Claro / Escuro):</strong> pequeno registro salvo no seu próprio navegador para memorizar sua preferência de visualização (Tema Escuro, Claro ou Automático).</li>
      <li><strong>Ausência de rastreadores:</strong> não utilizamos pixels de marketing, ferramentas de rastreamento entre sites ou redes de anúncios.</li>
    </ul>

    <h2><i class="bi bi-shield-shaded text-success"></i> 4. Segurança da Informação</h2>
    <p>Adotamos medidas técnicas e operacionais para resguardar a integridade das suas informações:</p>
    <ul>
      <li>Consultas ao banco de dados com parâmetros preparados (Prepared Statements) para prevenir injeções de SQL;</li>
      <li>Tokens de segurança anti-CSRF em todos os formulários;</li>
      <li>Proteção contra acessos forçados e restrição de acesso por sessão autenticada.</li>
    </ul>

    <h2><i class="bi bi-person-check text-success"></i> 5. Seus Direitos como Titular (LGPD, Art. 18)</h2>
    <p>Você é o único dono dos seus dados. A qualquer momento, você tem o direito de:</p>
    <ul>
      <li><strong>Acessar e consultar</strong> todos os lançamentos inseridos na plataforma;</li>
      <li><strong>Corrigir e editar</strong> qualquer informação incorreta de perfil ou transação;</li>
      <li><strong>Portabilidade e exportação:</strong> baixar uma cópia completa dos seus dados financeiros através da opção de exportação disponível no sistema;</li>
      <li><strong>Exclusão definitiva:</strong> solicitar a exclusão integral da sua conta e de todo o seu histórico financeiro de nossos registros.</li>
    </ul>

    <h2><i class="bi bi-envelope text-success"></i> 6. Fale Conosco</h2>
    <p class="mb-0">Para esclarecer dúvidas sobre esta Política de Privacidade ou solicitar a remoção dos seus dados, entre em contato através dos canais de suporte da plataforma.</p>
  </div>

  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 px-2">
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-house me-1"></i>Voltar ao Início</a>
    <a href="termos.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-text me-1"></i>Ler Termos de Uso</a>
  </div>
</main>

<footer class="text-center small text-muted py-4 border-top mt-5">
  Meu Dinheiro · projeto acadêmico de controle financeiro para estudantes · <a href="termos.php" class="text-muted text-decoration-underline">Termos de Uso</a> · <a href="privacidade.php" class="text-muted text-decoration-underline">Política de Privacidade</a>
</footer>
<?php publico_fim(); ?>
