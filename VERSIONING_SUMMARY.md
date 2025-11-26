# 📦 Sistema de Versionamento - Sumário

Este documento resume todos os arquivos criados para o sistema de versionamento do Innochannel SDK.

## 📁 Arquivos Criados

### Documentação

1. **CHANGELOG.md** - Histórico de mudanças do projeto

   - Formato: Keep a Changelog
   - Documenta todas as versões e suas mudanças

2. **VERSIONING.md** - Guia completo de versionamento

   - Explica Semantic Versioning
   - Processo detalhado de release
   - Comandos e checklist

3. **RELEASE.md** - Guia rápido de release
   - Como usar os scripts
   - Comandos rápidos
   - Troubleshooting

### Scripts de Automação

4. **release.ps1** - Script PowerShell (Windows)

   - Automação completa do processo de release
   - Validações e segurança
   - Interface amigável

5. **release.sh** - Script Bash (Linux/Mac)
   - Mesmas funcionalidades do PowerShell
   - Compatível com Unix

### Configuração Git

6. **.gitattributes** - Atributos do Git
   - Normalização de arquivos
   - Exclusões de export
   - Otimização de releases

### GitHub

7. **.github/RELEASE_TEMPLATE.md** - Template para releases

   - Formato consistente
   - Seções predefinidas

8. **.github/workflows/release.yml** - GitHub Actions
   - Automação de releases no GitHub
   - Extração automática do CHANGELOG
   - Notificação ao Packagist

### Atualizações

9. **README.md** (atualizado)

   - Badges de versão
   - Seção de versionamento
   - Links para documentação

10. **src/Client.php** (corrigido)
    - Bug fix no tratamento de exceções
    - Preparação para v1.0.0

## 🚀 Como Usar

### Opção 1: Script Automatizado (Recomendado)

#### Windows

```powershell
.\release.ps1 -Version "1.0.0"
```

#### Linux/Mac

```bash
./release.sh 1.0.0
```

### Opção 2: Manual

```bash
# 1. Atualizar CHANGELOG.md
# 2. Commit
git add .
git commit -m "Preparando release v1.0.0"

# 3. Criar tag
git tag -a v1.0.0 -m "Release v1.0.0"

# 4. Push
git push origin master
git push origin v1.0.0

# 5. Criar release no GitHub
# Acesse: https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases/new
```

## 📋 Próximos Passos para Criar v1.0.0

1. **Revisar CHANGELOG.md**

   - ✅ Já está criado com v1.0.0
   - Adicione mais detalhes se necessário

2. **Executar o script de release**

   ```powershell
   # Windows
   .\release.ps1 -Version "1.0.0" -Message "Primeira versão estável"
   ```

3. **Criar release no GitHub**

   - Acesse: https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases/new
   - Selecione a tag v1.0.0
   - Use o template em `.github/RELEASE_TEMPLATE.md`
   - Publique

4. **Verificar Packagist**
   - Aguarde alguns minutos
   - Verifique: https://packagist.org/packages/lucasbrito-wdt/innochannel-sdk

## 🔄 Fluxo de Trabalho

```
Desenvolvimento
    ↓
Commit & Push
    ↓
Atualizar CHANGELOG.md
    ↓
Executar script de release
    ↓
Tag criada e enviada
    ↓
GitHub Actions cria release automaticamente
    ↓
Packagist atualiza em ~5 minutos
    ↓
Usuários podem instalar nova versão
```

## 📝 Versionamento Semântico

- **MAJOR** (X.0.0): Breaking changes
  - Exemplo: `./release.sh 2.0.0`
- **MINOR** (1.X.0): Novas features (compatível)
  - Exemplo: `./release.sh 1.1.0`
- **PATCH** (1.0.X): Bug fixes
  - Exemplo: `./release.sh 1.0.1`

## 🛡️ Validações Automáticas

Os scripts validam:

- ✅ Formato da versão (X.Y.Z)
- ✅ Tag não existe
- ✅ Repositório Git válido
- ✅ Confirmação do usuário

## 📚 Documentação Relacionada

- [CHANGELOG.md](CHANGELOG.md) - Histórico de mudanças
- [VERSIONING.md](VERSIONING.md) - Guia completo
- [RELEASE.md](RELEASE.md) - Guia rápido
- [README.md](README.md) - Documentação principal

## 🆘 Suporte

Se tiver problemas:

1. Consulte [VERSIONING.md](VERSIONING.md) para guia detalhado
2. Consulte [RELEASE.md](RELEASE.md) para troubleshooting
3. Abra uma issue: https://github.com/lucasbrito-wdt/innochannel-sdk-php/issues

## ✨ Recursos

- ✅ Scripts de automação multiplataforma
- ✅ GitHub Actions para releases automáticas
- ✅ Template de release consistente
- ✅ Documentação completa
- ✅ Badges no README
- ✅ CHANGELOG estruturado
- ✅ Validações de segurança
- ✅ Suporte para Packagist

---

**Status**: Pronto para criar v1.0.0! 🚀
