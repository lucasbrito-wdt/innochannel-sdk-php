# 📚 Índice de Documentação - Sistema de Versionamento

Guia completo para navegar pela documentação do sistema de versionamento.

## 🚀 Início Rápido

**Quer criar uma versão agora?** → [QUICK_COMMANDS.md](QUICK_COMMANDS.md)

**Primeira vez?** → [VISUAL_GUIDE.md](VISUAL_GUIDE.md)

**Precisa de ajuda?** → [RELEASE.md](RELEASE.md)

## 📖 Documentação Completa

### Para Desenvolvedores

| Documento                              | Descrição                    | Quando Usar               |
| -------------------------------------- | ---------------------------- | ------------------------- |
| [QUICK_COMMANDS.md](QUICK_COMMANDS.md) | ⚡ Comandos rápidos          | Criar release rapidamente |
| [VISUAL_GUIDE.md](VISUAL_GUIDE.md)     | 🎯 Guia visual passo a passo | Primeira vez ou dúvidas   |
| [RELEASE.md](RELEASE.md)               | 📦 Guia de release           | Tutorial completo         |
| [VERSIONING.md](VERSIONING.md)         | 📚 Guia completo             | Referência detalhada      |
| [CHANGELOG.md](CHANGELOG.md)           | 📝 Histórico de mudanças     | Ver o que mudou           |

### Para Mantenedores

| Documento                                                  | Descrição              | Quando Usar              |
| ---------------------------------------------------------- | ---------------------- | ------------------------ |
| [VERSIONING_SUMMARY.md](VERSIONING_SUMMARY.md)             | 📦 Sumário do sistema  | Entender estrutura       |
| [.github/README.md](.github/README.md)                     | 🤖 Configuração GitHub | Configurar automações    |
| [.github/RELEASE_TEMPLATE.md](.github/RELEASE_TEMPLATE.md) | 📝 Template de release | Criar releases no GitHub |

## 🎯 Por Objetivo

### Quero criar uma release

1. **Rápido** → [QUICK_COMMANDS.md](QUICK_COMMANDS.md#-criar-nova-versão)
2. **Detalhado** → [VISUAL_GUIDE.md](VISUAL_GUIDE.md#-exemplo-prático)
3. **Completo** → [VERSIONING.md](VERSIONING.md#processo-de-release)

### Quero entender o processo

1. **Fluxograma** → [VISUAL_GUIDE.md](VISUAL_GUIDE.md#-fluxograma)
2. **Passo a passo** → [RELEASE.md](RELEASE.md)
3. **Teoria** → [VERSIONING.md](VERSIONING.md)

### Preciso de um comando específico

→ [QUICK_COMMANDS.md](QUICK_COMMANDS.md)

### Tenho um problema

1. **Troubleshooting** → [QUICK_COMMANDS.md](QUICK_COMMANDS.md#%EF%B8%8F-troubleshooting)
2. **Problemas Comuns** → [VISUAL_GUIDE.md](VISUAL_GUIDE.md#-problemas-comuns)
3. **Detalhado** → [VERSIONING.md](VERSIONING.md#checklist-pré-release)

## 📂 Estrutura de Arquivos

```
innochannel-sdk-php/
│
├── 📝 Documentação de Versionamento
│   ├── CHANGELOG.md              # Histórico de mudanças
│   ├── VERSIONING.md             # Guia completo
│   ├── RELEASE.md                # Guia de release
│   ├── QUICK_COMMANDS.md         # Comandos rápidos
│   ├── VISUAL_GUIDE.md           # Guia visual
│   ├── VERSIONING_SUMMARY.md     # Sumário
│   └── INDEX.md                  # Este arquivo
│
├── 🤖 Scripts de Automação
│   ├── release.ps1               # Script PowerShell (Windows)
│   └── release.sh                # Script Bash (Linux/Mac)
│
├── ⚙️ Configuração Git/GitHub
│   ├── .gitattributes            # Atributos do Git
│   └── .github/
│       ├── README.md             # Docs das configurações
│       ├── RELEASE_TEMPLATE.md   # Template de release
│       └── workflows/
│           └── release.yml       # GitHub Actions
│
└── 📚 Documentação Principal
    └── README.md                 # Documentação do SDK
```

## 🎓 Tutoriais por Nível

### 🌱 Iniciante

1. Leia: [VISUAL_GUIDE.md](VISUAL_GUIDE.md) - Entenda o fluxo
2. Pratique: [QUICK_COMMANDS.md](QUICK_COMMANDS.md) - Execute comandos
3. Consulte: [RELEASE.md](RELEASE.md) - Se tiver dúvidas

### 🌿 Intermediário

1. Domine: [QUICK_COMMANDS.md](QUICK_COMMANDS.md) - Todos os comandos
2. Entenda: [VERSIONING.md](VERSIONING.md) - Processo completo
3. Configure: [.github/README.md](.github/README.md) - Automações

### 🌳 Avançado

1. Otimize: [VERSIONING_SUMMARY.md](VERSIONING_SUMMARY.md) - Arquitetura
2. Customize: `.github/workflows/release.yml` - Workflows
3. Automatize: Scripts personalizados

## 📋 Checklists

### Antes da Primeira Release

- [ ] Ler [VISUAL_GUIDE.md](VISUAL_GUIDE.md)
- [ ] Configurar scripts (`chmod +x release.sh` ou permissões PS)
- [ ] Atualizar [CHANGELOG.md](CHANGELOG.md)
- [ ] Testar código
- [ ] Executar script de release

### Antes de Cada Release

- [ ] Testes passando
- [ ] [CHANGELOG.md](CHANGELOG.md) atualizado
- [ ] Documentação atualizada
- [ ] Versão correta escolhida
- [ ] Executar script
- [ ] Publicar no GitHub
- [ ] Verificar Packagist

## 🔗 Links Úteis

### Internos

- [README principal](README.md)
- [Guia de Integração](INTEGRATION_GUIDE.md)
- [Guia de Publicação](PUBLISHING_GUIDE.md)

### Externos

- [Semantic Versioning](https://semver.org/lang/pt-BR/)
- [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/)
- [GitHub Releases](https://docs.github.com/en/repositories/releasing-projects-on-github)
- [Packagist](https://packagist.org/packages/lucasbrito-wdt/innochannel-sdk)

## 🆘 Obter Ajuda

### Por Tipo de Problema

| Problema                   | Onde Procurar                                                                            |
| -------------------------- | ---------------------------------------------------------------------------------------- |
| Comando não funciona       | [QUICK_COMMANDS.md → Troubleshooting](QUICK_COMMANDS.md#%EF%B8%8F-troubleshooting)       |
| Não entendo o processo     | [VISUAL_GUIDE.md](VISUAL_GUIDE.md)                                                       |
| GitHub Actions falhou      | [.github/README.md → Troubleshooting](.github/README.md#-troubleshooting)                |
| Packagist não atualizou    | [VERSIONING.md → Packagist](VERSIONING.md#5-publicar-no-packagist)                       |
| Tag já existe              | [QUICK_COMMANDS.md → Deletar Tag](QUICK_COMMANDS.md#%EF%B8%8F-deletar-tag-se-necessário) |
| Dúvida sobre versionamento | [VERSIONING.md](VERSIONING.md)                                                           |

### Fluxo de Ajuda

```
Problema
   ↓
QUICK_COMMANDS.md (troubleshooting)
   ↓ (não resolveu)
VISUAL_GUIDE.md (problemas comuns)
   ↓ (não resolveu)
VERSIONING.md (guia completo)
   ↓ (não resolveu)
GitHub Issues
```

## 📊 Resumo dos Arquivos

| Arquivo               | Tamanho | Complexidade    | Público       |
| --------------------- | ------- | --------------- | ------------- |
| QUICK_COMMANDS.md     | Curto   | ⭐ Fácil        | Todos         |
| VISUAL_GUIDE.md       | Médio   | ⭐⭐ Médio      | Iniciantes    |
| RELEASE.md            | Médio   | ⭐⭐ Médio      | Intermediário |
| VERSIONING.md         | Longo   | ⭐⭐⭐ Avançado | Todos         |
| VERSIONING_SUMMARY.md | Curto   | ⭐⭐⭐ Avançado | Mantenedores  |
| .github/README.md     | Curto   | ⭐⭐⭐ Avançado | DevOps        |

## 🎯 Recomendações

### Para Desenvolvedores Novos

**Comece com:** [VISUAL_GUIDE.md](VISUAL_GUIDE.md)

### Para Criar Release Rápido

**Use:** [QUICK_COMMANDS.md](QUICK_COMMANDS.md)

### Para Entender Tudo

**Leia:** [VERSIONING.md](VERSIONING.md)

### Para Configurar Automação

**Consulte:** [.github/README.md](.github/README.md)

## ✨ Status do Sistema

- ✅ Documentação completa
- ✅ Scripts automatizados
- ✅ GitHub Actions configurado
- ✅ Templates criados
- ✅ Guias visuais
- ✅ Índice de navegação

---

**📚 Toda documentação está interligada e pronta para uso!**

**🚀 Próximo passo:** [Criar v1.0.0](QUICK_COMMANDS.md#-criar-nova-versão)
