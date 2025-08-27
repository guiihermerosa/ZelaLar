# ZelaLar - Marketplace de Profissionais de Aluguel

ZelaLar é um MVP (Minimum Viable Product) de marketplace que conecta clientes a profissionais de serviços diversos como CFTV, pedreiros, pintores, encanadores, eletricistas e jardineiros.

## 🚀 Características

- **Landing Page** atrativa com apresentação das categorias
- **Sistema de Cadastro** para profissionais
- **Listagem e Filtros** por categoria
- **Integração com WhatsApp** para agendamento
- **Design Responsivo** para mobile e desktop
- **Validação de Formulários** em tempo real
- **Upload de Fotos** para profissionais

## 🛠️ Tecnologias Utilizadas

- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Ícones**: Font Awesome 6.0
- **Design**: CSS Grid e Flexbox

## 🎨 Paleta de Cores

- **Azul Petróleo**: #1B4965 (cabeçalho, títulos)
- **Verde Água**: #5FA8D3 (botões, destaques)
- **Cinza Claro**: #F8F9FA (background)
- **Cinza Médio**: #495057 (texto)

## 📁 Estrutura do Projeto

```
ZelaLar/
├── index.php              # Landing page principal
├── profissionais.php      # Página de cadastro
├── listagem.php          # Lista de profissionais
├── css/
│   └── style.css         # Estilos principais
├── js/
│   └── main.js           # JavaScript e validações
├── config/
│   └── database.php      # Configuração do banco
├── img/
│   └── profissionais/    # Fotos dos profissionais
├── database.sql          # Script de criação do banco
└── README.md             # Este arquivo
```

## ⚙️ Requisitos do Sistema

- **Servidor Web**: Apache/Nginx
- **PHP**: 7.4 ou superior
- **MySQL**: 5.7 ou superior
- **Extensões PHP**: PDO, PDO_MySQL, GD (para upload de imagens)

## 🚀 Instalação

### 1. Configuração do Servidor

1. Clone ou baixe o projeto para a pasta do seu servidor web
2. Certifique-se de que o servidor web e PHP estão funcionando

### 2. Configuração do Banco de Dados

1. Acesse o phpMyAdmin ou seu cliente MySQL preferido
2. Execute o script `database.sql` para criar:
   - Banco de dados `zelalar_db`
   - Tabela `profissionais`
   - Dados de exemplo

**Alternativa via linha de comando:**
```bash
mysql -u root -p < database.sql
```

### 3. Configuração da Conexão

1. Edite o arquivo `config/database.php`
2. Ajuste as credenciais conforme seu ambiente:

```php
define('DB_HOST', 'localhost');     // Host do banco
define('DB_NAME', 'zelalar_db');    // Nome do banco
define('DB_USER', 'root');          // Usuário do banco
define('DB_PASS', '');              // Senha do banco
```

### 4. Permissões de Diretório

1. Crie a pasta `img/profissionais/` se não existir
2. Configure as permissões para upload de arquivos:

```bash
chmod 755 img/profissionais/
```

## 📱 Como Usar

### Para Clientes

1. **Navegar pelas Categorias**: Acesse a página inicial para ver todas as categorias disponíveis
2. **Ver Profissionais**: Clique em "Ver Profissionais" para acessar a listagem
3. **Filtrar por Categoria**: Use o filtro para encontrar profissionais específicos
4. **Contatar**: Clique nos botões WhatsApp ou telefone para entrar em contato

### Para Profissionais

1. **Cadastrar**: Acesse "Cadastrar" no menu principal
2. **Preencher Formulário**: Complete todos os campos obrigatórios
3. **Enviar Foto**: Adicione uma foto profissional (opcional)
4. **Confirmar**: Clique em "Cadastrar Profissional"

## 🔧 Personalização

### Alterar Categorias

Para adicionar ou modificar categorias, edite o arquivo `index.php` na seção de categorias:

```php
<div class="category-card">
    <div class="category-icon">
        <i class="fas fa-toolbox"></i> <!-- Ícone Font Awesome -->
    </div>
    <h3>Nova Categoria</h3>
    <p>Descrição da nova categoria</p>
    <a href="https://wa.me/5511999999999?text=..." class="btn-whatsapp">
        <i class="fab fa-whatsapp"></i> Agende no WhatsApp
    </a>
</div>
```

### Alterar Cores

Modifique as variáveis CSS no arquivo `css/style.css`:

```css
.header {
    background-color: #1B4965; /* Azul petróleo */
}

.btn-primary {
    background-color: #5FA8D3; /* Verde água */
}
```

### Adicionar Novos Campos

Para adicionar novos campos aos profissionais:

1. **Banco de Dados**: Adicione a coluna na tabela `profissionais`
2. **Formulário**: Adicione o campo em `profissionais.php`
3. **Listagem**: Exiba o campo em `listagem.php`
4. **Validação**: Atualize o JavaScript se necessário

## 🐛 Solução de Problemas

### Erro de Conexão com Banco

- Verifique se o MySQL está rodando
- Confirme as credenciais em `config/database.php`
- Teste a conexão via phpMyAdmin

### Upload de Fotos Não Funciona

- Verifique as permissões da pasta `img/profissionais/`
- Confirme se a extensão GD do PHP está ativa
- Verifique o limite de upload no `php.ini`

### Página Não Carrega

- Verifique se o servidor web está funcionando
- Confirme se o PHP está ativo
- Verifique os logs de erro do servidor

## 📞 Suporte

Para suporte técnico ou dúvidas:

- **Email**: contato@zelalar.com
- **WhatsApp**: (11) 99999-9999

## 📄 Licença

Este projeto é um MVP desenvolvido para demonstração. Use conforme necessário para fins educacionais ou comerciais.

## 🔄 Atualizações Futuras

- Sistema de avaliações e comentários
- Geolocalização para encontrar profissionais próximos
- Sistema de pagamentos integrado
- App mobile nativo
- Painel administrativo
- Sistema de notificações

---

**Desenvolvido com ❤️ para conectar profissionais e clientes**
