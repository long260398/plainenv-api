# Laravel Conventions — plainenv-api

## Folder Structure

```
app/
  Http/
    Controllers/Api/     ← all API controllers here
    Requests/            ← Form Request validation classes
    Resources/           ← API Resource transformers
  Models/                ← Eloquent models
  Services/              ← business logic (if controller gets fat)
```

## Controllers

- Thin controllers — no business logic, only call models/services
- One controller per resource: `ProjectController`, `EnvironmentController`, etc.
- Always use Form Requests for validation
- Always return API Resources, never raw models

```php
// Good
public function store(StoreProjectRequest $request): JsonResponse
{
    $project = Project::create([...$request->validated(), 'user_id' => auth()->id()]);
    return response()->json(['data' => new ProjectResource($project)], 201);
}

// Bad — inline validation, raw model return
public function store(Request $request)
{
    $request->validate([...]);
    return Project::create($request->all());
}
```

## Models

- Define `$fillable` explicitly — never use `$guarded = []`
- Define relationships for all foreign keys
- Define casts for booleans, dates, JSON

```php
protected $fillable = ['name', 'description', 'user_id'];

protected $casts = [
    'meta' => 'array',
    'created_at' => 'datetime',
];
```

## Migrations

- Always snake_case column names
- Always add `->comment()` for non-obvious columns
- Foreign keys must have `->constrained()->cascadeOnDelete()`
- Timestamps on every table

```php
$table->foreignId('project_id')->constrained()->cascadeOnDelete();
$table->enum('role', ['owner', 'editor', 'viewer'])->default('viewer');
```

## API Resources

Every model response goes through a Resource class:

```php
// app/Http/Resources/ProjectResource.php
public function toArray(Request $request): array
{
    return [
        'id'          => $this->id,
        'name'        => $this->name,
        'description' => $this->description,
        'created_at'  => $this->created_at->toISOString(),
    ];
}
```

## Form Requests

Every POST/PUT uses a dedicated Form Request:

```php
// app/Http/Requests/StoreProjectRequest.php
public function rules(): array
{
    return [
        'name'        => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string', 'max:1000'],
    ];
}
```

## Response Format

Always consistent JSON structure:

```json
// Success
{ "data": {...} }
{ "data": [...] }

// Created
{ "data": {...}, "message": "Project created" }

// Error (handled by Laravel exception handler)
{ "message": "...", "errors": { "field": ["..."] } }
```

HTTP status codes:
- `200` — OK
- `201` — Created
- `204` — No content (DELETE)
- `401` — Unauthenticated
- `403` — Unauthorized (wrong role)
- `404` — Not found
- `422` — Validation error

## Routes

All routes in `routes/api.php`. Group by resource:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('projects.environments', EnvironmentController::class);
});
```
