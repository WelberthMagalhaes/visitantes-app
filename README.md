# Visitantes - PHP 8.4 + SQLite

Sistema de cadastro de visitantes para igrejas, pronto para deploy no Render.com (plano free). Interface web com autenticação por senha, prevenção de duplicidade no mesmo dia, busca instantânea via localStorage e API REST para integração com Holyrics.

## ✨ Funcionalidades

- 📝 **Cadastro Individual**: Interface simples para recepção
- 🔒 **Autenticação**: Login com senha para acesso à interface
- 🚫 **Prevenção de Duplicatas**: Não permite cadastro duplicado no mesmo dia
- 🔍 **Busca Instantânea**: Autocomplete com visitantes já cadastrados
- 📋 **Lista de Visitantes**: Visualização dos visitantes do dia
- 🔌 **API REST**: Endpoint para Holyrics com autenticação via API Key
- 💾 **Offline First**: Funciona localmente via localStorage com sincronização

## 🐳 Desenvolvimento Local (Docker)

**Requisitos**: Docker e Docker Compose

```bash
# Setup automático
./setup.sh

# Ou manualmente:
docker-compose build
docker-compose up -d
docker-compose exec web php /var/www/html/database/criar_banco.php
```

**Acesso**: http://localhost:8080/
**Senha padrão**: `hope-recepcao523` (definida no `.env`)

## 🚀 Deploy no Render.com

1. **Faça push para o GitHub:**
   ```bash
   git init
   git add .
   git commit -m "Deploy inicial"
   git remote add origin https://github.com/SEU_USUARIO/visitantes-app.git
   git push -u origin main
   ```

2. **Crie PostgreSQL Database no Render:**
   - No dashboard, clique em "New +" → "PostgreSQL"
   - **Name:** `visitantes-db`
   - **Instance Type:** Free
   - Aguarde a criação (~2 min)
   - Copie a **Internal Database URL**

3. **Crie Web Service no Render:**
   - Clique em "New +" → "Web Service"
   - Conecte seu repositório GitHub
   - **Runtime:** Docker
   - **Instance Type:** Free

4. **Configure Variáveis de Ambiente:**
   ```
   DATABASE_URL=postgresql://user:pass@host/db (cole a Internal Database URL)
   API_KEY=sua_chave_secreta_aqui
   SENHA_INTERNA=sua_senha_recepcao_aqui
   ```

5. **Criar tabelas no banco:**
   - Após o deploy, acesse: `https://seu-app.onrender.com/database/criar_banco.php`
   - Você verá: "Banco criado com sucesso"

6. **Acesse sua aplicação:**
   - Interface: `https://seu-app.onrender.com/`
   - API: `https://seu-app.onrender.com/api/visitantes?data=2024-11-26&api_key=SUA_CHAVE`

## 🎵 Integração com Holyrics

**Endpoint:** `GET /api/visitantes`

**Parâmetros obrigatórios:**
- `data`: Data no formato `YYYY-MM-DD`
- `api_key`: Sua chave de API (query param) **OU** header `X-API-KEY`

**Exemplo:**
```
https://seu-app.onrender.com/api/visitantes?data=2024-11-26&api_key=SUA_CHAVE
```

**Resposta:**
```json
[
  {"id": 1, "nome": "João Silva", "visitas": 1},
  {"id": 2, "nome": "Maria Santos", "visitas": 3}
]
```

## 🔒 Segurança

- **API_KEY:** Protege endpoint externo (Holyrics). Aceita header `X-API-KEY` ou query param `api_key`
- **SENHA_INTERNA:** Protege interface web da recepção via autenticação por sessão
- **Validação:** Parâmetro `data` é obrigatório na API externa
- **Sessões:** Interface web usa sessões PHP para manter login
- **Backups:** Faça backup do arquivo `database/visitantes.sqlite` regularmente

## 📦 Estrutura do Projeto

```
visitantes-app/
├── database/
│   ├── criar_banco.php      # Script de criação do banco
│   └── visitantes.sqlite    # Banco SQLite (criado automaticamente)
├── public/
│   ├── cadastrar.html       # Interface de cadastro
│   ├── visitantes-hoje.html # Lista de visitantes do dia
│   ├── index.php            # Router e endpoints API
│   └── style.css            # Estilos
├── src/
│   ├── auth.php             # Autenticação e sessões
│   ├── db.php               # Conexão SQLite
│   ├── utils.php            # Funções utilitárias
│   └── visitantes.php       # Lógica de negócio
├── .env                     # Variáveis locais
├── Dockerfile               # Imagem Docker PHP 8.4
├── docker-compose.yml       # Orquestração local
└── setup.sh                 # Script de setup automático
```

## 📊 Endpoints

### API Externa (com API_KEY)
- `GET /api/visitantes?data=YYYY-MM-DD` - Lista visitantes por data

### API Interna (com sessão)
- `POST /interno/login` - Autenticação
- `POST /interno/visitantes` - Cadastrar visitante
- `GET /interno/visitantes/all` - Listar todos
- `GET /interno/visitantes/hoje` - Listar visitantes de hoje

## ⚖️ Licença

MIT
