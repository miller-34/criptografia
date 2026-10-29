# Cofre Seguro — Gerenciador de Senhas

O **Cofre Seguro** é uma aplicação web para armazenar e organizar credenciais pessoais. Cada pessoa cria sua própria conta e recebe um cofre privado, no qual pode guardar senhas de e-mails, bancos, redes sociais, sistemas e outros serviços.

O projeto foi desenvolvido em PHP com MySQL e está preparado para execução com Docker.

## O que a aplicação permite fazer

- Criar uma conta e entrar com e-mail e senha.
- Cadastrar uma credencial informando serviço, site, usuário, senha e observações.
- Listar as credenciais pertencentes ao usuário autenticado.
- Visualizar, ocultar e copiar uma senha guardada.
- Editar uma credencial sem precisar alterar sua senha.
- Excluir credenciais do cofre.
- Encerrar a sessão com segurança.

Cada usuário acessa somente os dados do próprio cofre.

## Como a segurança funciona

Existem dois tipos diferentes de senha na aplicação:

1. **Senha de acesso:** usada para entrar no sistema. Ela é transformada com `password_hash` e não pode ser recuperada em texto puro.
2. **Senhas guardadas no cofre:** precisam ser recuperadas pelo proprietário, por isso são criptografadas com libsodium antes de serem enviadas ao MySQL.

A chave usada para criptografar o cofre fica na variável `APP_KEY`, separada do banco de dados. O sistema também possui:

- Consultas preparadas contra injeção de SQL.
- Proteção CSRF nos formulários.
- Cookies de sessão com `HttpOnly` e `SameSite`.
- Regeneração da sessão após o login.
- Validação e escape dos dados exibidos nas páginas.
- Verificação do proprietário em todas as operações do cofre.

> **Importante:** este é um projeto educacional. Antes de uma publicação real, utilize HTTPS, senhas fortes para o banco, uma `APP_KEY` exclusiva e uma solução segura para gerenciamento de segredos.

## Tecnologias utilizadas

- PHP 8.3
- Apache HTTP Server
- MySQL 8.4
- HTML, CSS e JavaScript
- Docker e Docker Compose
- Extensões PHP MySQLi e Sodium

## Executando com Docker

### Requisitos

- Docker 24 ou superior
- Docker Compose v2

### Inicialização

1. Crie o arquivo local de configuração:

   ```bash
   cp .env.example .env
   ```

2. Construa e inicie os contêineres:

   ```bash
   docker compose up --build -d
   ```

3. Abra [http://localhost:18080](http://localhost:18080) no navegador.

O primeiro acesso apresenta a página de cadastro. Depois de criar uma conta e entrar, a aplicação direciona o usuário para seu cofre de senhas.

### Comandos úteis

Ver os contêineres:

```bash
docker compose ps
```

Acompanhar os logs da aplicação:

```bash
docker compose logs -f app
```

Encerrar os contêineres sem apagar o banco:

```bash
docker compose down
```

Encerrar os contêineres e apagar todos os dados do banco:

```bash
docker compose down -v
```

> O comando com `-v` remove permanentemente contas e credenciais armazenadas no ambiente local.

## Variáveis de ambiente

As configurações ficam no arquivo `.env`, que não deve ser enviado ao repositório.

| Variável | Descrição | Valor local padrão |
|---|---|---|
| `APP_PORT` | Porta HTTP exposta no computador | `18080` |
| `APP_KEY` | Chave Base64 usada para criptografar o cofre | chave de desenvolvimento |
| `DB_NAME` | Nome do banco de dados | `auth_app` |
| `DB_USER` | Usuário utilizado pela aplicação | `auth_user` |
| `DB_PASSWORD` | Senha do usuário da aplicação | `change_me` |
| `DB_ROOT_PASSWORD` | Senha administrativa do MySQL | `change_root_me` |

Para gerar uma nova `APP_KEY`:

```bash
php -r 'echo base64_encode(random_bytes(32)), PHP_EOL;'
```

Copie o resultado para o arquivo `.env` antes de armazenar credenciais. A mesma chave deve ser mantida enquanto os dados existirem. Se ela for perdida ou alterada, as senhas já criptografadas não poderão ser recuperadas.

## Fluxo da aplicação

```text
Cadastro → Login → Cofre pessoal → Cadastrar credencial
                              ├── Visualizar e copiar
                              ├── Editar
                              └── Excluir
```

Ao guardar uma credencial, os campos sensíveis são criptografados pela aplicação. O MySQL recebe somente o conteúdo cifrado. Durante a visualização, a aplicação confirma a sessão e o proprietário da credencial antes de descriptografá-la.

## Estrutura do projeto

```text
.
├── docker/
│   ├── apache/             # configurações do servidor HTTP
│   ├── mysql/              # criação inicial das tabelas
│   └── php/                # configurações de segurança do PHP
├── public/                     # páginas acessíveis e arquivos estáticos
│   └── assets/             # CSS e JavaScript
├── src/                        # banco, autenticação, cofre e criptografia
├── templates/                  # cabeçalho, rodapé e formulários
├── compose.yaml               # serviços da aplicação e MySQL
├── Dockerfile                 # imagem PHP com Apache
└── .env.example               # exemplo das variáveis de ambiente
```

## Banco de dados

O banco utiliza duas tabelas principais:

- `users`: contas que podem acessar a aplicação.
- `vault_entries`: credenciais criptografadas pertencentes a cada conta.

O MySQL executa `docker/mysql/001-schema.sql` quando o volume é criado pela primeira vez. A aplicação também cria de forma idempotente qualquer tabela ausente, permitindo atualizar um volume de desenvolvimento existente sem remover seus usuários.
