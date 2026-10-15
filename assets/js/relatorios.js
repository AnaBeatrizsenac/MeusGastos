(function () {
  const canvas = document.getElementById('graficoMeses');
  if (!canvas || typeof Chart === 'undefined') return;
  const serie = JSON.parse(canvas.dataset.serie);
  const meses = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
  const rotulo = (ym) => meses[parseInt(ym.slice(5), 10) - 1] + '/' + ym.slice(2, 4);
  const css = (v) => getComputedStyle(document.documentElement).getPropertyValue(v).trim();

  let grafico;
  function montar() {
    if (grafico) grafico.destroy();
    Chart.defaults.color = css('--mudo');
    Chart.defaults.font.family = css('--fonte-corpo') || 'sans-serif';
    grafico = new Chart(canvas, {
      type: 'bar',
      data: {
        labels: serie.map((s) => rotulo(s.ym)),
        datasets: [
          { label: 'Entradas', data: serie.map((s) => s.entradas), backgroundColor: css('--verde'), borderRadius: 3 },
          { label: 'Gastos', data: serie.map((s) => s.gastos), backgroundColor: css('--vermelho'), borderRadius: 3 },
        ],
      },
      options: {
        maintainAspectRatio: false,
        scales: {
          x: { grid: { display: false } },
          y: { beginAtZero: true, grid: { color: css('--borda') }, ticks: { callback: (v) => 'R$ ' + v.toLocaleString('pt-BR') } },
        },
        plugins: {
          legend: { position: 'bottom', labels: { boxWidth: 10, boxHeight: 10 } },
          tooltip: { callbacks: { label: (c) => ' ' + c.dataset.label + ': ' + c.parsed.y.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) } },
        },
      },
    });
  }
  montar();
  document.addEventListener('md:tema', montar);
})();
