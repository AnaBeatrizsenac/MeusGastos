<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/publico.php';

publico_topo('Termos de Uso', 'legal-page');
?>
<header class="land-nav">
  <div class="container d-flex align-items-center">
    <a class="brand" href="index.php"><?= logo_svg(36) ?><span><strong>Meu Dinheiro</strong><small>Controle Financeiro para Estudantes</small></span></a>
    <nav class="ms-auto d-flex align-items-center gap-3">
      <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Início</a>
      <a href="privacidade.php" class="btn btn-outline-secondary btn-sm d-none d-sm-inline-flex"><i class="bi bi-shield-lock me-1"></i>Privacidade</a>
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
      <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill small">
        <i class="bi bi-file-earmark-ruled me-1"></i> Termos e Condições de Uso
      </span>
      <span class="text-muted small">Atualizado em 22 de outubro de 2026</span>
    </div>

    <h1 class="mb-2">Termos de Uso</h1>
    <p class="lead text-muted">Condições gerais para utilização da aplicação web <strong>Meu Dinheiro</strong>.</p>

    <!-- CLÁUSULA CENTRAL DE ISENÇÃO DE RESPONSABILIDADE -->
    <div class="legal-alerta my-4">
      <div class="d-flex align-items-start gap-3">
        <i class="bi bi-exclamation-triangle-fill text-warning-emphasis fs-4 mt-1 flex-shrink-0"></i>
        <div>
          <strong class="d-block mb-1 text-warning-emphasis">Cláusula de Isenção de Responsabilidade (Disclaimer Financeiro)</strong>
          <p class="mb-2">
            O <strong>Meu Dinheiro</strong> é exclusivamente uma <strong>ferramenta tecnológica de organização pessoal, anotação e cálculo matemático</strong> de despesas, receitas e metas informadas diretamente pelo usuário.
          </p>
          <ul class="mb-0 ps-3 small">
            <li><strong>Não é consultoria financeira:</strong> a plataforma <strong>NÃO</strong> fornece aconselhamento financeiro, contábil, tributário ou assessoria em investimentos de qualquer natureza.</li>
            <li><strong>Autonomia do usuário:</strong> toda e qualquer decisão orçamentária, aquisição de bens, contratação de empréstimos, cortes de gastos ou investimentos é de <strong>responsabilidade única, exclusiva e discricionária do usuário</strong>.</li>
            <li><strong>Sem garantias de ganhos:</strong> cálculos e indicadores refletem unicamente os valores lançados. A plataforma não se responsabiliza por desvios de saldo decorrentes de lançamentos incompletos ou decisões financeiras tomadas pelo usuário.</li>
          </ul>
        </div>
      </div>
    </div>

    <h2><i class="bi bi-check2-circle text-success"></i> 1. Aceitação dos Termos</h2>
    <p>Ao se cadastrar ou navegar na plataforma, você declara ter lido, compreendido e concordado integralmente com estes Termos de Uso e com a nossa <a href="privacidade.php" class="text-decoration-underline">Política de Privacidade</a>.</p>

    <h2><i class="bi bi-laptop text-success"></i> 2. Natureza e Finalidade do Serviço</h2>
    <p>O <strong>Meu Dinheiro</strong> foi concebido como um projeto focado em apoiar estudantes e pessoas físicas no aprendizado e disciplina de registrar suas movimentações financeiras do dia a dia, eliminando a dependência de planilhas complexas.</p>

    <h2><i class="bi bi-person-badge text-success"></i> 3. Cadastro e Obrigações do Usuário</h2>
    <p>Para usufruir dos recursos da plataforma, o usuário se compromete a:</p>
    <ul>
      <li>Informar endereço de e-mail autêntico e de sua titularidade;</li>
      <li>Manter sua senha de acesso em sigilo e sob sua guarda, sendo vedado o compartilhamento com terceiros;</li>
      <li>Utilizar o sistema de forma ética, não realizando tentativas de invasão, sobrecarga de requisições ou violações da integridade do banco de dados;</li>
      <li>Verificar a exatidão dos valores de despesas e receitas lançados antes de tomar conclusões sobre o próprio orçamento.</li>
    </ul>

    <h2><i class="bi bi-clock-history text-success"></i> 4. Disponibilidade do Sistema e Backups</h2>
    <p>Empregamos os melhores esforços para manter a plataforma estável e operacional. Entretanto, o serviço é disponibilizado no formato "como está" (as-is), podendo passar por atualizações ou paradas técnicas momentâneas. É recomendado que o usuário utilize periodicamente o recurso de <strong>Exportar Dados</strong> para guardar cópias offline de seus registros.</p>

    <h2><i class="bi bi-shield-x text-success"></i> 5. Suspensão e Encerramento</h2>
    <p>O usuário pode deixar de utilizar o sistema e requerer a exclusão de seus dados quando desejar. O Meu Dinheiro reserva-se o direito de suspender ou rescindir o acesso de qualquer usuário que descumpra as regras de segurança ou pratique atos ilícitos contra a plataforma.</p>

    <h2><i class="bi bi-journal-text text-success"></i> 6. Alterações nos Termos</h2>
    <p>Estes termos poderão ser modificados pontualmente para incorporar novas funcionalidades ou atender requisitos legais. A continuidade de uso da ferramenta após as atualizações representa o consentimento com os novos termos.</p>

    <h2><i class="bi bi-geo-alt text-success"></i> 7. Legislação Aplicável</h2>
    <p class="mb-0">Estes Termos de Uso são regidos e interpretados em conformidade com as leis vigentes na República Federativa do Brasil.</p>
  </div>

  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 px-2">
    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-house me-1"></i>Voltar ao Início</a>
    <a href="privacidade.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-shield-lock me-1"></i>Ver Política de Privacidade</a>
  </div>
</main>

<footer class="text-center small text-muted py-4 border-top mt-5">
  Meu Dinheiro · projeto acadêmico de controle financeiro para estudantes · <a href="termos.php" class="text-muted text-decoration-underline">Termos de Uso</a> · <a href="privacidade.php" class="text-muted text-decoration-underline">Política de Privacidade</a>
</footer>
<?php publico_fim(); ?>
