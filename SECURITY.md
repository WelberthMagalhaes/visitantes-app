# Política de Segurança

## Configuração Segura

### Variáveis de Ambiente Obrigatórias

Nunca commite o arquivo `.env` com credenciais reais. Use o `.env.example` como template.

**Variáveis necessárias:**

```bash
# API Key para integração externa (Holyrics)
# Gere com: openssl rand -hex 32
API_KEY=sua_chave_api_secreta_aqui

# Senha para acesso à interface web
SENHA_INTERNA=sua_senha_recepcao_aqui

# Database (PostgreSQL)
DB_HOST=
DB_PORT=
DB_NAME=
DB_USER=
DB_PASS=
```

### GitHub Secrets (para workflow)

Configure no repositório em **Settings → Secrets and variables → Actions**:

- `RENDER_API_KEY`: Sua API key do Render
- `RENDER_APP_URL`: URL da sua aplicação (ex: https://seu-app.onrender.com)

## Reportar Vulnerabilidades

Se você encontrar uma vulnerabilidade de segurança, por favor:

1. **NÃO** abra uma issue pública
2. Entre em contato diretamente com os mantenedores
3. Aguarde confirmação antes de divulgar publicamente

## Boas Práticas

- ✅ Use senhas fortes (mínimo 16 caracteres)
- ✅ Gere API keys com `openssl rand -hex 32`
- ✅ Mantenha o `.env` fora do controle de versão
- ✅ Use HTTPS em produção
- ✅ Atualize dependências regularmente
- ❌ Nunca commite credenciais no código
- ❌ Nunca compartilhe API keys publicamente
