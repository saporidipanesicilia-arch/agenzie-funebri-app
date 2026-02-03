# Funeral Agency Management — Architecture (FROZEN)

> **Status**: FROZEN  
> **Last Updated**: 2026-02-03  
> **Authority**: Technical Lead

---

## Architecture Principles

This application follows **Clean Architecture** with a **use-case–driven approach**.

### Core Rules (IMMUTABLE)

1. **UI is Passive**
   - Blade views and Controllers contain ZERO business logic
   - Controllers only orchestrate use cases and format responses
   - No calculations, no validations, no decisions in the UI layer

2. **Application Layer = Orchestration**
   - All workflows live in Use Cases (`app/Application/UseCases`)
   - Use cases coordinate between domain and infrastructure
   - Use cases enforce transactions and cross-cutting concerns

3. **Domain Layer = Business Rules**
   - Models (`app/Models`) contain business rules and validation
   - Domain services contain complex business logic
   - All funeral-specific rules live here (pricing, deadlines, permissions)

4. **Infrastructure = Replaceable**
   - Repositories abstract database access
   - External APIs are behind interfaces
   - File storage, email, SMS are infrastructure concerns

---

## Directory Structure

```
app/
├── Application/
│   └── UseCases/
│       ├── CreateFuneral/
│       │   ├── CreateFuneralUseCase.php
│       │   ├── CreateFuneralRequest.php
│       │   └── CreateFuneralResponse.php
│       └── ...
├── Domain/
│   ├── Models/          # Eloquent models with business rules
│   ├── Services/        # Domain services for complex logic
│   └── ValueObjects/    # Immutable domain values
├── Infrastructure/
│   ├── Repositories/    # Database abstraction
│   ├── Services/        # External APIs (PDF, Email, SMS)
│   └── Traits/          # Cross-cutting concerns (Multi-tenant)
└── Http/
    └── Controllers/     # Thin orchestration layer
```

---

## Use Case Pattern

Every feature follows this pattern:

### 1. Request DTO
```php
class CreateFuneralRequest
{
    public function __construct(
        public readonly int $tenantId,
        public readonly array $deceasedData,
        public readonly array $ceremonyData,
    ) {}
}
```

### 2. Use Case
```php
class CreateFuneralUseCase
{
    public function __construct(
        private FuneralRepository $funeralRepository,
        private DeceasedRepository $deceasedRepository,
        private TimelineService $timelineService,
    ) {}

    public function execute(CreateFuneralRequest $request): CreateFuneralResponse
    {
        // Orchestrate domain operations
        DB::transaction(function() use ($request) {
            $deceased = $this->deceasedRepository->create($request->deceasedData);
            $funeral = $this->funeralRepository->create($request->ceremonyData);
            $this->timelineService->initializeFor($funeral);
            
            return new CreateFuneralResponse($funeral);
        });
    }
}
```

### 3. Response DTO
```php
class CreateFuneralResponse
{
    public function __construct(
        public readonly Funeral $funeral
    ) {}
}
```

### 4. Controller (Thin)
```php
class FuneralController extends Controller
{
    public function store(Request $request, CreateFuneralUseCase $useCase)
    {
        $validated = $request->validate([...]);
        
        $useCaseRequest = new CreateFuneralRequest(
            tenantId: auth()->user()->tenant_id,
            deceasedData: $validated['deceased'],
            ceremonyData: $validated['ceremony'],
        );
        
        $response = $useCase->execute($useCaseRequest);
        
        return redirect()->route('funerals.show', $response->funeral->id);
    }
}
```

---

## Dependency Flow

```
Controller → Use Case → Domain Service → Model
                ↓           ↓
          Repository  ←  Infrastructure
```

**Rules**:
- Domain NEVER depends on Infrastructure
- Application depends on Domain interfaces
- Infrastructure implements Domain interfaces
- UI depends on Application, never on Infrastructure directly

---

## Multi-Tenancy

All data access goes through:
1. **Global Scope** (`BelongsToTenant` trait) for automatic filtering
2. **Repository Pattern** to enforce tenant isolation
3. **Use Cases** validate tenant ownership before operations

---

## Testing Strategy

- **Unit Tests**: Domain models and services (no DB)
- **Integration Tests**: Use cases with in-memory DB
- **Feature Tests**: Full HTTP workflows with seeded data

---

## Change Management

> ⚠️ **CRITICAL**: This architecture is FROZEN.

If future requirements conflict with this structure:
1. STOP immediately
2. Document the conflict
3. Ask for explicit confirmation before proceeding
4. Do NOT attempt "clever workarounds"

---

## Migration Path (if needed)

Any architectural changes require:
1. Written justification
2. Impact analysis on existing code
3. Migration plan
4. Approval from Technical Lead

**Current Status**: NO CHANGES APPROVED
