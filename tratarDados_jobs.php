<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Teste inicial de execução
echo "Início do script<br>";

// Conexão
$host = 'localhost';
$dbname = 'jobs';
$user = 'root';
$pass = '12345';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

echo "Conectado com sucesso!<br>";

// Consulta
$sql = "SELECT * FROM jobs";
$result = $conn->query($sql);

if (!$result) {
    die("Erro na consulta SQL: " . $conn->error);
}

echo "Consulta executada com sucesso!<br>";

// Mostrar resultado bruto
while ($row = $result->fetch_assoc()) {
    echo "<pre>";
    print_r($row);
    echo "</pre>";
}

$conn->close();
?>