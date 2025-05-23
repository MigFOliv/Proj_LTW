<footer>
  <p>© <?= date('Y') ?> FreeLanceX. Todos os direitos reservados.</p>
</footer>

<?php if (basename($_SERVER['PHP_SELF']) === 'stats.php'): ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx = document.getElementById('statsChart')?.getContext('2d');
    if (ctx) {
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['Serviços', 'Pedidos Recebidos', 'Pedidos Concluídos'],
          datasets: [{
            label: 'Totais',
            data: [<?= $total_services ?>, <?= $total_orders ?>, <?= $completed ?>],
            backgroundColor: ['#0070f3', '#00c853', '#ff6d00'],
            borderRadius: 10
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: '#000',
              titleColor: '#fff',
              bodyColor: '#fff'
            }
          }
        }
      });
    }
  </script>
<?php endif; ?>

</body>
</html>
