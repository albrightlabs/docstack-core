# API Reference

This tab demonstrates a third section for API documentation.

## Admin API

When `FEATURE_EDITING=true`, DocStack exposes an admin API for content management.

### Authentication

All admin endpoints require the `X-Admin-Token` header with your `ADMIN_PASSWORD`.

### Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/tree` | Get navigation tree |
| GET | `/api/content` | Get page content |
| POST | `/api/content` | Create/update page |
| DELETE | `/api/content` | Delete page |

## Example Request

```bash
curl -X GET "https://docs.example.com/api/tree" \
  -H "X-Admin-Token: your-admin-password"
```

## Response Format

```json
{
  "success": true,
  "data": {
    "sections": [...]
  }
}
```

## Error Handling

Errors return appropriate HTTP status codes with JSON error messages:

```json
{
  "success": false,
  "error": "Unauthorized"
}
```
