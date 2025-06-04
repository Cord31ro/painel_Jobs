<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard de Jobs</title>

  <!-- Ag Grid Estilos atualizados-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-grid.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-theme-alpine.css">
  <style>
    html,
    body {
      height: 100%;
      margin: 0;
      font-family: Arial, sans-serif;
    }

    .ag-theme-alpine {
      height: 80vh;
      width: 100%;
    }

    header {
      background-color: #007bff;
      color: white;
      padding: 10px;
      text-align: center;
    }

    .logout {
      text-align: right;
      margin: 10px;
    }
  </style>
</head>

<body>

  <?php
  session_start();
  if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
  }
  ?>

  <header>
    <h1>Painel de Monitoramento Jobs</h1>
    <div class="logout">
      Bem-vindo, <?php echo $_SESSION['usuario']; ?> |
      <a href="logout.php">Sair</a>
    </div>
  </header>

  <div id="grid" class="ag-theme-alpine"></div>

  <!-- Inclusão dos gráficos -->
  <div style="display: flex; flex-wrap: wrap; justify-content: space-around; margin-top: 30px;">
    <div id="chartStatus" style="width: 45%; height: 400px;"></div>
    <div id="chartSistema" style="width: 45%; height: 400px;"></div>
  </div>

  <!-- Ag Grid Script atualizado com versão global exposta -->
  <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.noStyle.js"></script>
  <!-- Ag Charts Script -->
  <script src="https://cdn.jsdelivr.net/npm/ag-charts-community/dist/ag-charts-community.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const columnDefs = [
        { headerName: 'ID', field: 'id', sortable: true, filter: true },
        { headerName: 'Job', field: 'nome_job', sortable: true, filter: true },
        { headerName: 'Status', field: 'status', sortable: true, filter: true },
        { headerName: 'Data Execucao', field: 'data_execucao', sortable: true, filter: true },
        { headerName: 'Duração (s)', field: 'duracao_segundos', sortable: true, filter: true },
        { headerName: 'Sistema Origem', field: 'sistema_origem', sortable: true, filter: true },
      ];

      const gridOptions = {
        columnDefs: columnDefs,
        defaultColDef: {
          flex: 1,
          minWidth: 100,
          resizable: true,
        },
        animateRows: true,
        rowData: null
      };

      const gridDiv = document.querySelector('#grid');
      agGrid.createGrid(gridDiv, gridOptions);

      fetch('src/tratarDados_jobs.php')
        .then(response => response.json())
        .then(data => {
          gridOptions.api.setRowData(data);

          // Gráfico de pizza - Distribuição por status
          const statusContagem = {};
          data.forEach(job => {
            statusContagem[job.status] = (statusContagem[job.status] || 0) + 1;
          });

          const statusChartData = Object.entries(statusContagem).map(([status, count]) => ({
            status,
            count
          }));

          agCharts.AgChart.create({
            container: document.getElementById("chartStatus"),
            data: statusChartData,
            series: [{
              type: 'pie',
              angleKey: 'count',
              labelKey: 'status'
            }],
            title: {
              text: 'Distribuição de Jobs por Status',
              fontSize: 18
            }
          });

          // Gráfico de barras - Duração média por sistema de origem
          const sistemas = {};
          data.forEach(job => {
            const sistema = job.sistema_origem;
            if (!sistemas[sistema]) {
              sistemas[sistema] = { total: 0, count: 0 };
            }
            sistemas[sistema].total += parseInt(job.duracao_segundos);
            sistemas[sistema].count++;
          });

          const sistemaChartData = Object.entries(sistemas).map(([sistema, info]) => ({
            sistema,
            media: info.total / info.count
          }));

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

        })
        .catch(error => console.error('Erro ao carregar dado', error));
    });
  </script>

</body>
<!-- teste de alteração git 01:36 -->
</html>
