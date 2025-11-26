# 🚀 Como Criar uma Nova Versão

Este guia rápido explica como criar e publicar uma nova versão do Innochannel SDK.

## Método 1: Usando o Script Automatizado (Recomendado)

### Windows (PowerShell)

```powershell
# Dar permissão de execução (primeira vez)
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope Process

# Criar release
.\release.ps1 -Version "1.0.1"

# Ou com mensagem personalizada
.\release.ps1 -Version "1.0.1" -Message "Correção de bugs críticos"
```

### Linux/Mac (Bash)

```bash
# Dar permissão de execução (primeira vez)
chmod +x release.sh

# Criar release
./release.sh 1.0.1

# Ou com mensagem personalizada
./release.sh 1.0.1 "Correção de bugs críticos"
```

O script irá:

1. ✅ Validar a versão
2. ✅ Verificar se a tag já existe
3. ✅ Criar commit das mudanças pendentes
4. ✅ Criar a tag anotada
5. ✅ Fazer push para o GitHub
6. ✅ Exibir próximos passos

## Método 2: Manual

### 1. Atualizar CHANGELOG.md

```markdown
## [1.0.1] - 2025-11-26

### Corrigido

- Tratamento de exceções no Client

[1.0.1]: https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases/tag/v1.0.1
```

### 2. Criar Tag

```bash
# Commit
git add CHANGELOG.md
git commit -m "Preparando release v1.0.1"

# Criar tag
git tag -a v1.0.1 -m "Release v1.0.1"

# Push
git push origin master
git push origin v1.0.1
```

### 3. Criar Release no GitHub

1. Acesse: https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases/new
2. Selecione a tag `v1.0.1`
3. Adicione as notas do CHANGELOG.md
4. Clique em "Publish release"

## Versionamento Semântico

- **PATCH** (1.0.X): Correções de bugs → `./release.sh 1.0.1`
- **MINOR** (1.X.0): Novas funcionalidades → `./release.sh 1.1.0`
- **MAJOR** (X.0.0): Breaking changes → `./release.sh 2.0.0`

## Checklist Pré-Release

Antes de criar uma release, verifique:

- [ ] Todos os testes passando
- [ ] CHANGELOG.md atualizado
- [ ] Documentação atualizada
- [ ] Sem commits pendentes críticos
- [ ] Versão correta escolhida

## Após a Release

O Packagist detectará automaticamente a nova versão em alguns minutos.

Usuários poderão instalar com:

```bash
composer require lucasbrito-wdt/innochannel-sdk:^1.0.1
```

## Problemas Comuns

### Tag já existe

```bash
# Deletar localmente
git tag -d v1.0.1

# Deletar remotamente
git push origin --delete v1.0.1
```

### Erro de permissão no script

```powershell
# Windows
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope Process
```

```bash
# Linux/Mac
chmod +x release.sh
```

## Suporte

- 📚 Documentação: [VERSIONING.md](VERSIONING.md)
- 📝 Changelog: [CHANGELOG.md](CHANGELOG.md)
- 🐛 Issues: https://github.com/lucasbrito-wdt/innochannel-sdk-php/issues
