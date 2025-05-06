# Sistema de Loteria

Sistema para gerenciamento de apostas em jogos de loteria, com suporte a múltiplos perfis de usuários.

## Descrição

Este sistema foi desenvolvido para gerenciar apostas de loteria, permitindo que revendedores registrem apostas para seus clientes, administrem suas comissões e gerem comprovantes. Os apostadores podem acompanhar seus jogos, verificar resultados e solicitar pagamentos de prêmios. Administradores têm controle total sobre o sistema, incluindo cadastro de jogos, sorteios e gerenciamento de usuários.

## Funcionalidades Principais

### Perfil Administrador
- Dashboard com estatísticas gerais
- Gerenciamento de usuários (revendedores e apostadores)
- Configuração de jogos e sorteios
- Lançamento de resultados
- Geração de relatórios
- Administração de pagamentos

### Perfil Revendedor
- Dashboard com estatísticas de vendas
- Registro de apostas para clientes
- Controle de comissões
- Geração de comprovantes em PDF
- Relatórios de vendas

### Perfil Apostador
- Visualização de apostas realizadas
- Consulta de resultados
- Solicitação de pagamento de prêmios
- Visualização e impressão de comprovantes

## Tecnologias Utilizadas

- PHP 7.4+
- MySQL 5.7+
- HTML5, CSS3, JavaScript
- Biblioteca TCPDF para geração de PDFs

## Requisitos

- Servidor web (Apache/Nginx)
- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Extensões PHP: mysqli, session, gd, mbstring

## Instalação

1. Clone ou baixe os arquivos para o diretório do seu servidor web
2. Crie um banco de dados MySQL
3. Importe o arquivo `install.sql` para criar as tabelas e dados iniciais
4. Configure a conexão com o banco de dados no arquivo `includes/db.php`
5. Acesse o sistema pelo navegador e faça login com as credenciais:
   - **Administrador**: admin / admin123
   - **Revendedor**: revenda1 / senha123
   - **Apostador**: apostador1 / senha123

## Estrutura de Diretórios

```
loteria/
│
├── index.php                   → Tela de login
├── install.sql                 → Script SQL para criar o banco
├── README.md                   → Descrição do projeto
│
├── /admin/
│   └── index.php               → Dashboard do Admin
│
├── /revendedor/
│   └── index.php               → Dashboard do Revendedor
│
├── /usuario/
│   └── index.php               → Dashboard do Apostador
│
├── /includes/
│   ├── db.php                  → Conexão com o banco
│   ├── auth.php                → Autenticação por perfil
│   ├── functions.php           → Funções gerais (PDF, jogos)
│   └── tcpdf/                  → Biblioteca para gerar PDFs
│
├── /assets/
│   └── /css/
│       └── style.css           → Estilo básico
│
└── /uploads/                   → Pasta para PDFs gerados
```

## TCPDF - Biblioteca para PDFs

Antes de utilizar as funcionalidades de geração de PDF, é necessário instalar a biblioteca TCPDF. Siga os passos abaixo:

1. Baixe a biblioteca TCPDF em [https://github.com/tecnickcom/tcpdf/releases](https://github.com/tecnickcom/tcpdf/releases)
2. Extraia o conteúdo e copie para a pasta `includes/tcpdf/`
3. Verifique se o arquivo `tcpdf.php` está presente no diretório `includes/tcpdf/`

## Segurança

- Todas as senhas são armazenadas com hash usando a função `password_hash()` do PHP
- Todas as consultas SQL utilizam prepared statements para prevenir injeção de SQL
- Validação de entrada em todos os formulários
- Controle de acesso baseado no tipo de usuário
- Registro de atividades no sistema para fins de auditoria

## Autor

Nome: [Seu Nome]
Email: [seu.email@exemplo.com]

## Licença

Este projeto está licenciado sob [sua licença escolhida]. 