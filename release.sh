#!/bin/bash
# Script para criar uma nova release do Innochannel SDK
# Uso: ./release.sh 1.0.1 "Mensagem opcional"

set -e

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Funções de output
success() { echo -e "${GREEN}$1${NC}"; }
error() { echo -e "${RED}$1${NC}"; }
info() { echo -e "${CYAN}$1${NC}"; }
warning() { echo -e "${YELLOW}$1${NC}"; }

# Verificar argumentos
if [ -z "$1" ]; then
    error "❌ Versão não especificada!"
    info "Uso: ./release.sh <versão> [mensagem]"
    info "Exemplo: ./release.sh 1.0.1"
    exit 1
fi

VERSION=$1
MESSAGE=${2:-"Release $VERSION"}
TAG_NAME="v$VERSION"

# Verificar se estamos em um repositório git
if [ ! -d .git ]; then
    error "❌ Este diretório não é um repositório git!"
    exit 1
fi

# Validar formato da versão
if ! [[ $VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    error "❌ Formato de versão inválido! Use o formato: X.Y.Z (ex: 1.0.1)"
    exit 1
fi

# Verificar se a tag já existe
if git rev-parse "$TAG_NAME" >/dev/null 2>&1; then
    error "❌ A tag $TAG_NAME já existe!"
    info "Use 'git tag -d $TAG_NAME' para deletar localmente"
    info "Use 'git push origin --delete $TAG_NAME' para deletar remotamente"
    exit 1
fi

# Verificar se há mudanças não commitadas
if [ -n "$(git status --porcelain)" ]; then
    warning "⚠️  Há mudanças não commitadas:"
    git status --short
    echo
    read -p "Deseja continuar mesmo assim? (s/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Ss]$ ]]; then
        info "Operação cancelada."
        exit 0
    fi
fi

# Exibir informações da release
info "\n📦 Preparando release:"
info "  Versão: $VERSION"
info "  Tag: $TAG_NAME"
info "  Mensagem: $MESSAGE"
echo

# Confirmar
read -p "Deseja continuar? (s/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Ss]$ ]]; then
    info "Operação cancelada."
    exit 0
fi

# Criar commit se necessário
if [ -n "$(git status --porcelain)" ]; then
    info "\n📝 Criando commit..."
    git add .
    git commit -m "Preparando release $TAG_NAME"
    success "✅ Commit criado"
fi

# Criar tag
info "\n🏷️  Criando tag $TAG_NAME..."
git tag -a "$TAG_NAME" -m "$MESSAGE"
success "✅ Tag criada"

# Push para origin
info "\n⬆️  Enviando para origin..."
git push origin master || git push origin main
success "✅ Commits enviados"

# Push da tag
info "\n⬆️  Enviando tag..."
git push origin "$TAG_NAME"
success "✅ Tag enviada"

# Sucesso!
success "\n✨ Release $VERSION criada com sucesso!"
info "\n📋 Próximos passos:"
info "  1. Acesse: https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases/new"
info "  2. Selecione a tag: $TAG_NAME"
info "  3. Adicione as notas de release do CHANGELOG.md"
info "  4. Publique a release"
info "\n📦 Packagist:"
info "  O Packagist detectará automaticamente a nova versão em alguns minutos"
info "  Ou atualize manualmente em: https://packagist.org/packages/lucasbrito-wdt/innochannel-sdk"
echo
