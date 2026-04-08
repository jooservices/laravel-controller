# Request Lifecycle

1. The service provider boots and merges package configuration.
2. If enabled, the package registers its status route and versioned route mapping helpers.
3. Application controllers extending `BaseApiController` call response helpers from `HasApiResponses`.
4. Response helpers normalize payload, metadata, warnings, and trace identifiers into a consistent envelope.
5. Exception handling helpers translate common framework and domain failures into package-standard JSON error responses.