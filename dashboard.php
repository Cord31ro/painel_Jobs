
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Jobs</title>

    <!-- AG Grid CSS - Apenas o tema, sem o ag-grid.css para evitar conflito -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-theme-quartz.css">

    <!-- Removido CSS do AG Charts que não existe -->

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
        
        .logout a {
            color: white;
            text-decoration: none;
        }
        
        .charts-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin-top: 30px;
            gap: 20px;
        }
        
        .chart-box {
            width: 45%;
            height: 400px;
            min-width: 300px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
        }
        
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
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

<div class="charts-container">
    <div class="chart-box">
        <div id="chartStatus">
            <div class="loading">Carregando gráfico de status...</div>
        </div>
    </div>
    <div class="chart-box">
        <div id="chartSistema">
            <div class="loading">Carregando gráfico de sistemas...</div>
        </div>
    </div>
</div>

    <!-- Scripts em ordem correta - usando CDNs que funcionam -->
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community@31.3.0/dist/ag-grid-community.min.js"></script>
    <script src="libs/ag-charts/ag-charts-community.min.js"></script>


    <script>
        // Função para aguardar todas as bibliotecas carregarem
        function waitForLibraries() {
            return new Promise((resolve) => {
                const checkLibraries = () => {
                    // Verificar diferentes nomes possíveis para AG Charts
                    const chartsAvailable = (
                        typeof agCharts !== 'undefined' || 
                        typeof AgCharts !== 'undefined' || 
                        typeof window.agCharts !== 'undefined' ||
                        typeof window.AgCharts !== 'undefined'
                    );
                    
                    if (typeof agGrid !== 'undefined' && chartsAvailable) {
                        resolve();
                    } else {
                        setTimeout(checkLibraries, 200);
                    }
                };
                checkLibraries();
            });
        }

        // Inicializar dashboard após carregar tudo
        waitForLibraries().then(() => {
            initializeDashboard();
        });

        function initializeDashboard() {
            const columnDefs = [
                { headerName: 'ID', field: 'id', sortable: true, filter: true, width: 80 },
                { headerName: 'Job', field: 'nome_job', sortable: true, filter: true },
                { headerName: 'Status', field: 'status', sortable: true, filter: true, width: 120 },
                { headerName: 'Data Execução', field: 'data_execucao', sortable: true, filter: true },
                { headerName: 'Duração (s)', field: 'duracao_segundos', sortable: true, filter: true, width: 120 },
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
                rowData: [],
                onGridReady: function (params) {
                    console.log("🚀 AG Grid pronto! Carregando dados...");
                    loadData(params);
                }
            };

            // Inicializar o grid
            const gridDiv = document.querySelector('#grid');
            const gridApi = agGrid.createGrid(gridDiv, gridOptions);
        }

        function loadData(gridParams) {
            fetch('tratarDados_jobs.php')
                .then(response => {
                    console.log("📡 Resposta recebida:", response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (gridParams.api && typeof gridParams.api.setRowData === 'function') {
                        gridParams.api.setRowData(data);
         
                    }

                    // Criar gráficos
                    createCharts(data);
                })
                .catch(error => {
                    console.error('❌ Erro ao carregar dados:', error);
                    alert('Erro ao carregar dados: ' + error.message);
                });
        }

        function createCharts(data) {
            
            // Descobrir qual variável do AG Charts está disponível
            let ChartsAPI = null;
            if (typeof agCharts !== 'undefined' && agCharts.AgCharts) {
                ChartsAPI = agCharts.AgCharts;
            } else if (typeof AgCharts !== 'undefined') {
                ChartsAPI = AgCharts;
                
            } else if (typeof window.agCharts !== 'undefined' && window.agCharts.AgCharts) {
                ChartsAPI = window.agCharts.AgCharts;
                
            } else if (typeof window.AgCharts !== 'undefined') {
                ChartsAPI = window.AgCharts;
                
            }
            
                if (!ChartsAPI || !ChartsAPI.create) {
                console.error("AG Charts não carregado.");
                document.getElementById('chartStatus').innerHTML = '<div class="loading">AG Charts não carregou</div>';
                document.getElementById('chartSistema').innerHTML = '<div class="loading">AG Charts não carregou</div>';
                return;
            }
            
            
                // Gráfico de Status (Pizza)
                const statusContagem = {};
                data.forEach(job => {
                    const status = job.status || 'Indefinido';
                    statusContagem[status] = (statusContagem[status] || 0) + 1;
                });

                const statusChartData = Object.entries(statusContagem).map(([status, count]) => ({
                    status,
                    count
                }));

                console.log("📊 Dados do gráfico de status:", statusChartData);

                ChartsAPI.create({
                    container: document.getElementById("chartStatus"),
                    data: statusChartData,
                    series: [{
                        type: 'pie',
                        angleKey: 'count',
                        labelKey: 'status',
                        outerRadiusRatio: 0.8,
                        innerRadiusRatio: 0.3, // Donut style
                        label: {
                            enabled: true
                        }
                    }],
                    title: {
                        text: 'Distribuição de Jobs por Status',
                        fontSize: 16,
                        fontWeight: 'bold'
                    }
                });

                // Gráfico de Sistemas (Barras)
                const sistemas = {};
                data.forEach(job => {
                    const sistema = job.sistema_origem || 'Não informado';
                    if (!sistemas[sistema]) {
                        sistemas[sistema] = { total: 0, count: 0 };
                    }
                    const duracao = parseFloat(job.duracao_segundos) || 0;
                    sistemas[sistema].total += duracao;
                    sistemas[sistema].count++;
                });

                const sistemaChartData = Object.entries(sistemas).map(([sistema, info]) => ({
                    sistema,
                    media: info.count > 0 ? (info.total / info.count) : 0
                }));
                            // Remover "loading"
                document.getElementById('chartSistema').innerHTML = '';

                ChartsAPI.create({
                    container: document.getElementById('chartSistema'),
                    data: sistemaChartData,
                    series: [{
                        type: 'bar',
                        xKey: 'sistema',
                        yKey: 'media',
                        fill: '#007bff'
                    }],
                    title: {
                        text: 'Duração Média por Sistema',
                        fontSize: 16,
                        fontWeight: 'bold'
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

                console.log("✅ Gráficos criados com sucesso!");

            }
        
    </script>

</body>
</html>