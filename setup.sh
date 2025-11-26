#!/bin/bash

echo "🐳 Iniciando setup do projeto com Docker..."

# Build da imagem
docker-compose build

# Inicia os containers
docker-compose up -d

# Aguarda o container estar pronto
echo "⏳ Aguardando container inicializar..."
sleep 5

# Cria o banco de dados
echo "🗄️ Criando banco de dados..."
docker-compose exec web php /var/www/html/database/criar_banco.php

# Corrige permissões do banco
echo "🔧 Corrigindo permissões..."
docker-compose exec web chown -R www-data:www-data /var/www/html/database
docker-compose exec web chmod 775 /var/www/html/database
docker-compose exec web chmod 664 /var/www/html/database/visitantes.sqlite

echo "✅ Setup concluído!"
echo "🌐 Acesse: http://localhost:8080/"
echo "🔑 Senha da recepção: hope-recepcao523"