# 🚀 ZELALAR - GUIA DE INSTALAÇÃO COMPLETO

## 📋 Pré-requisitos

### Sistema Operacional
- **Windows 10/11** (recomendado para desenvolvimento)
- **Linux** (Ubuntu 20.04+ para produção)
- **macOS** (para desenvolvimento)

### Servidor Web
- **Apache 2.4+** ou **Nginx 1.18+**
- **PHP 8.0+** (recomendado PHP 8.1+)
- **MySQL 8.0+** ou **MariaDB 10.5+**

### Extensões PHP Obrigatórias
```bash
php-mysql
php-gd
php-mbstring
php-curl
php-json
php-xml
php-zip
php-opcache
```

### Navegadores Suportados
- **Chrome 90+**
- **Firefox 88+**
- **Safari 14+**
- **Edge 90+**

## 🛠️ Instalação Passo a Passo

### 1. Preparação do Ambiente

#### Windows (XAMPP/WAMP)
```bash
# Baixar e instalar XAMPP
# https://www.apachefriends.org/download.html

# Ou WAMP
# https://www.wampserver.com/en/
```

#### Linux (Ubuntu/Debian)
```bash
# Atualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Apache, PHP e MySQL
sudo apt install apache2 php8.1 php8.1-mysql php8.1-gd php8.1-mbstring php8.1-curl php8.1-json php8.1-xml php8.1-zip php8.1-opcache mysql-server -y

# Habilitar mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### macOS
```bash
# Instalar Homebrew
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Instalar PHP e MySQL
brew install php mysql

# Instalar Apache
brew install httpd
```

### 2. Configuração do Banco de Dados

#### Criar Banco de Dados
```sql
-- Conectar ao MySQL
mysql -u root -p

-- Criar banco de dados
CREATE DATABASE zelalar_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Criar usuário (opcional, mas recomendado)
CREATE USER 'zelalar_user'@'localhost' IDENTIFIED BY 'senha_segura_aqui';
GRANT ALL PRIVILEGES ON zelalar_db.* TO 'zelalar_user'@'localhost';
FLUSH PRIVILEGES;

-- Sair
EXIT;
```

#### Importar Estrutura
```bash
# Importar arquivo SQL
mysql -u root -p zelalar_db < database.sql
```

### 3. Configuração do Projeto

#### Clonar/Download
```bash
# Se usando Git
git clone https://github.com/seu-usuario/zelalar.git
cd zelalar

# Ou baixar ZIP e extrair
```

#### Configurar Permissões
```bash
# Linux/macOS
chmod 755 -R .
chmod 777 -R img/profissionais/
chmod 777 -R cache/
chmod 777 -R logs/
chmod 777 -R backups/

# Windows (via Explorer)
# Clicar com botão direito → Propriedades → Segurança → Editar → Adicionar permissões
```

#### Configurar Banco de Dados
```bash
# Editar arquivo config/database.php
# Alterar as seguintes linhas:
define('DB_HOST', 'localhost');
define('DB_NAME', 'zelalar_db');
define('DB_USER', 'zelalar_user');  # ou 'root'
define('DB_PASS', 'sua_senha_aqui');
```

#### Configurar Ambiente
```bash
# Para desenvolvimento
cp config/env.development config/env.local

# Para produção
cp config/env.production config/env.local

# Editar config/env.local com suas configurações
```

### 4. Configuração do Servidor Web

#### Apache (.htaccess já configurado)
```apache
# O arquivo .htaccess já está configurado
# Verificar se mod_rewrite está habilitado
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx
```nginx
server {
    listen 80;
    server_name zelalar.local;
    root /var/www/zelalar;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Configurações de cache e compressão
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 5. Configuração de Email

#### Gmail (Recomendado para produção)
```bash
# 1. Ativar verificação em 2 etapas na conta Google
# 2. Gerar senha de app
# 3. Configurar no arquivo env.production:
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=seu-email@gmail.com
SMTP_PASSWORD=sua-senha-de-app
SMTP_SECURE=tls
```

#### Mailtrap (Para desenvolvimento/testes)
```bash
# Usar configurações do env.development
# Mailtrap é gratuito para testes
```

### 6. Configuração de Analytics

#### Google Analytics
```bash
# 1. Criar conta no Google Analytics
# 2. Obter ID de rastreamento (G-XXXXXXXXXX)
# 3. Configurar no arquivo env.production:
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
```

#### Facebook Pixel
```bash
# 1. Criar conta no Facebook Business
# 2. Criar pixel de rastreamento
# 3. Configurar no arquivo env.production:
FACEBOOK_PIXEL_ID=XXXXXXXXXX
```

### 7. Configuração de Notificações Push

#### VAPID Keys
```bash
# 1. Instalar web-push
npm install -g web-push

# 2. Gerar VAPID keys
web-push generate-vapid-keys

# 3. Configurar no arquivo env.production:
VAPID_PUBLIC_KEY=sua-chave-publica
VAPID_PRIVATE_KEY=sua-chave-privada
```

## 🔧 Configurações Avançadas

### 1. Cache e Performance

#### Redis (Opcional)
```bash
# Instalar Redis
sudo apt install redis-server

# Configurar no PHP
sudo apt install php8.1-redis
```

#### Memcached (Opcional)
```bash
# Instalar Memcached
sudo apt install memcached php8.1-memcached
```

### 2. Segurança

#### SSL/HTTPS
```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-apache

# Gerar certificado
sudo certbot --apache -d zelalar.com -d www.zelalar.com
```

#### Firewall
```bash
# Configurar UFW
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable
```

### 3. Backup Automático

#### Script de Backup
```bash
# Criar script de backup
sudo nano /usr/local/bin/zelalar-backup.sh

#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/zelalar"
DB_NAME="zelalar_db"
DB_USER="zelalar_user"
DB_PASS="sua_senha"

# Criar diretório de backup
mkdir -p $BACKUP_DIR

# Backup do banco
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_$DATE.sql

# Backup dos arquivos
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/zelalar

# Manter apenas os últimos 30 backups
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

# Tornar executável
sudo chmod +x /usr/local/bin/zelalar-backup.sh

# Adicionar ao cron
sudo crontab -e
# Adicionar linha:
0 2 * * * /usr/local/bin/zelalar-backup.sh
```

## 🧪 Testes e Verificação

### 1. Teste de Funcionamento
```bash
# Acessar o site
http://localhost/zelalar

# Verificar se não há erros no log
tail -f /var/log/apache2/error.log
```

### 2. Teste do Banco
```bash
# Verificar conexão
php -r "require 'config/database.php'; echo isDatabaseAccessible() ? 'OK' : 'ERRO';"
```

### 3. Teste de Upload
```bash
# Tentar cadastrar um profissional com foto
# Verificar se a imagem foi salva em img/profissionais/
```

### 4. Teste de WhatsApp
```bash
# Clicar nos botões de WhatsApp
# Verificar se abre o app com a mensagem correta
```

## 🚀 Deploy em Produção

### 1. Preparação
```bash
# Configurar domínio
# Configurar DNS
# Configurar SSL
# Configurar backup automático
```

### 2. Otimizações
```bash
# Habilitar OPcache
# Configurar compressão GZIP
# Configurar cache de navegador
# Minificar CSS/JS
```

### 3. Monitoramento
```bash
# Configurar logs
# Configurar alertas
# Configurar métricas de performance
```

## 🔍 Solução de Problemas

### Erro de Conexão com Banco
```bash
# Verificar se MySQL está rodando
sudo systemctl status mysql

# Verificar credenciais
mysql -u zelalar_user -p

# Verificar permissões
SHOW GRANTS FOR 'zelalar_user'@'localhost';
```

### Erro de Permissão
```bash
# Verificar permissões dos diretórios
ls -la img/profissionais/
ls -la cache/
ls -la logs/

# Corrigir permissões
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 777 img/profissionais/ cache/ logs/
```

### Erro de Upload
```bash
# Verificar configurações do PHP
php -i | grep upload

# Verificar tamanho máximo
php -i | grep max_file_size
php -i | grep post_max_size

# Ajustar no php.ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Erro de Cache
```bash
# Limpar cache do navegador
# Verificar se Service Worker está funcionando
# Verificar console do navegador
```

## 📞 Suporte

### Contatos
- **Email**: suporte@zelalar.com
- **WhatsApp**: (11) 99999-9999
- **Documentação**: https://docs.zelalar.com

### Comunidade
- **GitHub**: https://github.com/zelalar
- **Discord**: https://discord.gg/zelalar
- **Telegram**: https://t.me/zelalar

## 📚 Recursos Adicionais

### Documentação
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Apache Documentation](https://httpd.apache.org/docs/)
- [PWA Documentation](https://web.dev/progressive-web-apps/)

### Ferramentas Úteis
- [PHPMyAdmin](https://www.phpmyadmin.net/) - Gerenciamento do banco
- [Composer](https://getcomposer.org/) - Gerenciador de dependências PHP
- [Node.js](https://nodejs.org/) - Para ferramentas de build
- [Git](https://git-scm.com/) - Controle de versão

---

## 🎯 Próximos Passos

Após a instalação bem-sucedida:

1. **Personalizar** o design e conteúdo
2. **Configurar** domínio e SSL
3. **Implementar** funcionalidades adicionais
4. **Otimizar** performance
5. **Configurar** monitoramento
6. **Fazer backup** regular
7. **Atualizar** regularmente

**Boa sorte com seu projeto ZelaLar! 🚀**
