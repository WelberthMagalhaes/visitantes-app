# Sistema de Cadastro de Famílias

## Resumo das Mudanças

Esta branch `develop` implementa o sistema de cadastro de famílias, permitindo registrar visitantes em grupos familiares com relacionamentos definidos.

## Novas Funcionalidades

### 1. Banco de Dados
- **Nova tabela `familias`**: Armazena informações das famílias
- **Campo `parentesco`** na tabela `visitantes`: Define o relacionamento (esposo, esposa, filho, filha, etc.)
- **Campo `familia_id`** na tabela `visitantes`: Liga o visitante à sua família

### 2. Interface Web
- **Nova página `/cadastrar-familia.html`**: Interface para cadastro de famílias
- **Navegação**: Links entre cadastro individual e familiar
- **Formulário dinâmico**: Adicionar/remover membros da família
- **Opções de parentesco**: 20+ tipos de relacionamento familiar

### 3. Backend
- **Função `cadastrarFamiliaBackend()`**: Processa cadastro de famílias
- **Validação**: Impede cadastro duplicado no mesmo dia
- **Relacionamentos**: Mantém vínculos familiares no banco

### 4. API Atualizada
- **Endpoint `/interno/familias`**: Para cadastro via interface web
- **API `/api/visitantes`** modificada: Retorna dados agrupados por família

## Como Usar

### Cadastro Individual (mantido)
- Acesse `/cadastrar.html`
- Funciona como antes

### Cadastro de Família (novo)
- Acesse `/cadastrar-familia.html`
- Defina nome da família (ex: "Família Silva")
- Adicione membros com nome, telefone e parentesco
- Sistema previne duplicatas e mantém relacionamentos

### API para Holyrics (atualizada)
```bash
# Retorna dados agrupados
GET /api/visitantes?data=2024-01-15&api_key=SUA_CHAVE

# Resposta:
{
  "familias": [
    {
      "nome_familia": "Família Silva",
      "membros": [
        {"nome": "João Silva", "parentesco": "esposo"},
        {"nome": "Maria Silva", "parentesco": "esposa"},
        {"nome": "Pedro Silva", "parentesco": "filho"}
      ]
    }
  ],
  "individuais": [
    {"nome": "Ana Santos"}
  ]
}
```

## Parentescos Disponíveis
- Esposo/Esposa
- Filho/Filha
- Pai/Mãe
- Avô/Avó
- Neto/Neta
- Sobrinho/Sobrinha
- Tio/Tia
- Primo/Prima
- Cunhado/Cunhada
- Genro/Nora
- Sogro/Sogra

## Exemplo de Uso
1. Família Silva chega à igreja
2. Recepcionista acessa `/cadastrar-familia.html`
3. Preenche:
   - Nome da família: "Família Silva"
   - João Silva (esposo)
   - Maria Silva (esposa)
   - Pedro Silva (filho, 12 anos)
   - Ana Silva (filha, 8 anos)
4. Sistema registra todos como uma unidade familiar
5. Holyrics recebe dados organizados por família

## Compatibilidade
- ✅ Mantém compatibilidade com cadastros individuais existentes
- ✅ API continua funcionando para sistemas externos
- ✅ Dados antigos permanecem intactos
- ✅ Deploy no Render.com sem mudanças adicionais

## Próximos Passos
- Testar em ambiente de produção
- Coletar feedback dos usuários
- Considerar relatórios por família
- Implementar busca por família