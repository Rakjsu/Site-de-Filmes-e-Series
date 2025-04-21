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
mysql -u usuario -p nome_do_banco < api/database.sql
```

4. Configure as credenciais de banco de dados em `home_files/db_config.php`

5. Acesse o site através do navegador

## Licença

Este projeto está licenciado sob a licença MIT. Veja o arquivo LICENSE para mais detalhes. 