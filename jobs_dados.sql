CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_job VARCHAR(255) NOT NULL,
    status ENUM('sucesso', 'falha', 'pendente') NOT NULL,
    data_execucao DATE NOT NULL,
    duracao_segundos INT NOT NULL,
    sistema_origem VARCHAR(100) NOT NULL
);
