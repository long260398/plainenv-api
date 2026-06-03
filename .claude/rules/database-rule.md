# Database Rules — plainenv-api

## Naming Conventions

- Tables: `snake_case`, plural (`projects`, `activity_logs`)
- Columns: `snake_case` (`created_at`, `user_id`)
- Foreign keys: `{model}_id` (`project_id`, `user_id`)
- Pivot tables: alphabetical order (`project_user`, not `user_project`)
- Indexes: `{table}_{column}_index`

## Migration Rules

- One migration per logical change
- Always reversible — write `down()` properly
- Never modify existing migrations — create new ones
- Add indexes on all foreign keys and frequently queried columns

## Column Standards

```php
// Every table has
$table->id();
$table->timestamps();

// Soft deletes where data matters
$table->softDeletes();

// Foreign keys always constrained
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->foreignId('project_id')->constrained()->cascadeOnDelete();

// Enums with defaults
$table->enum('role', ['owner', 'editor', 'viewer'])->default('viewer');

// Encrypted values — TEXT not VARCHAR (encrypted is longer)
$table->text('value');

// JSON columns
$table->json('meta')->nullable();
```

## Indexes

```php
// Always index foreign keys (Laravel does this automatically with foreignId)
// Add composite index for frequent queries
$table->index(['project_id', 'environment_id']);
$table->unique(['environment_id', 'key']); // no duplicate keys per env
```

## Schema Reference

```sql
users
  id, name, email, password, remember_token, timestamps

projects
  id, user_id (FK), name, description(nullable), timestamps

environments
  id, project_id (FK), name, timestamps
  unique: [project_id, name]

variables
  id, environment_id (FK), key, value(text, encrypted), timestamps
  unique: [environment_id, key]

members
  id, project_id (FK), user_id (FK), role(enum), timestamps
  unique: [project_id, user_id]

activity_logs
  id, project_id (FK), user_id (FK), action(string), meta(json), timestamps

personal_access_tokens  ← managed by Sanctum
```

## Query Rules

- Always eager load relationships to avoid N+1
- Use `select()` to limit columns on large tables
- Never use `all()` without pagination on user-owned data

```php
// Good
Project::with(['environments'])->where('user_id', auth()->id())->paginate(20);

// Bad — N+1 and no pagination
Project::all();
```
