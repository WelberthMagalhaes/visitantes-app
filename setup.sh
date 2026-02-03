#!/bin/bash

echo "🐳 Iniciando setup do projeto com Docker (Web + PostgreSQL)..."

# Build da imagem e subida dos containers (Web + DB)
docker-compose -f docker-compose.yml -f docker-compose.postgres.yml build
docker-compose -f docker-compose.yml -f docker-compose.postgres.yml up -d

# Aguarda o container estar pronto
echo "⏳ Aguardando containers inicializarem..."
sleep 10

echo "✅ Setup concluído!"
echo "🌐 Acesse: http://localhost:8080/"
echo "🔑 Senha da recepção: (Configure no arquivo .env)"