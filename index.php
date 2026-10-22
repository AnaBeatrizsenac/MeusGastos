<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/publico.php';
$entrar = logado() ? ['dashboard.php', 'Meu painel'] : ['login.php', 'Entrar'];
$comecar = logado() ? 'dashboard.php' : 'cadastro.php';
publico_topo('Organize suas finanças', 'landing');
?>
<header class="land-nav">
  <div class="container d-flex align-items-center">
    <a class="brand" href="index.php"><?= logo_svg(40) ?><span><strong>Meu Dinheiro</strong><small>Controle Financeiro para Estudantes</small></span></a>
    <nav class="ms-auto d-none d-md-flex align-items-center gap-4">
      <a href="#inicio">Início</a><a href="#sobre">Sobre</a><a href="#funcionalidades">Funcionalidades</a>
      <a href="<?= $entrar[0] ?>" class="btn btn-md-primary rounded-pill px-4"><?= $entrar[1] ?></a>
    </nav>
    <a href="<?= $entrar[0] ?>" class="btn btn-md-primary rounded-pill ms-auto d-md-none"><?= $entrar[1] ?></a>
  </div>
</header>

<section id="inicio" class="hero">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <h1>Organize suas finanças e conquiste seus objetivos!</h1>
        <p class="lead">O Meu Dinheiro é um sistema simples e seguro para você controlar seus gastos, acompanhar seu saldo e planejar o seu futuro.</p>
        <div class="d-flex flex-wrap gap-3">
          <a href="<?= $comecar ?>" class="btn btn-md-primary btn-lg rounded-pill px-4">Começar agora</a>
          <a href="#sobre" class="btn btn-md-outline btn-lg rounded-pill px-4">Saiba mais</a>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="phone">
          <div class="phone-tela">
            <div class="d-flex justify-content-between fw-bold small"><span>MeuDinheiro</span><i class="bi bi-list"></i></div>
            <div class="small text-muted mt-3">Saldo disponível</div>
            <div class="fs-4 fw-bold">R$ 1.250,00</div>
            <svg viewBox="0 0 200 60" class="w-100 my-2"><polyline fill="none" stroke="#16a34a" stroke-width="3" stroke-linejoin="round" points="0,50 30,42 55,46 85,30 115,34 145,18 175,22 200,6"/></svg>
            <?php foreach ([['Moradia', 45, '#0f766e'], ['Alimentação', 20, '#16a34a'], ['Transporte', 15, '#f59e0b'], ['Lazer', 10, '#ef4444'], ['Outros', 10, '#64748b']] as [$n, $p, $c]): ?>
              <div class="d-flex align-items-center small py-1"><span class="cat-dot" style="background:<?= $c ?>"></span><?= $n ?><span class="ms-auto fw-semibold"><?= $p ?>%</span></div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
    <div class="row g-4 mt-4 destaques">
      <?php foreach ([['bi-speedometer2', 'Controle suas despesas e receitas'], ['bi-wallet2', 'Acompanhe seu saldo'], ['bi-bullseye', 'Crie metas de economia'], ['bi-bar-chart-line', 'Veja gráficos e relatórios']] as [$i, $t]): ?>
        <div class="col-6 col-lg-3"><div class="d-flex align-items-center gap-3"><span class="ico-circulo"><i class="bi <?= $i ?>"></i></span><span><?= $t ?></span></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="sobre" class="secao">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-5"><h2>Sobre o projeto</h2></div>
      <div class="col-lg-7">
        <p class="fs-5">O Meu Dinheiro nasceu de uma pergunta comum entre estudantes: "para onde foi a mesada?".</p>
        <p class="text-muted">É um sistema web para anotar o que entra e o que sai, ver os gastos por categoria e guardar dinheiro para objetivos concretos, como um fone, um notebook ou a viagem de formatura. Sem planilha complicada e sem propaganda.</p>
      </div>
    </div>
  </div>
</section>

<section id="funcionalidades" class="secao alt">
  <div class="container">
    <h2 class="mb-4">O que dá para fazer</h2>
    <ol class="lista-func">
      <li><strong>Lançar entradas e gastos.</strong> Mesada, bolsa, estágio, lanche, ônibus: cada um com categoria, data e uma observação se precisar.</li>
      <li><strong>Ver o mês num relance.</strong> Saldo, total recebido, total gasto e um gráfico mostrando o que pesou mais.</li>
      <li><strong>Criar metas.</strong> Defina quanto quer juntar, vá guardando aos poucos e acompanhe a porcentagem.</li>
      <li><strong>Receber avisos.</strong> Quando os gastos passam do ponto ou uma meta está quase completa.</li>
      <li><strong>Consultar o histórico.</strong> Filtre, corrija, apague e exporte para planilha ou PDF.</li>
      <li><strong>Comparar os meses.</strong> Relatórios mostram a evolução e onde dá para economizar.</li>
    </ol>
  </div>
</section>
<footer class="text-center small text-muted py-4">Meu Dinheiro · projeto acadêmico de controle financeiro para estudantes · <a href="termos.php" class="text-muted text-decoration-underline">Termos de Uso</a> · <a href="privacidade.php" class="text-muted text-decoration-underline">Política de Privacidade</a></footer>
<?php publico_fim(); ?>
