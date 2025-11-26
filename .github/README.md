# GitHub Configuration

Este diretório contém configurações e automações para o GitHub.

## 📁 Estrutura

```
.github/
├── workflows/
│   └── release.yml          # Automação de releases
└── RELEASE_TEMPLATE.md      # Template para releases
```

## 🤖 Workflows

### release.yml

Automação que é executada quando uma nova tag é criada.

**Trigger**: Push de tag `v*.*.*`

**Ações**:

1. ✅ Extrai versão da tag
2. ✅ Extrai notas do CHANGELOG.md
3. ✅ Cria release no GitHub automaticamente
4. ✅ Prepara notificação ao Packagist

**Uso**:

```bash
# Criar tag (manualmente ou via script)
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0

# GitHub Actions cria o release automaticamente
```

## 📝 Templates

### RELEASE_TEMPLATE.md

Template para criar releases consistentes no GitHub.

**Uso**:

1. Acesse: https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases/new
2. Selecione a tag
3. Copie o template
4. Preencha com as informações do CHANGELOG.md
5. Publique

## 🔧 Configuração

### Permissões Necessárias

O workflow `release.yml` requer:

- ✅ `contents: write` - Para criar releases

Essas permissões já estão configuradas no workflow.

### Secrets

Nenhum secret é necessário. O workflow usa `GITHUB_TOKEN` automaticamente.

### Packagist (Opcional)

Para notificar o Packagist automaticamente:

1. Obtenha seu API Token do Packagist
2. Adicione como secret no GitHub:
   - Nome: `PACKAGIST_TOKEN`
3. Descomente as linhas no `release.yml`:
   ```yaml
   - name: Notify Packagist
     run: |
       curl -XPOST -H'content-type:application/json' \
       'https://packagist.org/api/update-package?username=USERNAME&apiToken=${{ secrets.PACKAGIST_TOKEN }}' \
       -d'{"repository":{"url":"https://github.com/lucasbrito-wdt/innochannel-sdk-php"}}'
   ```

## 📊 Status

- ✅ Workflow de release configurado
- ✅ Template de release criado
- ⚠️ Packagist auto-update (opcional)

## 🆘 Troubleshooting

### Workflow não executa

Verifique:

1. Tag está no formato `v*.*.*` (ex: `v1.0.0`)
2. Permissões do repositório
3. Logs em: https://github.com/lucasbrito-wdt/innochannel-sdk-php/actions

### Release não é criada

Verifique:

1. CHANGELOG.md tem seção para a versão
2. Permissão `contents: write` está configurada
3. Token tem acesso ao repositório

## 📚 Mais Informações

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Creating Releases](https://docs.github.com/en/repositories/releasing-projects-on-github/managing-releases-in-a-repository)
- [Workflow Syntax](https://docs.github.com/en/actions/using-workflows/workflow-syntax-for-github-actions)
