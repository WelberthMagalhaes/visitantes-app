# Visitantes - PHP 8.4 + PostgreSQL

Sistema de cadastro de visitantes para igrejas, pronto para deploy no Render.com (plano free). Interface web com autenticação por senha, prevenção de duplicidade no mesmo dia, busca instantânea via localStorage e API REST para integração com Holyrics.

## ✨ Funcionalidades

- 📝 **Cadastro Individual**: Interface simples para recepção
- 👥 **Acompanhantes**: Campo para registrar quantas pessoas vieram junto
- 📝 **Observações**: Anotações por visita (nomes de familiares, amigos, etc)
- 📊 **Histórico Completo**: Cada visita registrada separadamente
- 🔒 **Autenticação**: Login com senha para acesso à interface
- 🚫 **Prevenção de Duplicatas**: Não permite cadastro duplicado no mesmo dia
- 🔍 **Busca Instantânea**: Autocomplete com visitantes já cadastrados
- 📋 **Lista de Visitantes**: Visualização dos visitantes do dia com contagem total de pessoas
- 🔌 **API REST**: Endpoint para Holyrics com autenticação via API Key
- 💾 **Offline First**: Funciona localmente via localStorage com sincronização

## 🐳 Desenvolvimento Local (Docker)

**Requisitos**: Docker e Docker Compose

```bash
# 1. Clone o repositório
git clone https://github.com/SEU_USUARIO/visitantes-app.git
cd visitantes-app

# 2. Configure as variáveis de ambiente
cp .env.example .env
# Edite o .env e defina suas credenciais

# 3. Setup automático
./setup.sh

# Ou manualmente:
docker-compose -f docker-compose.yml -f docker-compose.postgres.yml build
docker-compose -f docker-compose.yml -f docker-compose.postgres.yml up -d
```

**Acesso**: http://localhost:8080/
**Senha padrão**: Configure no `.env` (variável `SENHA_INTERNA`)



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
  - Gere uma chave forte: `openssl rand -hex 32`
- **SENHA_INTERNA:** Protege interface web da recepção via autenticação por sessão
- **Validação:** Parâmetro `data` é obrigatório na API externa
- **Sessões:** Interface web usa sessões PHP para manter login
- **Backups:** PostgreSQL no Render tem backup automático (plano free: 7 dias)
- **⚠️ NUNCA commite o arquivo `.env`** - Use `.env.example` como template

Veja [SECURITY.md](SECURITY.md) para mais detalhes sobre segurança.

## 📈 Contabilização de Pessoas

O sistema agora registra:
- **Visitantes cadastrados**: Pessoas que preencheram o formulário
- **Acompanhantes**: Pessoas que vieram junto (campo numérico)
- **Total de pessoas**: Visitantes + Acompanhantes

Exemplo:
```
João Silva - 3 acompanhantes
Observação: Esposa Maria, filhos Pedro e Ana

Total: 4 pessoas (1 visitante + 3 acompanhantes)
```

## 📦 Estrutura do Projeto

```
visitantes-app/
├── database/
│   ├── schema.sql           # Schema PostgreSQL

├── public/
│   ├── cadastrar.html       # Interface de cadastro
│   ├── visitantes-hoje.html # Lista de visitantes do dia
│   ├── index.php            # Router e endpoints API
│   └── style.css            # Estilos
├── src/
│   ├── auth.php             # Autenticação e sessões
│   ├── db.php               # Conexão PostgreSQL
│   ├── utils.php            # Funções utilitárias
│   └── visitantes.php       # Lógica de negócio
├── .env                     # Variáveis locais
├── .env.example             # Template de variáveis
├── Dockerfile               # Imagem Docker PHP 8.4
├── docker-compose.yml       # Orquestração local (Web)
├── docker-compose.postgres.yml # Orquestração local (DB)
└── setup.sh                 # Script de setup automático
```

## 📊 Endpoints

### API Externa (com API_KEY)
- `GET /api/visitantes?data=YYYY-MM-DD` - Lista visitantes por data

### API Interna (com sessão)
- `POST /interno/login` - Autenticação
- `POST /interno/visitantes` - Cadastrar visitante (com acompanhantes e observação)
- `PUT /interno/visitas/{id}` - Atualizar observação/acompanhantes de uma visita
- `GET /interno/visitantes/all` - Listar todos
- `GET /interno/visitantes/hoje` - Listar visitantes de hoje



## ⚖️ Licença

MIT
