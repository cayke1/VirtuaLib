USE LivrosDB;

CREATE TABLE IF NOT EXISTS livros (
    id_livro INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    autor VARCHAR(100) NOT NULL,
    editora VARCHAR(100),
    ano_publicacao YEAR,
    genero VARCHAR(50),
    isbn VARCHAR(20) UNIQUE
);