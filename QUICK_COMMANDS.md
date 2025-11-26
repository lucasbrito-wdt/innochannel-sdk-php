# ⚡ Comandos Rápidos para Releases

## 🚀 Criar Nova Versão

### Windows (PowerShell)

```powershell
# Primeira vez - dar permissão
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope Process

# Criar v1.0.0
.\release.ps1 -Version "1.0.0"

# Criar v1.0.1 (patch)
.\release.ps1 -Version "1.0.1"

# Criar v1.1.0 (minor)
.\release.ps1 -Version "1.1.0"

# Criar v2.0.0 (major)
.\release.ps1 -Version "2.0.0"
```

### Linux/Mac (Bash)

```bash
# Primeira vez - dar permissão
chmod +x release.sh

# Criar v1.0.0
./release.sh 1.0.0

# Criar v1.0.1 (patch)
./release.sh 1.0.1

# Criar v1.1.0 (minor)
./release.sh 1.1.0

# Criar v2.0.0 (major)
./release.sh 2.0.0
```

## 📝 Atualizar CHANGELOG

Antes de criar release, edite `CHANGELOG.md`:

```markdown
## [1.0.1] - 2025-11-26

### Corrigido

- Descrição do bug corrigido

### Adicionado

- Descrição de nova feature

[1.0.1]: https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases/tag/v1.0.1
```

## 🔍 Verificar Versões

```bash
# Listar todas as tags
git tag -l

# Ver última tag
git describe --tags --abbrev=0

# Ver detalhes de uma tag
git show v1.0.0
```

## 🗑️ Deletar Tag (se necessário)

```bash
# Deletar localmente
git tag -d v1.0.0

# Deletar remotamente
git push origin --delete v1.0.0
```

## 📦 Após a Release

### GitHub

1. Acesse: https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases/new
2. Selecione a tag
3. Cole o conteúdo do CHANGELOG
4. Publique

### Packagist

Aguarde ~5 minutos ou atualize manualmente:
https://packagist.org/packages/lucasbrito-wdt/innochannel-sdk

## 🧪 Testar Instalação

```bash
# Em outro projeto
composer require lucasbrito-wdt/innochannel-sdk:^1.0.0

# Ou versão específica
composer require lucasbrito-wdt/innochannel-sdk:1.0.0
```

## 📊 Badges para README

```markdown
[![Latest Version](https://img.shields.io/github/v/release/lucasbrito-wdt/innochannel-sdk-php)](https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/lucasbrito-wdt/innochannel-sdk)](https://packagist.org/packages/lucasbrito-wdt/innochannel-sdk)
```

## 🔄 Workflow Completo

```bash
# 1. Fazer alterações no código
git add .
git commit -m "feat: nova funcionalidade"
git push

# 2. Atualizar CHANGELOG.md
# (editar arquivo)

# 3. Criar release
.\release.ps1 -Version "1.1.0"  # Windows
# ou
./release.sh 1.1.0              # Linux/Mac

# 4. GitHub Actions cria release automaticamente

# 5. Verificar Packagist após 5 minutos
```

## ⚠️ Troubleshooting

### Erro de permissão (Windows)

```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope Process
```

### Erro de permissão (Linux/Mac)

```bash
chmod +x release.sh
```

### Tag já existe

```bash
git tag -d v1.0.0
git push origin --delete v1.0.0
```

### Mudanças não commitadas

```bash
# Commit antes de criar release
git add .
git commit -m "Preparando release"
```

## 📚 Documentação

- [VERSIONING.md](VERSIONING.md) - Guia completo
- [RELEASE.md](RELEASE.md) - Guia detalhado
- [CHANGELOG.md](CHANGELOG.md) - Histórico
- [README.md](README.md) - Documentação principal
