# Perfil e Feedback

Sistema web em PHP com cadastro, login, perfil com foto e envio de feedback com nota de 0 a 10. Projeto de estudo do primeiro semestre de Sistemas de Informação, com foco em boas práticas de segurança e organização de código.

## Funcionalidades

- Cadastro com validação de nome, e-mail e senha (mínimo de 12 caracteres, com confirmação)
- Login e logout seguros
- Página de perfil com dados do usuário e foto (JPG, PNG ou WebP, até 2 MB)
- Formulário de feedback com `textarea` e `input range` de 0 a 10
- Histórico dos feedbacks enviados
- Interface responsiva e acessível (rótulos associados, foco visível, respeito a `prefers-reduced-motion`)

## Tecnologias

- PHP 8.2+ (orientado a objetos, `strict_types`, enums)
- MySQL / MariaDB com PDO e *prepared statements*
- HTML5, CSS3 (variáveis CSS, Grid e Flexbox) e JavaScript puro
- Composer (autoload PSR-4 e `vlucas/phpdotenv`)

## Segurança

- Senhas com `password_hash` (bcrypt), nunca em texto puro
- Proteção contra SQL Injection (*prepared statements*), XSS (escape de toda saída) e CSRF (token em todo formulário POST)
- Sessão com cookie `HttpOnly` e `SameSite`, e regeneração do identificador no login e no logout
- Mensagem de erro genérica no login e tempo de resposta equalizado para não revelar e-mails cadastrados
- Upload validado pelo conteúdo real do arquivo (`finfo` e `getimagesize`), com limite de tamanho e de dimensões, nome aleatório e execução de scripts bloqueada na pasta de uploads
- Cabeçalhos de segurança e *Content Security Policy* (sem CSS ou JavaScript dentro das páginas)
- Credenciais fora do código, em arquivo `.env`

## Estrutura do projeto

```text
ex004/
├── database/        script SQL do banco
├── public/          única pasta acessível pelo navegador
│   ├── assets/      CSS e JavaScript
│   └── uploads/     fotos de perfil
├── src/
│   ├── Config/      conexão com o banco
│   ├── Enums/       tipos de usuário
│   ├── Exceptions/  exceções próprias
│   ├── Models/      regras de negócio (Usuario, Feedback)
│   ├── Repositories/ acesso ao banco (SQL)
│   ├── Services/    upload de foto
│   └── Support/     Csrf, Flash, Auth e funções auxiliares
└── templates/       cabeçalho, rodapé e alertas reutilizáveis
```

## Como executar

Requisitos: PHP 8.2 ou superior (extensões `pdo_mysql`, `mbstring` e `fileinfo`), MySQL ou MariaDB e Composer.

1. Clone o repositório e entre na pasta:
   ```bash
   git clone https://github.com/[seu-usuario]/[nome-do-repositorio].git
   cd [nome-do-repositorio]
   ```
2. Instale as dependências:
   ```bash
   composer install
   ```
3. Crie o arquivo de configuração e ajuste os valores (usuário e senha do seu MySQL):
   ```bash
   cp .env.example .env
   ```
   No Windows, copie e renomeie o arquivo manualmente.
4. Crie o banco importando `database/schema.sql` pelo phpMyAdmin (aba **Importar**) ou pelo terminal:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
5. Inicie o servidor apontando para a pasta `public`:
   ```bash
   php -S localhost:8000 -t public
   ```
6. Acesse `http://localhost:8000`.

> Importante: a raiz do servidor deve ser a pasta `public/`. Assim, `.env`, `src/` e `vendor/` ficam inacessíveis pelo navegador.

## Telas

<!-- Coloque os prints em uma pasta "prints/" e ajuste os nomes abaixo -->
| Login | Cadastro | Perfil |
|---|---|---|
| ![Login](prints/login.png) | ![Cadastro](prints/cadastro.png) | ![Perfil](prints/perfil.png) |

## Melhorias futuras

- Limite de tentativas de login (tabela de tentativas ou *rate limiting*)
- Atualização do hash da senha com `password_needs_rehash`
- Painel para administradores com a média das notas
- Roteador único (*front controller*) e testes automatizados (PHPUnit)
- Recuperação de senha por e-mail

## Autor

[Seu nome] · [LinkedIn](https://www.linkedin.com/in/[seu-perfil]) · [GitHub](https://github.com/[seu-usuario])