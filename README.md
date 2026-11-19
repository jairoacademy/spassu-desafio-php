# Cadastro de livros

Projeto desenvolvido para um desafio técnico, em PHP 8.5 e MySQL 8.4.

## Núcleo

É a base própria da aplicação, em PHP puro: resolve banco, HTTP e erros uma vez só, e cada recurso cuida apenas da sua regra de negócio. Classes em `src/Nucleo/`:

- `Banco`: PDO, consultas com parâmetros e transações
- `Repositorio`: CRUD genérico, cada tabela herda dele
- `Http`: rotas, token dos formulários e página de erro em um só lugar
- `Requisicao` / `Resposta`: entrada do formulário, saída em HTML ou JSON
- `Sessao`: token CSRF e aviso de uma vez só
- `View`: páginas PHP dentro do layout, com escape de HTML
- `ErroAplicacao`: erro com status HTTP (404, 409, 422...)

Testes:

    docker compose exec app composer test
