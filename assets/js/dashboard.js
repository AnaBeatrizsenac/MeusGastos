(function () {
  const canvas = document.getElementById('graficoGastos');
  if (!canvas || typeof Chart === 'undefined') return;
  const dados = JSON.parse(canvas.dataset.dados);
  const fundo = () => getComputedStyle(document.documentElement).getPropertyValue('--card').trim();

  const grafico = new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels: dados.map((d) => d.nome),
      datasets: [{ data: dados.map((d) => d.total), backgroundColor: dados.map((d) => d.cor), borderWidth: 2, borderColor: fundo() }],
    },
    options: {
      cutout: '68%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (c) => ' ' + c.label + ': ' + c.parsed.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }),
          },
        },
      },
    },
  });
  document.addEventListener('md:tema', () => {
    grafico.data.datasets[0].borderColor = fundo();
    grafico.update();
  });
})();
