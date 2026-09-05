# OpenAPI Envelope Contract

v4 ships stable OpenAPI 3.1 component schemas for client codegen and API docs.

## Artifact

Path (in this package / Composer vendor tree):

```text
resources/openapi/envelope.v4.yaml
```

PHP constants: `JOOservices\LaravelController\OpenApi\EnvelopeContract`

| Constant | Value |
| --- | --- |
| `SCHEMA_API_SUCCESS_ENVELOPE` | `ApiSuccessEnvelope` |
| `SCHEMA_API_ERROR_ENVELOPE` | `ApiErrorEnvelope` |
| `SCHEMA_API_PROBLEM_DETAILS` | `ApiProblemDetails` |
| `SCHEMA_PAGINATION_META` | `PaginationMeta` |
| `SCHEMA_CURSOR_PAGINATION_META` | `CursorPaginationMeta` |
| `SCHEMA_OFFSET_PAGINATION_META` | `OffsetPaginationMeta` |
| `CONTENT_TYPE_PROBLEM_JSON` | `application/problem+json` |
| `OPENAPI_RELATIVE_PATH` | `resources/openapi/envelope.v4.yaml` |

## Reference from your app OpenAPI

```yaml
components:
  schemas:
    UserListResponse:
      allOf:
        - $ref: 'vendor/jooservices/laravel-controller/resources/openapi/envelope.v4.yaml#/components/schemas/ApiSuccessEnvelope'
```

Adjust the `$ref` path to match how your OpenAPI bundler resolves Composer vendor files.

Pagination fields for length-aware, cursor, and offset helpers live under `meta.pagination` and match the `*PaginationMeta` schemas.
