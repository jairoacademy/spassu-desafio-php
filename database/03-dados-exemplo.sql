INSERT INTO Livro (CodLivro, Titulo, Editora, Edicao, AnoPublicacao, Valor)
VALUES (1, 'Não Sei, Só Sei Que Foi Assim', 'Taperoa', 1, '2026', 39.90);

INSERT INTO Autor (CodAutor, Nome)
VALUES
    (1, 'João Grilo'),
    (2, 'Chicó');

INSERT INTO Assunto (CodAssunto, Descricao)
VALUES (1, 'Causos do Sertão');

INSERT INTO Livro_Autor (Livro_CodLivro, Autor_CodAutor)
VALUES
    (1, 1),
    (1, 2);

INSERT INTO Livro_Assunto (Livro_CodLivro, Assunto_CodAssunto)
VALUES (1, 1);
