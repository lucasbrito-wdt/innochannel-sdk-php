#!/usr/bin/env pwsh
# Script para criar uma nova release do Innochannel SDK
# Uso: .\release.ps1 -Version "1.0.1" -Type "patch"

param(
    [Parameter(Mandatory=$true)]
    [string]$Version,
    
    [Parameter(Mandatory=$false)]
    [ValidateSet('major', 'minor', 'patch')]
    [string]$Type = 'patch',
    
    [Parameter(Mandatory=$false)]
    [string]$Message = ""
)

# Cores para output
function Write-Success { Write-Host $args -ForegroundColor Green }
function Write-Error { Write-Host $args -ForegroundColor Red }
function Write-Info { Write-Host $args -ForegroundColor Cyan }
function Write-Warning { Write-Host $args -ForegroundColor Yellow }

# Verificar se estamos em um repositório git
if (-not (Test-Path .git)) {
    Write-Error "❌ Este diretório não é um repositório git!"
    exit 1
}

# Validar formato da versão
if ($Version -notmatch '^\d+\.\d+\.\d+$') {
    Write-Error "❌ Formato de versão inválido! Use o formato: X.Y.Z (ex: 1.0.1)"
    exit 1
}

$TagName = "v$Version"

# Verificar se a tag já existe
$tagExists = git tag -l $TagName
if ($tagExists) {
    Write-Error "❌ A tag $TagName já existe!"
    Write-Info "Use 'git tag -d $TagName' para deletar localmente"
    Write-Info "Use 'git push origin --delete $TagName' para deletar remotamente"
    exit 1
}

# Verificar se há mudanças não commitadas
$status = git status --porcelain
if ($status) {
    Write-Warning "⚠️  Há mudanças não commitadas:"
    git status --short
    $continue = Read-Host "`nDeseja continuar mesmo assim? (s/N)"
    if ($continue -ne 's' -and $continue -ne 'S') {
        Write-Info "Operação cancelada."
        exit 0
    }
}

# Exibir informações da release
Write-Info "`n📦 Preparando release:"
Write-Info "  Versão: $Version"
Write-Info "  Tag: $TagName"
Write-Info "  Tipo: $Type"
Write-Info ""

# Confirmar
$confirm = Read-Host "Deseja continuar? (s/N)"
if ($confirm -ne 's' -and $confirm -ne 'S') {
    Write-Info "Operação cancelada."
    exit 0
}

# Mensagem padrão
if ([string]::IsNullOrWhiteSpace($Message)) {
    $Message = "Release $Version"
}

# Criar commit se necessário
if ($status) {
    Write-Info "`n📝 Criando commit..."
    git add .
    git commit -m "Preparando release $TagName"
    if ($LASTEXITCODE -ne 0) {
        Write-Error "❌ Falha ao criar commit!"
        exit 1
    }
    Write-Success "✅ Commit criado"
}

# Criar tag
Write-Info "`n🏷️  Criando tag $TagName..."
git tag -a $TagName -m "$Message"
if ($LASTEXITCODE -ne 0) {
    Write-Error "❌ Falha ao criar tag!"
    exit 1
}
Write-Success "✅ Tag criada"

# Push para origin
Write-Info "`n⬆️  Enviando para origin..."
git push origin master
if ($LASTEXITCODE -ne 0) {
    Write-Error "❌ Falha ao enviar commits!"
    exit 1
}
Write-Success "✅ Commits enviados"

# Push da tag
Write-Info "`n⬆️  Enviando tag..."
git push origin $TagName
if ($LASTEXITCODE -ne 0) {
    Write-Error "❌ Falha ao enviar tag!"
    Write-Warning "⚠️  A tag foi criada localmente. Use 'git push origin $TagName' para enviar manualmente."
    exit 1
}
Write-Success "✅ Tag enviada"

# Sucesso!
Write-Success "`n✨ Release $Version criada com sucesso!"
Write-Info "`n📋 Próximos passos:"
Write-Info "  1. Acesse: https://github.com/lucasbrito-wdt/innochannel-sdk-php/releases/new"
Write-Info "  2. Selecione a tag: $TagName"
Write-Info "  3. Adicione as notas de release do CHANGELOG.md"
Write-Info "  4. Publique a release"
Write-Info "`n📦 Packagist:"
Write-Info "  O Packagist detectará automaticamente a nova versão em alguns minutos"
Write-Info "  Ou atualize manualmente em: https://packagist.org/packages/lucasbrito-wdt/innochannel-sdk"
Write-Info ""
