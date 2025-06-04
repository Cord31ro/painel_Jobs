<?php
$mysqli = new mysqli('localhost', 'root', '', 'painel_jobs');

if ($mysqli->connect_error) {
    die('Erro na conexão: ' . $mysqli->connect_error);
} else {
    echo 'Conexão bem sucedida!';
}
?>