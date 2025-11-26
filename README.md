# Visitantes - PHP + SQLite

Projeto pronto para deploy no Render.com (plano free). Sistema simples para cadastro de visitantes, prevenção de duplicidade via localStorage + busca instantânea, backend em PHP + SQLite, autenticação mínima via API Key e endpoint JSON para Holyrics.

## Desenvolvimento Local (Docker)

**Requisitos**: Docker e Docker Compose

```bash
# Setup automático
./setup.sh

# Ou manualmente:
docker-compose up -d
curl http://localhost:8080/database/criar_banco.php
```

**Acesso**: http://localhost:8080/cadastrar.html

## Deploy no Render.com

1. Clone o repositório e faça `git push` para o GitHub.
2. No Render, crie um novo Web Service apontando para o repositório. Runtime: Docker.
3. Defina a variável de ambiente `API_KEY` no painel do serviço (valor secreto). Ex: `MINHA_CHAVE_SUPER_SECRETA`.
4. O banco é criado automaticamente no build do Docker.
5. Abra `/cadastrar.html` para a recepção.
6. Configure o Holyrics para consumir `https://seu-app.onrender.com/api/visitantes?data=YYYY-MM-DD&api_key=MINHA_CHAVE` ou configure cabeçalho `X-API-KEY` se suportado.

## Observações de segurança

- **API_KEY:** Defina um segredo forte em Render (ou outra hospedagem) chamado `API_KEY`. O backend checa `X-API-KEY` header e, como fallback, `?api_key=` query param.
- **Holyrics:** se o Holyrics aceita cabeçalho personalizado, configure `X-API-KEY` com o mesmo valor. Se não aceitar, utilize o query param com cuidado.
- **Backups:** Faça backup do arquivo `database/visitantes.sqlite` regularmente.
