# Innochannel SDK Laravel Integration

## ValidationException Handling

Este pacote Laravel fornece tratamento automático para `ValidationException` do Innochannel SDK, retornando respostas HTTP 422 com detalhes dos campos inválidos.

### Funcionalidades

1. **Exception Handler Customizado**: Intercepta automaticamente `ValidationException` do SDK
2. **Trait para Controllers**: Facilita o tratamento manual de exceções
3. **Controller de Exemplo**: Demonstra o uso correto do SDK com tratamento de erros
4. **Rotas de API**: Endpoints prontos para testar o tratamento de validação

### Como Usar

#### 1. Exception Handler Automático

O `InnochannelServiceProvider` registra automaticamente um exception handler que intercepta `ValidationException` do SDK:

```php
// Automaticamente retorna HTTP 422 com:
{
    "message": "Property validation failed. Errors: property_name: Property name is required",
    "errors": {
        "property_name": ["Property name is required"]
    },
    "formatted_errors": [
        "property_name: Property name is required"
    ]
}
```

#### 2. Trait HandlesInnochannelExceptions

Use o trait em seus controllers para tratamento manual:

```php
use Innochannel\Laravel\Traits\HandlesInnochannelExceptions;

class MyController extends Controller
{
    use HandlesInnochannelExceptions;

    public function store(Request $request)
    {
        try {
            $property = $this->propertyService->create($request->all());
            return response()->json($property, 201);
        } catch (\Throwable $exception) {
            $response = $this->handleInnochannelException($exception);
            if ($response) {
                return $response;
            }
            throw $exception;
        }
    }
}
```

#### 3. Controller de Exemplo

O `PropertyController` demonstra o uso completo:

```php
// POST /innochannel/properties
// PUT /innochannel/properties/{id}
// POST /innochannel/properties/{id}/rooms
// PUT /innochannel/properties/{id}/rooms/{roomId}
// POST /innochannel/properties/{id}/rooms/{roomId}/rate-plans
```

### Tipos de Exceções Tratadas

- **ValidationException**: HTTP 422 com detalhes dos campos
- **AuthenticationException**: HTTP 401
- **NotFoundException**: HTTP 404
- **RateLimitException**: HTTP 429
- **ApiException**: HTTP status code da exceção

### Estrutura da Resposta de Validação

```json
{
    "message": "Mensagem principal do erro",
    "errors": {
        "campo1": ["Erro 1", "Erro 2"],
        "campo2": ["Erro 3"]
    },
    "context": {
        "informações_adicionais": "valor"
    },
    "formatted_errors": [
        "campo1: Erro 1, Erro 2",
        "campo2: Erro 3"
    ]
}
```

### Testando

Use as rotas de API para testar o tratamento de validação:

```bash
# Teste com dados inválidos
curl -X POST http://localhost:8000/api/innochannel/properties \
  -H "Content-Type: application/json" \
  -d '{}'

# Resposta esperada: HTTP 422 com detalhes dos erros
```

### Instalação

O tratamento de exceções é ativado automaticamente quando o `InnochannelServiceProvider` é registrado no Laravel.