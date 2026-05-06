# Project Overview

**JOOservices Laravel Controller** standardizes the controller boundary for Laravel APIs.

It owns the response envelope layer:

```text
Model / entity / domain DTO
    -> Laravel Resource / ResourceCollection
    -> API response envelope
    -> JsonResponse
```

Laravel Resources remain the official presentation transformer for payload data. This package wraps transformed payloads, pagination metadata, warnings, errors, and trace IDs into a consistent API response contract.

## Package Scope

This package is:

- base API controller helpers
- standard response envelope helpers
- pagination and status response helpers
- formatter contract
- optional exception response helper

This package is not:

- CRUD generator
- service layer replacement
- repository replacement
- validation package
- full application exception-handler framework
- JSON:API full implementation
- business logic layer

## JOOservices Ecosystem Fit

The package aligns with `jooservices/dto` by accepting structured data inputs where Laravel can serialize them, but it does not replace Laravel Resources. DTOs and data objects are useful application-layer inputs; Resources remain the API presentation layer.
