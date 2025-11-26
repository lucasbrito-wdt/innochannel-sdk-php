# Guia de Versionamento

Este guia explica como criar e publicar novas versões do Innochannel SDK.

## Versionamento Semântico

Este projeto segue o [Semantic Versioning 2.0.0](https://semver.org/lang/pt-BR/):

- **MAJOR** (X.0.0): Mudanças incompatíveis com versões anteriores
- **MINOR** (1.X.0): Novas funcionalidades compatíveis com versões anteriores
- **PATCH** (1.0.X): Correções de bugs compatíveis com versões anteriores

## Processo de Release

### 1. Atualizar o CHANGELOG.md

Edite o arquivo `CHANGELOG.md` e mova as mudanças de `[Unreleased]` para a nova versão:

```markdown
## [1.0.1] - 2025-11-26

### Corrigido

- Descrição da correção

[1.0.1]: https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases/tag/v1.0.1
```

### 2. Commit das Mudanças

```bash
git add CHANGELOG.md
git commit -m "Preparando release v1.0.1"
git push origin master
```

### 3. Criar a Tag de Versão

```bash
# Criar tag anotada
git tag -a v1.0.1 -m "Release v1.0.1"

# Enviar tag para o repositório
git push origin v1.0.1
```

### 4. Criar Release no GitHub

1. Acesse: https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases/new
2. Selecione a tag criada (v1.0.1)
3. Título: `v1.0.1`
4. Descrição: Copie as mudanças do CHANGELOG.md
5. Clique em "Publish release"

### 5. Publicar no Packagist

O Packagist detectará automaticamente a nova tag se o repositório estiver configurado.

Se necessário, você pode atualizar manualmente em:
https://packagist.org/packages/lucasbrito-wdt/innochannel-sdk

## Comandos Rápidos

### Criar Versão Patch (1.0.X)

```bash
# Atualizar CHANGELOG.md primeiro
git add CHANGELOG.md
git commit -m "Preparando release v1.0.1"
git tag -a v1.0.1 -m "Release v1.0.1"
git push origin master
git push origin v1.0.1
```

### Criar Versão Minor (1.X.0)

```bash
# Atualizar CHANGELOG.md primeiro
git add CHANGELOG.md
git commit -m "Preparando release v1.1.0"
git tag -a v1.1.0 -m "Release v1.1.0"
git push origin master
git push origin v1.1.0
```

### Criar Versão Major (X.0.0)

```bash
# Atualizar CHANGELOG.md primeiro
git add CHANGELOG.md
git commit -m "Preparando release v2.0.0"
git tag -a v2.0.0 -m "Release v2.0.0"
git push origin master
git push origin v2.0.0
```

## Verificar Versões

### Listar todas as tags

```bash
git tag -l
```

### Ver detalhes de uma tag

```bash
git show v1.0.1
```

### Deletar tag (se necessário)

```bash
# Local
git tag -d v1.0.1

# Remoto
git push origin --delete v1.0.1
```

## Checklist Pré-Release

Antes de criar uma nova versão, verifique:

- [ ] Todos os testes estão passando
- [ ] CHANGELOG.md está atualizado
- [ ] Documentação está atualizada
- [ ] Não há commits pendentes
- [ ] Versão está correta no CHANGELOG.md
- [ ] Todos os arquivos importantes estão commitados

## Versão Atual

**v1.0.0** - Release inicial com correção de bugs críticos

## Próximas Versões

### v1.1.0 (Planejado)

- Suporte para mais tipos de PMS
- Melhorias no sistema de cache

### v1.2.0 (Planejado)

- Suporte para webhooks assíncronos
- Dashboard de monitoramento

### v2.0.0 (Futuro)

- Breaking changes planejados para melhorias de arquitetura
