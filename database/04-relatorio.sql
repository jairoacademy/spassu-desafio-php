me suCREATE OR REPLACE VIEW vw_livros_por_autor AS
SELECT
    a.CodAutor,
    a.Nome AS Autor,
    l.CodLivro,
    l.Titulo,
    l.Editora,
    l.Edicao,
    l.AnoPublicacao,
    l.Valor,
    assuntos.Assuntos
FROM Livro l
    INNER JOIN Livro_Autor la ON la.Livro_CodLivro = l.CodLivro
    INNER JOIN Autor a ON a.CodAutor = la.Autor_CodAutor
    LEFT JOIN (
        SELECT
            ls.Livro_CodLivro,
            GROUP_CONCAT(s.Descricao ORDER BY s.Descricao SEPARATOR ', ') AS Assuntos
        FROM Livro_Assunto ls
            INNER JOIN Assunto s ON s.CodAssunto = ls.Assunto_CodAssunto
        GROUP BY ls.Livro_CodLivro
    ) assuntos ON assuntos.Livro_CodLivro = l.CodLivro;
