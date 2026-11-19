# Cadastro de livros

Projeto desenvolvido para um desafio técnico, em PHP 8.5 e MySQL 8.4.

## Núcleo

É a base própria da aplicação, em PHP puro: resolve banco, HTTP e erros uma vez só, e cada recurso cuida apenas da sua regra de negócio. Classes em `src/Nucleo/`:

- `Banco`: PDO, consultas com parâmetros e transações
- `Repositorio`: CRUD genérico, cada tabela herda dele
- `Http`: rotas e tratamento de erros em um só lugar
- `Requisicao` / `Resposta`: entrada e saída em JSON
- `ErroAplicacao`: erro com status HTTP (404, 409, 422...)

Testes:

    docker compose exec app composer test
