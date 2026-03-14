# Kennel — Project Architecture & Conventions

## Stack
- **Backend:** Laravel (PHP) — Inertia.js server-driven, not a REST/JSON API
- **Frontend:** Vue 3 + TypeScript + Vite
- **Styling:** Tailwind CSS only — no component library
- **Auth:** Laravel sessions + middleware (role-based), no JWT
- **Routing:** Ziggy for typed route references (`route('staff.dogs.show', dog)`)
- **@ alias** maps to `resources/js/`

---

## Critical Rules — Read First

- **Never scaffold a REST API** — this is Inertia.js. Mutations return `RedirectResponse`, reads return `Inertia::render()`
- **Never use axios for forms** — always use `useForm()` from `@inertiajs/vue3`
- **Never add a component library** — Tailwind only
- **Controllers stay thin** — validate → service → redirect/render, nothing else
- **All business logic lives in Services**
- **Fire events on state transitions** — especially all `Booking*` events
- **TypeScript strict mode** — all new code must be fully typed
- **Role-based portals are separate namespaces** — Staff (`/staff/*`) and Owner (`/owner/*`) never bleed into each other

---

## Backend Patterns

| Pattern | Usage |
|---------|-------|
| Services | All business logic — `DogService`, `BookingService`, `CapacityService`, `PaymentService` |
| Form Requests | Validation per verb — `StoreDogRequest`, `UpdateDogRequest` |
| JSON Resources | Response shaping — `DogResource`, `OwnerResource` |
| Events/Listeners | State change notifications — `BookingApproved`, `BookingCreated` |
| Enums | Type-safe constants — `BookingStatus`, `UserRole`, `DayOfWeek` |
| Middleware | Role gates — `EnsureUserIsStaff`, `EnsureUserIsOwner` |
| Repositories | Not used |
| Actions | Not used |

---

### Typical Controller

```php
class DogController extends Controller
{
    public function __construct(private readonly DogService $dogService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Staff/Dogs/Index', [
            'dogs' => DogResource::collection($this->dogService->list($request->only(['search']))),
        ]);
    }

    public function store(StoreDogRequest $request): RedirectResponse
    {
        $dog = $this->dogService->create($request->validated());
        return redirect()->route('staff.dogs.show', $dog)->with('success', 'Dog created.');
    }
}
```

---

### Routing

Routes split by portal in `routes/kennel.php`, prefixed and role-guarded:

```php
Route::middleware(['auth', 'verified', 'role.staff'])->prefix('staff')->name('staff.')->group(fn() =>
    Route::resource('dogs', DogController::class)
);
```

---

### Error & Flash Handling

- **Validation errors** → Inertia auto-maps to `form.errors.*` on the frontend — never manually pass errors
- **Flash messages** → `->with('success', '...')` → available as `page.props.flash.success`
- **Domain exceptions** → caught in controllers → `back()->withErrors(['field' => $e->getMessage()])`

---

### Backend Naming Conventions

| Artefact | Convention | Example |
|----------|-----------|---------|
| PHP class | PascalCase | `BookingService` |
| PHP method | camelCase | `validateDates()` |
| Event | Past-tense action | `BookingApproved` |
| Exception | Action + Exception | `BookingConflictException` |
| Resource | Entity + Resource | `DogResource` |
| Form Request | Verb + Entity + Request | `StoreDogRequest` |
| Route | dot-namespaced | `staff.dogs.index` |

---

## Frontend Patterns

### Component Style

`<script setup lang="ts">` consistently across all components — no Options API.

```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

const form = useForm({ name: '', breed: '' })
const submit = () => form.post(route('owner.dogs.store'))
</script>
```

---

### Routing

No Vue Router — all routing is server-side via Inertia. Navigation uses Inertia's `<Link>` and `router.*`. Always use Ziggy's `route()` for typed route references.

---

### State Management

Pinia (composition API style) — lean, UI state only (filters, pending status changes). All page data comes from Inertia page props, not stores.

Auth state is shared globally via `HandleInertiaRequests::share()` — access via `usePage().props.auth.user`. No auth store.

---

### API Calls

- Forms → always `useForm()` from Inertia
- Standalone requests (notifications, settings) → axios directly
- No global axios interceptor

---

### Forms & Validation

- `useForm()` from `@inertiajs/vue3` only — no VeeValidate or FormKit
- Server validation errors auto-wired to `form.errors.*` — no manual mapping needed
- Flash messages available at `usePage().props.flash.success`

---

### Types

Shared in `resources/js/types/kennel.ts`, mirroring Laravel Resources:

```ts
export interface Dog { id: number; name: string; breed: string }
export interface SharedProps { auth: { user: AuthUser | null }; flash: { success: string | null } }
```

---

### Folder Structure

```
resources/js/
├── pages/          # Page components — Staff/, Owner/, Auth/
├── Components/     # Shared UI — Kennel/ subdirectory for domain components
├── Layouts/        # KennelLayout.vue, AuthenticatedLayout.vue
├── stores/         # Pinia — useBookingStore.ts
├── composables/    # useAppearance.ts, useInitials.ts
├── services/       # NotificationApiService.ts, KennelSettingsService.ts
├── types/          # kennel.ts (domain types)
└── lib/            # utils.ts
```

---

### Frontend Naming Conventions

| Artefact | Convention | Example |
|----------|-----------|---------|
| Vue page | PascalCase, folder-mirrored | `Staff/Dogs/Index.vue` |
| Vue component | PascalCase | `DogForm.vue` |
| Store | `use` prefix, camelCase | `useBookingStore.ts` |
| Composable | `use` prefix, camelCase | `useAppearance.ts` |
| Frontend service | PascalCase + ApiService | `NotificationApiService.ts` |
| Tailwind palette | zinc/indigo/emerald/red | neutral/primary/success/danger |

---

## Testing

- PHPUnit for backend only
- No frontend test framework configured
