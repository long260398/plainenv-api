# Security Rules — plainenv-api

## Variable Encryption (CRITICAL)

All variable values MUST be encrypted before saving. Never store plaintext.

```php
// Saving — always encrypt
'value' => Crypt::encryptString($request->value)

// Reading — always decrypt
'value' => Crypt::decryptString($this->value)
```

Decryption happens in the API Resource, NOT in the controller or model.

```php
// VariableResource.php
public function toArray(Request $request): array
{
    return [
        'id'    => $this->id,
        'key'   => $this->key,
        'value' => Crypt::decryptString($this->value),
    ];
}
```

## Authorization

Every request must check ownership. Use Policies, not inline checks.

```php
// Register policy in AuthServiceProvider
Gate::policy(Project::class, ProjectPolicy::class);

// Controller
$this->authorize('update', $project);

// Policy
public function update(User $user, Project $project): bool
{
    return $project->user_id === $user->id
        || $project->members()->where('user_id', $user->id)
                              ->whereIn('role', ['owner', 'editor'])
                              ->exists();
}
```

## Sanctum Tokens

- Web auth: session-based (cookie)
- CLI auth: named tokens with abilities

```php
// Create CLI token
$token = $user->createToken('cli-token', ['env:read']);
return ['token' => $token->plainTextToken];

// Check ability in controller
$request->user()->tokenCan('env:read');
```

## Input Validation

- Always use Form Requests — never trust raw input
- Sanitize variable keys: only allow `A-Z`, `0-9`, `_`
- Max lengths: key=255, value=10000, name=255

```php
'key' => ['required', 'regex:/^[A-Z0-9_]+$/'],
```

## What NEVER to do

- Never return encrypted values raw — always decrypt in Resource
- Never use `$request->all()` in `create()` — use `$request->validated()`
- Never expose `user_id` or internal IDs in error messages
- Never log variable values — only log keys
- Never skip `authorize()` check in controllers
