<?php
session_start();

// Usuários fixos (simulação do LDAP)
$usuarios = [
    'analista1' => 'senha123',
    'admin' => 'admin456'
];

?>

$usuario = $_POST['usuario'] ?? '';
$senha = $_POST['senha'] ?? '';

if (isset($usuarios[$usuario]) && $usuarios[$usuario] === $senha) {
    $_SESSION['usuario'] = $usuario;
    header('Location: ../dashboard.php');
    exit;
} else {
    echo "<p style='color:red; text-align:center;'>Usuário ou senha inválidos.</p>";
    echo "<p style='text-align:center;'><a href='../index.php'>Voltar</a></p>";
}
