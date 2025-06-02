<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard de Jobs</title>

  <!-- Ad Grid Estilos -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-grid.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-theme-alpine.css">

  <style>
    html, body {
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
      text-align: rigth;
      margin: 10px;

    }

  </style>
</head>
<body>
  

<?php 
session_start();
if (!isset($_SESSION['usuario'])){
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

<!-- AG DRIG SCRIPTl -->

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const columnDefs = [
      {headerName: 'ID', field: 'id', sortable: true, filter: true},
      {headerName: 'Job', field: 'nome_job', sortable: true, filter: true},
      {headerName: 'Status', field: 'status', sortable: true, filter: true},
      {headerName: 'Data Execucao', field: 'data_execucao', sortable: true, filter: true},
      {headerName: 'Ducação (s)', field: 'duracao_segundos', sortable: true, filter: true},
      {headerName: 'Sistema Origem', field: 'sistema_origem', sortable: true, filter: true},

];

const gridOptions ={
  columnDefs: columnDefs,
  defaultColDef: {
    flex: 1,
    minWidth: 100,
    resizable: true,
  },
  animateRows: true,
  rowData: null
};

 const gridDiv  = document.querySelector('#grid');
 new agGrid.Grid(gridDiv, gridOptions);

 fetch('src/tratarDados_jobs.php')
 .then(response => response.json())
 .then(data => gridOptions.api.setRowData(data))
 .catch(error => console.error('Erro ao carregar dados:', error));
  

});
</script>

</body>
</html>



