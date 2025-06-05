<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

//Teste inicial de execução (retirada o echo pois estava enviando dados em formato ñ Json)


//Conexão//

$host = 'localhost';
$dbname = 'painel_jobs'; //no computador o nome do banco está como painel_jobs
$user = 'root';
$pass = '';  //no computador não tem senha 12345 o SQL (caso não receba os dados rodar na porta 36ou 37 )

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Error na conexão: ' . $conn->connect_error]);
    exit;
}

// echo "Conectado com sucesso!<br>";

// Consulta
$sql = "SELECT * FROM jobs";
$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro na consulta SQL: ' . $conn->error]);
    exit;
}

// echo "Consulta executada com sucesso!<br>";

// Mostrar resultado bruto
$dados = [];
while ($row = $result->fetch_assoc()) {
    $dados[] = $row;
}

$conn->close();

//envia JSON sem usar print
echo json_encode($dados);
?>