# Request Lifecycle

1. A request enters a host Laravel route.
2. The controller delegates validation to a FormRequest.
3. The controller calls an application service.
4. The service coordinates repositories, models, or domain data objects.
5. The controller passes the result through a Laravel `JsonResource` or `ResourceCollection`.
6. `BaseApiController` wraps that transformed payload with the package response envelope.
7. The response formatter returns the final JSON payload.

Recommended application shape:

```text
Request -> Controller -> FormRequest -> Service -> Repository -> Model
```

## ResponseFormatter Role

`ResponseFormatter` receives the normalized outer response context:

- `success`
- `code`
- `message`
- `data`
- `meta`
- `errors`
- `trace_id`
- `warnings`
- configured response keys

It may change the final top-level JSON shape. It should not replace Laravel Resources or move business logic into the response layer.
