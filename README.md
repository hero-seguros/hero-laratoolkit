# HeroLaraToolkit

Biblioteca compartilhada usada pelos serviços Laravel da Hero Seguros. Fornece:

- **Geradores de código `make:` opinativos**, que organizam arquivos por domínio e já injetam as convenções da arquitetura padrão (Controllers com `ApiControllerTrait`, Services com `execute()`, Repositories com interface + bind, etc.).
- **Validadores brasileiros** auto-registrados (`cpf`, `cnpj`, `phone`, `cellphone`, `cep`, `passport`).
- **Helpers** de formatação (`FormatHelper`) e validação (`ValidatorHelper`).
- **`AbstractRepository`**, **`ApiControllerTrait`** e **`BusinessException`** como base do padrão de serviço/controller.

## Instalação

```bash
composer require hero-seguros/hero-laratoolkit
```

## Requisitos

- **PHP**: >= 8.0
- **Laravel** (runtime, declarado em `composer.json`): 8.x, 9.x, 10.x, 11.x ou 12.x

### Cobertura de CI

O workflow `tests.yml` roda a suíte Pest contra **Laravel 11 (Testbench 9) e Laravel 12 (Testbench 10)**, em PHP 8.2, 8.3 e 8.4. Laravel 8/9/10 continuam declarados como compatíveis no `composer.json` mas **não são exercitados em CI** — o ecossistema bloqueia a instalação combinada de Testbench 8 + PHPUnit-compatível-com-Pest-3 via security advisories (Laravel 10 já está fora do suporte de segurança oficial desde fev/2025). Se você usa o pacote em um serviço Laravel 8-10, os testes precisam ser rodados manualmente no ambiente do serviço.

## Comandos `make:` — visão geral

Todos os overrides preservam os flags nativos do Laravel (`--api`, `--resource`, `--model=`, `--sync`, etc.). O `--domain=` é **opcional** em quase tudo — quando omitido, o comando se comporta exatamente como o nativo.

### Overrides de comandos nativos

| Comando | Resultado com `--domain=Foo` | Resultado sem `--domain` |
|---|---|---|
| `make:controller` | `app/Http/Controllers/Foo/{Name}.php` (com `ApiControllerTrait`) | nativo |
| `make:request` | `app/Http/Requests/Foo/{Name}.php` | nativo |
| `make:resource` | `app/Http/Resources/Foo/{Name}.php` | nativo |
| `make:policy` | `app/Policies/Foo/{Name}.php` | nativo |
| `make:job` | `app/Jobs/Foo/{Name}.php` | nativo |
| `make:command` | `app/Console/Commands/Foo/{Name}.php` | nativo |
| `make:middleware` | `app/Http/Middleware/Foo/{Name}.php` | nativo |
| `make:rule` | `app/Rules/Foo/{Name}.php` | nativo |
| `make:observer` | `app/Observers/Foo/{Name}.php` | nativo |
| `make:test` | depende de `--type` (ver abaixo) | nativo (com Pest forçado) |

### Comandos com contrato próprio

| Comando | Assinatura | Resultado |
|---|---|---|
| `make:service` | `{name} --domain={Dominio}` (domínio **obrigatório**) | `app/Services/{Dominio}/{Name}Service.php` |
| `make:repository` | `{name}` | Cria interface, implementação e bind no `RepositoryServiceProvider` |
| `make:adapter` | `{name} [--domain=Foo]` | `app/Adapters/[Foo/]{Name}Adapter.php` (Guzzle) |
| `make:helper` | `{name}` (sem `--domain`) | `app/Helpers/{Name}Helper.php` |

### `make:test` — tipos suportados

```bash
php artisan make:test ListOrders --type=feature --domain=Order
# → tests/Feature/Order/ListOrders.php
```

| `--type=` | Caminho gerado |
|---|---|
| `feature` | `tests/Feature/[Domain/]{Name}.php` |
| `unit-controller` | `tests/Unit/Controllers/[Domain/]{Name}.php` |
| `unit-service` | `tests/Unit/Services/[Domain/]{Name}.php` |
| `unit-policy` | `tests/Unit/Policies/[Domain/]{Name}.php` |
| `unit-helper` | `tests/Unit/Helpers/{Name}.php` (sem domínio) |

Pest é forçado sempre. Use `--phpunit` se precisar do stub PHPUnit.

## `make:repository` — fluxo

```bash
php artisan make:repository Order
```

Gera **três efeitos**:

1. `app/Contracts/Repositories/OrderRepositoryInterface.php`
2. `app/Repositories/OrderRepository.php` (estende `AbstractRepository`, implementa a interface)
3. `app/Providers/RepositoryServiceProvider.php` é criado (na primeira execução) ou atualizado com o bind `OrderRepositoryInterface::class => OrderRepository::class`

O `HeroLaraToolkitServiceProvider` detecta a presença de `App\Providers\RepositoryServiceProvider` no boot e o registra automaticamente — não é preciso editar `AppServiceProvider` nem `bootstrap/providers.php`.

Executar `make:repository Order` duas vezes é idempotente: o bind não duplica. Use `--force` para sobrescrever os arquivos `.php`.

## Validadores

Registrados globalmente pelo Service Provider — basta usar nas regras:

```php
public function rules(): array
{
    return [
        'cpf'      => 'required|cpf',
        'cnpj'     => 'nullable|cnpj',
        'telefone' => 'required|cellphone',
        'fixo'     => 'nullable|phone',
        'cep'      => 'required|cep',
        'passport' => 'nullable|passport',
    ];
}
```

## `ApiControllerTrait` + `BusinessException`

Padroniza respostas JSON de controllers:

```php
use HeroLaraToolkit\Traits\ApiControllerTrait;
use HeroLaraToolkit\Exceptions\BusinessException;

class OrderController extends Controller
{
    use ApiControllerTrait;

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = app(CreateService::class)->execute($request->validated());

            return $this->returnSuccess($order, 'Pedido criado.');
        } catch (Throwable $e) {
            return $this->returnError('Falha ao criar pedido.', $e);
        }
    }
}
```

Para erros de negócio que precisam vazar a mensagem para o caller, lance `BusinessException` — o trait substitui a mensagem genérica pela do exception automaticamente.

## Helpers

- `FormatHelper::cpf|cnpj|cep|phone|dateToBr|dateToMysql|floatToBr` — máscaras e conversões pt-BR ↔ MySQL.
- `DebugHelper::inFile($name, $data)` — escreve um JSON em `base_path()` para debug local. **Não comitar chamadas.**

## CHANGELOG

### 2.0.0 (breaking)

- **`make:service` mudou de assinatura**: `make:service Create Order` → `make:service Create --domain=Order`. Scripts/CI precisam ser atualizados ao subir.
- Adicionados 10 overrides de comandos nativos (`make:controller`, `make:request`, `make:resource`, `make:policy`, `make:job`, `make:command`, `make:test`, `make:middleware`, `make:rule`, `make:observer`) que aceitam `--domain=` opcional.
- Adicionados `make:adapter` e `make:helper`.
- `make:repository` agora gera interface + bind via `RepositoryServiceProvider` dedicado.
- Suite de testes Pest + Orchestra Testbench adicionada.

## Licença

MIT — veja [LICENSE](LICENSE).
