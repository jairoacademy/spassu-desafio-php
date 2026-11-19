CREATE TABLE IF NOT EXISTS Livro (
    CodLivro INT NOT NULL AUTO_INCREMENT,
    Titulo VARCHAR(40) NOT NULL,
    Editora VARCHAR(40) NOT NULL,
    Edicao INT NOT NULL,
    AnoPublicacao VARCHAR(4) NOT NULL,
    Valor DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (CodLivro),
    INDEX idx_livro_titulo (Titulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Autor (
    CodAutor INT NOT NULL AUTO_INCREMENT,
    Nome VARCHAR(40) NOT NULL,
    PRIMARY KEY (CodAutor),
    UNIQUE INDEX uq_autor_nome (Nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Assunto (
    CodAssunto INT NOT NULL AUTO_INCREMENT,
    Descricao VARCHAR(20) NOT NULL,
    PRIMARY KEY (CodAssunto),
    UNIQUE INDEX uq_assunto_descricao (Descricao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Livro_Autor (
    Livro_CodLivro INT NOT NULL,
    Autor_CodAutor INT NOT NULL,
    PRIMARY KEY (Livro_CodLivro, Autor_CodAutor),
    INDEX Livro_Autor_FKIndex1 (Livro_CodLivro),
    INDEX Livro_Autor_FKIndex2 (Autor_CodAutor),
    CONSTRAINT fk_livro_autor_livro
        FOREIGN KEY (Livro_CodLivro) REFERENCES Livro (CodLivro)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_livro_autor_autor
        FOREIGN KEY (Autor_CodAutor) REFERENCES Autor (CodAutor)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Livro_Assunto (
    Livro_CodLivro INT NOT NULL,
    Assunto_CodAssunto INT NOT NULL,
    PRIMARY KEY (Livro_CodLivro, Assunto_CodAssunto),
    INDEX Livro_Assunto_FKIndex1 (Livro_CodLivro),
    INDEX Livro_Assunto_FKIndex2 (Assunto_CodAssunto),
    CONSTRAINT fk_livro_assunto_livro
        FOREIGN KEY (Livro_CodLivro) REFERENCES Livro (CodLivro)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_livro_assunto_assunto
        FOREIGN KEY (Assunto_CodAssunto) REFERENCES Assunto (CodAssunto)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
