# API Design Rules — plainenv-api

## URL Structure

```
/api/auth/register
/api/auth/login
/api/auth/logout
/api/auth/me

/api/projects
/api/projects/{project}
/api/projects/{project}/environments
/api/projects/{project}/environments/{environment}
/api/projects/{project}/environments/{environment}/variables
/api/projects/{project}/environments/{environment}/variables/{variable}
/api/projects/{project}/environments/{environment}/export

/api/projects/{project}/compare?from={envId}&to={envId}
/api/projects/{project}/activity
/api/projects/{project}/members

/api/tokens          ← CLI token management
```

## Naming Rules

- URLs: kebab-case, plural nouns
- No verbs in URLs — use HTTP methods
- Nested max 2 levels deep: `/projects/{id}/environments` OK, never 3 levels

## Pagination

List endpoints always paginate:

```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 54
  }
}
```

Use `?per_page=20&page=1` query params.

## Error Responses

```json
// 401 Unauthenticated
{ "message": "Unauthenticated" }

// 403 Forbidden
{ "message": "This action is unauthorized" }

// 404 Not found
{ "message": "Project not found" }

// 422 Validation
{
  "message": "The name field is required",
  "errors": {
    "name": ["The name field is required"]
  }
}
```

## Export Endpoint

`GET /api/projects/{id}/environments/{envId}/export`

Returns plain text `.env` format, not JSON:

```
Content-Type: text/plain
Content-Disposition: attachment; filename=".env"

APP_NAME=myapp
APP_ENV=production
DATABASE_URL=postgres://...
```

## CORS

Allow all origins in development. In production, restrict to `plainenv.com` and `localhost`.

## Rate Limiting

- Auth endpoints: 10 requests/minute
- Export endpoint: 30 requests/minute
- Everything else: 60 requests/minute
