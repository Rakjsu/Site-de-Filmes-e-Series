# Site de Filmes e Series

Plataforma de streaming para filmes e séries, com reprodução de vídeos, gerenciamento de perfis de usuário e recursos de navegação.

## Funcionalidades

- Sistema de login e registro de usuários
- Catálogo de filmes e séries
- Player de vídeo com suporte a streaming adaptativo (HLS)
- Continuar assistindo (retomar de onde parou)
- Histórico de visualizações
- Favoritos
- Recursos administrativos para usuários com permissões especiais

## Tecnologias

- PHP
- JavaScript
- HTML5/CSS3
- Font Awesome para ícones
- HLS.js para streaming adaptativo de vídeos

## Requisitos

- Servidor web (Apache, Nginx)
- PHP 7.4 ou superior
- MySQL ou MariaDB

## Instalação

1. Clone o repositório
```bash
git clone https://github.com/Rakjsu/Site-de-Filmes-e-Series.git
```

2. Configure seu servidor web para apontar para o diretório do projeto

3. Importe o arquivo de banco de dados
```bash
mysql -u root -p site < api/database.sql
```

4. Configure as credenciais de banco de dados em `db_config.php` (já configurado com valores padrão)

5. Acesse o site através do navegador

## Licença

Este projeto está licenciado sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

## Testes Automatizados

### Backend (PHPUnit)

- Para rodar todos os testes unitários e de integração do backend:

```sh
composer test
```
Ou diretamente:
```sh
vendor/bin/phpunit --colors=always
```

Os testes estão em `/tests` e cobrem autenticação, filmes, séries, usuários, gêneros, configurações, logs, estatísticas e APIs.

### Frontend (Cypress)

- Para rodar os testes E2E do frontend:

```sh
npx cypress open
```
Ou em modo headless:
```sh
npx cypress run
```

Os testes estão em `/cypress/e2e` e cobrem login, navegação e APIs.

### Boas práticas para colaboração
- Sempre escreva testes para novas funcionalidades e correções de bugs.
- Mantenha os testes organizados por módulo.
- Rode todos os testes antes de enviar PRs ou deploys.
- Documente cenários de teste relevantes no próprio código ou no README.

---

Dúvidas ou sugestões? Abra uma issue ou PR! 