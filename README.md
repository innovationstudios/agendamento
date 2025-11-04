

<div align="center">
   <h1>Agendamento - Gestão de Mensalidades</h1>
   <p><b>Sistema open source para gestão de mensalidades, clientes, planos, fornecedores e notificações.</b></p>
   <p>Desenvolvido em <b>PHP</b> • Interface responsiva • Integração com WhatsApp (Evolution API) e E-mail</p>
   <img src="https://img.shields.io/badge/PHP-8.1%2B-blue" alt="PHP 8.1+">
   <img src="https://img.shields.io/badge/MySQL-MariaDB-blue" alt="MySQL/MariaDB">
   <img src="https://img.shields.io/badge/License-MIT-green" alt="MIT License">
</div>

<p align="center">
<b>Ideal para academias, escolas, clubes, associações e negócios que trabalham com cobranças recorrentes.</b>
</p>

---

## � Visão Geral

O Agendamento é um sistema web para controle de mensalidades, clientes, planos e fornecedores, com notificações automáticas por e-mail e WhatsApp (Evolution API). Permite gerenciar cobranças recorrentes de forma simples e eficiente.

---


## 🗂️ Estrutura das Páginas

| Página                | Função                                                                                   |
|-----------------------|-----------------------------------------------------------------------------------------|
| `index.php`           | Tela de login do sistema. Valida usuário e senha, inicia sessão e redireciona ao painel.|
| `dashboard.php`       | Tela inicial com estatísticas, valores recebidos e mensalidades recentes.               |
| `clientes.php`        | Lista, cadastra e gerencia clientes (usuários ativos) e histórico de exclusão.          |
| `mensalidades.php`    | Gerencia mensalidades (pagas, pendentes, vencidas), com filtros e registro de pagamento.|
| `planos.php`          | Lista, cadastra e edita planos de mensalidade, vinculando a fornecedores.               |
| `fornecedores.php`    | Lista, cadastra, edita e exclui fornecedores de planos.                                 |
| `vencidos.php`        | Exibe apenas mensalidades vencidas, com opção de registrar pagamento.                   |
| `perfil.php`          | Permite ao usuário logado visualizar e editar seus dados pessoais.                      |
| `logout.php`          | Encerra a sessão do usuário e redireciona para o login.                                 |
| `configuracoes.php`   | Configura parâmetros de e-mail e WhatsApp (API Evolution) para notificações.            |
| `templates.php`       | Gerencia templates de mensagens automáticas (e-mail/WhatsApp) para clientes.            |

Ideal para academias, escolas, clubes, associações e negócios que trabalham com cobranças recorrentes.



---

## 🚀 Funcionalidades

- ✔️ Login de usuários e controle de sessão
- ✔️ Tela inicial com estatísticas e visão geral do sistema
- ✔️ Cadastro, edição, exclusão e restauração de clientes (usuários)
- ✔️ Cadastro, edição e exclusão de planos de mensalidade
- ✔️ Cadastro, edição e exclusão de fornecedores
- ✔️ Geração, visualização e controle de mensalidades (pagas, pendentes, vencidas)
- ✔️ Registro de pagamentos de mensalidades
- ✔️ Filtros por status e período nas mensalidades
- ✔️ Visualização de mensalidades vencidas
- ✔️ Gerenciamento de templates de mensagens automáticas (e-mail/WhatsApp)
- ✔️ Configuração de parâmetros de e-mail e WhatsApp (API Evolution) via tela de configurações
- ✔️ Histórico de exclusão/restauração de clientes
- ✔️ Edição de perfil do usuário logado
- ✔️ Logout seguro

---


---

## 🛠️ Instalação

### Instalação com Docker

1. Certifique-se de ter o [Docker](https://www.docker.com/) e o [Docker Compose](https://docs.docker.com/compose/) instalados.
2. No terminal, execute:
   ```bash
   docker-compose up -d
   ```
3. O sistema estará disponível em [http://localhost:8080](http://localhost:8080) e o phpMyAdmin em [http://localhost:8081](http://localhost:8081).
4. O banco de dados será criado automaticamente com as credenciais:
   - Host: db
   - Usuário: agendamento
   - Senha: agendamento
   - Banco: gestao_mensalidades
5. Após subir os containers, acesse o phpMyAdmin, selecione o banco `gestao_mensalidades` e importe o arquivo `database.sql` para criar as tabelas.
6. Pronto! O sistema estará rodando em ambiente isolado e pronto para uso.

### 1. Pré-requisitos
- PHP >= 8.1
- MySQL/MariaDB
- Composer
- Servidor web (Apache, Nginx, etc.)

### 2. Passos para Instalar
```bash
# Clone o repositório
git clone https://github.com/innovationstudios/agendamento.git

# Instale as dependências do Composer
composer install

# Crie o banco de dados e as tabelas
mysql -u usuario -p < database.sql
```

1. Configure o servidor web para apontar para a pasta do projeto.
2. Acesse `http://localhost/agendamento` no navegador.

### 3. Estrutura do Banco de Dados
O script `database.sql` cria as tabelas principais: `usuarios`, `clientes`, `fornecedores`, `planos`, `mensalidades`, `mensagem_templates`, `atividades`, `configuracoes`.

---



---


## ⚙️ Configuração

### Integrações
- E-mail e WhatsApp (Evolution API) são configurados pelo sistema, na tela `configuracoes.php`, e salvos no banco de dados.

### Banco de Dados
A conexão é configurada em `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gestao_mensalidades');
```

---

---


## 💬 Integração com Evolution API (WhatsApp)

O sistema permite o envio automático de mensagens de cobrança, lembretes e notificações via WhatsApp utilizando a Evolution API.

### Como configurar
1. Acesse a tela de configurações (`configuracoes.php`).
2. Preencha os campos:
   - **API Key:** Chave fornecida pela Evolution API.
   - **API URL:** URL do endpoint da Evolution API (opcional, pode usar o padrão).
   - **Número do WhatsApp:** Número da instância (ex: 5511999999999).
3. Salve as configurações.
4. Utilize a opção "Testar Conexão" para validar a integração.

### Como funciona
- O sistema utiliza os dados salvos para enviar mensagens automáticas de cobrança, lembrete de vencimento e confirmação de pagamento para os clientes.
- Os templates das mensagens podem ser personalizados em `templates.php`.

> **Atenção:** É necessário possuir uma conta ativa na Evolution API e configurar corretamente a instância para o envio funcionar.

---



## 📝 Como Usar

1. Cadastre usuários, clientes, planos e fornecedores.
2. Gere mensalidades e acompanhe o status (pagas, vencidas, pendentes).
3. Configure notificações automáticas por e-mail e WhatsApp em `configuracoes.php`.
4. Personalize templates de mensagens em `templates.php`.

---



## 🤝 Contribuição

Pull requests são bem-vindos! Para grandes mudanças, abra uma issue para discutir o que gostaria de modificar.

---

## 📄 Licença

[MIT](LICENSE)
