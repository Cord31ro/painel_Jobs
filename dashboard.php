<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Jobs</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-theme-quartz.css">

    <link rel="stylesheet" href="https://unpkg.com/ag-charts-community/styles/ag-charts-community.css">

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .ag-theme-quartz {
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

    <div id="grid" class="ag-theme-quartz"></div>

    <div style="display: flex; flex-wrap: wrap; justify-content: space-around; margin-top: 30px;">
        <div id="chartStatus" style="width: 45%; height: 400px;"></div>
        <div id="chartSistema" style="width: 45%; height: 400px;"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <script src="https://unpkg.com/ag-charts-community/dist/ag-charts-community.min.js"></script>

    <script>
        // Verificações para depuração. Esperamos 'true' para ambos agora.
        console.log("AgCharts disponível?", typeof AgCharts !== 'undefined');
        console.log("agGrid disponível", typeof agGrid !== 'undefined');
    </script>

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
                rowData: null,

                // onGridReady é o callback chamado quando o AG Grid está totalmente inicializado e pronto para a API.
                onGridReady: function (params) {
                    console.log("AG Grid está pronto. Buscando dados...");
                    fetch('tratarDados_jobs.php')
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log("Dados recebidos:", data);

                            // AG Grid: Adicionada verificação de segurança antes de usar setRowData.
                            // Isso previne o erro "TypeError: params.api.setRowData is not a function".
                            if (params.api && typeof params.api.setRowData === 'function') {
                                params.api.setRowData(data);
                            } else {
                                console.error("Erro: A API do AG Grid ou o método setRowData não está disponível. O grid pode não ser preenchido.");
                            }

                            // AG Charts: Adicionada verificação de segurança antes de usar AgCharts.create.
                            // Isso previne o "ReferenceError: AgCharts is not defined".
                            if (typeof AgCharts !== 'undefined') {
                                // --- Lógica para o primeiro gráfico (Status) ---
                                const statusContagem = {};
                                data.forEach(job => {
                                    statusContagem[job.status] = (statusContagem[job.status] || 0) + 1;
                                });

                                const statusChartData = Object.entries(statusContagem).map(([status, count]) => ({
                                    status,
                                    count
                                }));

                                AgCharts.create({
                                    container: document.getElementById("chartStatus"),
                                    data: statusChartData,
                                    series: [{
                                        type: 'pie',
                                        angleKey: 'count',
                                        labelKey: 'status',
                                        outerRadiusRatio: 0.8 // Adicionei este para um visual mais moderno, opcional
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
                                    const duracao = parseFloat(job.duracao_segundos); // Usando parseFloat para robustez
                                    if (!isNaN(duracao)) { // Verificando se a duração é um número válido
                                        sistemas[sistema].total += duracao;
                                        sistemas[sistema].count++;
                                    }
                                });

                                const sistemaChartData = Object.entries(sistemas).map(([sistema, info]) => ({
                                    sistema,
                                    media: info.count > 0 ? info.total / info.count : 0
                                }));

                                AgCharts.create({
                                    container: document.getElementById('chartSistema'),
                                    data: sistemaChartData,
                                    series: [{
                                        type: 'bar',
                                        xKey: 'sistema',
                                        yKey: 'media',
                                        tooltip: { // Adicionando tooltip para melhor UX
                                            renderer: ({ datum, xKey, yKey }) => ({
                                                content: `${datum[xKey]}: ${datum[yKey].toFixed(2)}s`
                                            })
                                        }
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
                            } else {
                                console.error("Erro: A biblioteca AG Charts não está disponível. Gráficos não serão renderizados.");
                            }
                        })
                        .catch(error => {
                            console.error('Erro ao carregar dados:', error);
                            // Este catch agora pegará erros do fetch ou do processamento de dados.
                            // Os erros de API e Charts são tratados nos 'if' statements acima.
                        });
                }
            };

            const gridDiv = document.querySelector('#grid');
            agGrid.createGrid(gridDiv, gridOptions); // Esta linha inicializa o AG Grid.
        });
    </script>

</body>
</html>