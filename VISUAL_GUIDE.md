# 🎯 Guia Visual de Release

Este guia visual mostra passo a passo como criar uma release.

## 📋 Fluxograma

```
┌─────────────────────────────────────────────┐
│  1. Desenvolver e Testar Código             │
│     - Implementar features/fixes            │
│     - Executar testes                       │
│     - Commit & Push                         │
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│  2. Atualizar CHANGELOG.md                  │
│     - Documentar mudanças                   │
│     - Seguir formato Keep a Changelog       │
│     - Adicionar link da versão              │
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│  3. Escolher Tipo de Versão                 │
│     - PATCH (1.0.X): Bug fixes             │
│     - MINOR (1.X.0): Novas features        │
│     - MAJOR (X.0.0): Breaking changes      │
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│  4. Executar Script de Release              │
│     Windows: .\release.ps1 -Version "X.Y.Z" │
│     Linux/Mac: ./release.sh X.Y.Z           │
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│  5. Script Executa Automaticamente:         │
│     ✅ Valida versão                        │
│     ✅ Verifica se tag existe               │
│     ✅ Cria commit (se necessário)          │
│     ✅ Cria tag anotada                     │
│     ✅ Faz push para GitHub                 │
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│  6. GitHub Actions (Automático)             │
│     ✅ Detecta nova tag                     │
│     ✅ Extrai notas do CHANGELOG            │
│     ✅ Cria release no GitHub               │
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│  7. Publicar Release no GitHub (Manual)     │
│     - Acessar GitHub Releases               │
│     - Revisar informações                   │
│     - Adicionar detalhes extras             │
│     - Clicar em "Publish release"           │
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│  8. Packagist Atualiza (Automático)         │
│     ⏱️  Aguardar ~5 minutos                 │
│     ✅ Nova versão disponível               │
└──────────────────┬──────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────┐
│  9. Usuários Podem Instalar                 │
│     composer require lucasbrito-wdt/        │
│       innochannel-sdk:^X.Y.Z                │
└─────────────────────────────────────────────┘
```

## 🎬 Exemplo Prático

### Cenário: Criar v1.0.0

#### 1️⃣ Preparar CHANGELOG.md

```markdown
## [1.0.0] - 2025-11-26

### Adicionado

- Cliente SDK completo
- Suporte para webhooks
- Integração com Laravel

### Corrigido

- Tratamento de exceções no Client

[1.0.0]: https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases/tag/v1.0.0
```

#### 2️⃣ Executar Script

**Windows (PowerShell):**

```powershell
.\release.ps1 -Version "1.0.0"
```

**Saída Esperada:**

```
📦 Preparando release:
  Versão: 1.0.0
  Tag: v1.0.0
  Tipo: patch

Deseja continuar? (s/N) s

📝 Criando commit...
✅ Commit criado

🏷️  Criando tag v1.0.0...
✅ Tag criada

⬆️  Enviando para origin...
✅ Commits enviados

⬆️  Enviando tag...
✅ Tag enviada

✨ Release 1.0.0 criada com sucesso!

📋 Próximos passos:
  1. Acesse: https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases/new
  2. Selecione a tag: v1.0.0
  3. Adicione as notas de release do CHANGELOG.md
  4. Publique a release
```

#### 3️⃣ GitHub Actions (Automático)

O GitHub Actions irá:

- ✅ Detectar a nova tag `v1.0.0`
- ✅ Extrair as notas do CHANGELOG.md
- ✅ Criar um rascunho de release

#### 4️⃣ Publicar no GitHub

1. Acesse: https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases
2. Encontre o rascunho `v1.0.0`
3. Revise e adicione informações extras
4. Clique em **"Publish release"**

#### 5️⃣ Verificar no Packagist

Aguarde ~5 minutos e verifique:
https://packagist.org/packages/lucasbrito-wdt/innochannel-sdk

## 📊 Comparação de Versões

```
v1.0.0  →  v1.0.1  (PATCH - Bug fixes)
v1.0.0  →  v1.1.0  (MINOR - Novas features)
v1.0.0  →  v2.0.0  (MAJOR - Breaking changes)
```

### Quando usar cada tipo?

| Tipo  | Quando Usar                 | Exemplo         |
| ----- | --------------------------- | --------------- |
| PATCH | Correção de bugs            | v1.0.0 → v1.0.1 |
| MINOR | Novas features (compatível) | v1.0.0 → v1.1.0 |
| MAJOR | Breaking changes            | v1.9.0 → v2.0.0 |

## 🔍 Verificar Versões

### Listar Todas as Tags

```bash
git tag -l
```

**Saída Esperada:**

```
v1.0.0
v1.0.1
v1.1.0
```

### Ver Última Versão

```bash
git describe --tags --abbrev=0
```

**Saída Esperada:**

```
v1.1.0
```

## 🎨 Badges no README

Após criar a primeira release, adicione badges:

```markdown
[![Latest Version](https://img.shields.io/github/v/release/lucasbrito-wdt/innochannel-sdk-php?label=version)](https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases)
```

Resultado:

> ![Version Badge](https://img.shields.io/badge/version-v1.0.0-blue)

## 📝 Checklist de Release

Use esta checklist antes de cada release:

- [ ] Todos os testes passam
- [ ] CHANGELOG.md atualizado
- [ ] Documentação atualizada (se necessário)
- [ ] Versão correta escolhida (PATCH/MINOR/MAJOR)
- [ ] Sem mudanças pendentes críticas
- [ ] README atualizado (se necessário)
- [ ] Executar script de release
- [ ] Verificar GitHub Actions
- [ ] Publicar release no GitHub
- [ ] Aguardar Packagist (5 min)
- [ ] Testar instalação: `composer require lucasbrito-wdt/innochannel-sdk:^X.Y.Z`

## 🆘 Problemas Comuns

### ❌ Tag já existe

**Solução:**

```bash
git tag -d v1.0.0
git push origin --delete v1.0.0
```

### ❌ Erro de permissão

**Windows:**

```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope Process
```

**Linux/Mac:**

```bash
chmod +x release.sh
```

### ❌ GitHub Actions não executou

**Verificar:**

1. Tag está no formato `v*.*.*`
2. Workflow está em `.github/workflows/release.yml`
3. Logs em: https://github.com/lucasbrito-wdt/innochannel-sdk-php/actions

## 📚 Recursos

- [QUICK_COMMANDS.md](QUICK_COMMANDS.md) - Comandos rápidos
- [VERSIONING.md](VERSIONING.md) - Guia completo
- [RELEASE.md](RELEASE.md) - Guia detalhado
- [CHANGELOG.md](CHANGELOG.md) - Histórico de mudanças

---

**✨ Pronto para criar sua primeira release!**
