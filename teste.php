<?php
session_start();
$_SESSION['usuario'] = 'Teste'; // simula login
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Teste Charts</title>
  <script src="https://cdn.jsdelivr.net/npm/ag-charts-community/dist/ag-charts-community.min.js"></script>
</head>
<body>

  <div>Bem-vindo, <?php echo $_SESSION['usuario']; ?></div>
  <div id="chartSistema" style="width: 600px; height: 400px;"></div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const sistemaChartData = [
        { sistema: 'SAP', media: 12 },
        { sistema: 'TOTVS', media: 18 },
        { sistema: 'Outros', media: 8 },
      ];

      agCharts.AgChart.create({
        container: document.getElementById('chartSistema'),
        data: sistemaChartData,
        series: [{
          type: 'bar',
          xKey: 'sistema',
          yKey: 'media'
        }],
        title: {
          text: 'Duração Média por Sistema de Origem',
          fontSize: 17
        },
        axes: [
          {
            type: 'category',
            position: 'bottom',
            title: { text: 'Sistema de Origem' }
          },
          {
            type: 'number',
            position: 'left',
            title: { text: 'Duração Média (s)' }
          }
        ]
      });
    });
  </script>

</body>
</html>
