# Ecosystem Comparison

This document compares the **JOOservices Laravel Controller** package with adjacent Laravel API packages.

The goal is not to find a single winner. These packages solve different layers of the API stack:

- **JOOservices Laravel Controller** focuses on controller ergonomics, consistent envelopes, and package-level API response behavior.
- **ResponseBuilder** focuses on normalized REST JSON responses and exception-safe API output.
- **Laravel Responder** focuses on Fractal-based transformation and response serialization.
- **Laravel JSON:API** focuses on full JSON:API compliance.
- **Dingo API** focuses on a broader API framework with versioning, negotiation, and adapters.
- **Spatie Laravel Data + Query Builder** focuses on transformation, validation, and query conventions rather than a base controller.

## Feature matrix

| Capability | JOOservices Laravel Controller | ResponseBuilder | Laravel Responder | Laravel JSON:API | Dingo API fork | Spatie Data + Query Builder |
|-----|-----|-----|-----|-----|-----|-----|
| Standardized JSON envelope | Yes | Yes | Yes | Spec-defined | Formatter-driven | No |
| Base API controller / trait helpers | Yes | Partial | Yes | Partial | Yes | No |
| Laravel `JsonResource` integration | Yes | Partial | No | No | Partial | N/A |
| Length-aware pagination helper | Yes | Partial | Yes | Yes | Yes | Partial |
| Cursor / offset pagination helper | Yes | No | Cursor only | Yes | Partial | Partial |
| Exception to JSON normalization | Yes | Yes | Yes | Yes | Yes | No |
| Configurable response keys | Yes | Limited | Serializer-driven | Spec-locked | Formatter-driven | No |
| Trace ID in every envelope | Yes | No | No | No | No | No |
| HAL-style item / pagination links | Yes | No | Partial | JSON:API links | Partial | No |
| Built-in status / health endpoint | Yes | No | No | No | No | No |
| Validation message strategy | Yes | Partial | Partial | Yes | Yes | Yes |
| Localization of default messages | Yes | Yes | Yes | Partial | Partial | N/A |
| Transformer / schema layer | No | Conversion only | Yes | Yes | Yes | Yes |
| Query include / filter / sort / sparse fieldsets | No | No | Yes | Yes | Partial | Yes |
| API versioning support | Route-file based | No | No | Route/server based | Yes | No |
| Content negotiation / media types | No | No | Serializer-focused | Yes | Yes | No |
| Multiple auth adapters | No | No | No | No | Yes | No |
| Internal API requests | No | No | No | No | Yes | No |
| Testing helpers for package contract | No | Yes | Partial | Yes | Partial | Partial |
| Current maintenance fit for Laravel 12+ | Native | Strong | Good | Strong | Strong | Strong |

## Maintenance notes

| Package | Current reading |
|-----|-----|
| JOOservices Laravel Controller | Native to this repository, currently aligned with Laravel 12 and PHP 8.5. |
| marcin-orlowski/laravel-api-response-builder | Actively maintained and recently released for Laravel 12. |
| flugg/laravel-responder | Still maintained, but architecturally tied to Fractal and a transformer-first workflow. |
| laravel-json-api/laravel | Actively maintained and standards-driven, but much heavier than this package's current scope. |
| api-ecosystem-for-laravel/dingo-api | Maintained fork of the abandoned `dingo/api`, still the broadest API framework in this comparison. |
| spatie/laravel-data + spatie/laravel-query-builder | Both are actively maintained and widely adopted, but they are building blocks, not a drop-in controller package. |

## What JOOservices Laravel Controller already does well

- It has a very clear value proposition for teams that want a lightweight Laravel-native controller base instead of adopting a full API framework.
- It already covers several gaps that other response-focused libraries do not cover well: trace correlation, status endpoint metadata, HAL-style links, and offset pagination helpers.
- It stays close to Laravel's native `JsonResource` model instead of forcing Fractal or a specification-specific schema layer.

## Gaps worth adding

These are the missing features that appear repeatedly across competing packages and would improve the package without changing its identity.

### 1. Official query convention layer

Current gap:

- no first-class support for request-driven `include`, `filter`, `sort`, or sparse field selection

Why it matters:

- this is the most visible capability gap versus Laravel JSON:API, Laravel Responder, and Spatie Query Builder
- API consumers often expect these conventions once the package already standardizes pagination and response envelopes

Recommended direction:

- add an optional integration layer for `spatie/laravel-query-builder`
- keep it opt-in so the package remains lightweight

### 2. First-class transformation contract

Current gap:

- the package integrates with Laravel `JsonResource`, but it does not define a richer transformation contract for DTOs, typed data objects, or alternate serializers

Why it matters:

- the ecosystem is moving toward typed data objects and resource/data unification
- this is where Spatie Laravel Data is materially stronger

Recommended direction:

- add an abstraction for "transformable response input" that can accept `JsonResource`, arrays, and optional DTO/data object adapters
- provide an official recipe or bridge for `spatie/laravel-data`

### 3. Extensible exception mapping and domain error codes

Current gap:

- exception mapping is useful, but still fixed and relatively shallow
- there is no first-class error code catalog or config-driven exception map

Why it matters:

- ResponseBuilder, Dingo, and Laravel JSON:API all provide stronger error semantics than a plain message plus HTTP status

Recommended direction:

- add configurable exception-to-response mappings
- allow stable machine-readable error codes in the envelope
- optionally support RFC 7807 style output as an alternate serializer mode

### 4. Better API versioning policy

Current gap:

- route-file discovery helps with `/api/v1`, but versioning behavior stops at route organization
- there is no deprecation, sunset, or version negotiation story

Why it matters:

- Dingo still differentiates itself here even when teams do not want the rest of its stack

Recommended direction:

- keep URL-based versioning as the default
- add optional helpers for `Deprecation`, `Sunset`, and version metadata headers
- document a consistent version lifecycle policy

### 5. Contract testing helpers

Current gap:

- the package standardizes envelopes, but does not yet ship assertion helpers for those envelopes

Why it matters:

- once a package owns a response contract, it should also help users test that contract

Recommended direction:

- add PHPUnit assertions such as `assertApiSuccess()`, `assertApiValidationError()`, and `assertApiTraceId()`

## Missing but probably out of scope

These features exist in competitor packages, but adding them directly would likely dilute the package.

- full content negotiation and media type handling
- multiple authentication adapters
- internal request dispatching
- full JSON:API compliance layer inside the base package
- a Fractal-style transformer subsystem as a hard dependency

For these concerns, it is better to integrate with external packages than to absorb them into the core package.

## Decision guide for the open questions

This section answers the four capabilities that came up in the matrix and explains what the package should do next.

### 1. Content negotiation and media types

Recommendation:

- do **not** add full content negotiation to the core package
- do add a small optional output-format layer for error responses and possibly serializer presets

Why this is the right scope:

- full Accept-header negotiation would push the package toward Dingo or Laravel JSON:API territory
- the package's current value is a Laravel-native controller contract, not an API gateway framework
- there is still value in supporting alternate response formats where the contract is already close, especially errors

Benefit:

- lets teams expose `application/json` by default while optionally returning `application/problem+json` for clients that expect RFC 7807 style errors
- improves interoperability with frontend platforms, API gateways, and third-party consumers
- gives the package a standards-friendly path without forcing a full JSON:API rewrite

Suggested usage:

- keep default behavior unchanged
- add a config option such as `error_format => 'default'|'problem-json'`
- optionally add a helper that sets headers based on the chosen error format

Example usage:

```php
return $this->unprocessable(
	'Validation failed',
	['email' => ['The email field is required.']]
);
```

With `problem-json` enabled, the response could be serialized to a Problem Details structure while the controller API stays the same.

Priority:

- medium

### 2. Multiple auth adapters

Recommendation:

- do **not** implement auth adapters inside the package core
- do publish integration recipes for Laravel Sanctum, Passport, and JWT-based packages

Why this is the right scope:

- authentication in Laravel is already well served by the framework ecosystem
- adding adapter abstractions would couple this package to security concerns it does not currently own
- it would increase maintenance cost with little differentiation versus existing Laravel auth solutions

Benefit:

- keeps the package small and controller-focused
- avoids duplicating framework auth features
- still helps users by documenting how standardized responses should behave for `401` and `403` across common auth stacks

Suggested usage:

- provide docs recipes such as "Using with Sanctum" and "Using with Passport"
- show how to combine middleware with `unauthorized()` and `forbidden()` responses
- optionally add a config recipe for machine-readable auth error codes

Example usage:

```php
Route::middleware('auth:sanctum')->group(function (): void {
	Route::get('/profile', [ProfileController::class, 'show']);
});
```

Inside exception handling, the package should continue to normalize `AuthenticationException` and `AuthorizationException` instead of owning auth adapters.

Priority:

- low for code
- medium for documentation

### 3. Internal API requests

Recommendation:

- do **not** add internal request dispatching to the package core

Why this is the right scope:

- internal requests often hide poor service boundaries and make tracing, authorization, and transaction flow harder to reason about
- Dingo supports it because it is a fuller API framework; this package should stay closer to standard Laravel controller and service patterns
- the safer Laravel-native pattern is to move shared work into actions or services rather than issuing fake HTTP calls inside the app

Benefit:

- preserves clear application architecture
- avoids surprising middleware, auth, and request lifecycle interactions
- keeps tracing and debugging straightforward

Suggested usage:

- recommend extracting shared business logic into service classes, actions, or domain handlers
- let multiple controllers call the same service instead of making internal HTTP requests

Example usage:

```php
final readonly class SyncUserProfileAction
{
	public function execute(User $user): array
	{
		return [
			'id' => $user->id,
			'synced' => true,
		];
	}
}
```

Both controllers can call the action and then return the package envelope normally.

Priority:

- do not add

### 4. Testing helpers for package contract

Recommendation:

- add this feature soon
- this is the strongest candidate from the four items listed here

Why this should be added:

- the package already defines a stable response contract
- once a package owns a contract, it should make that contract easy to test
- this is a high-value, low-risk addition that fits the current scope perfectly

Benefit:

- reduces repetitive JSON assertion code in consuming applications
- makes adoption easier because users can verify envelope compliance quickly
- improves backward-compatibility discipline for future package releases

Suggested usage:

- ship a PHPUnit trait such as `InteractsWithApiResponses`
- add assertions like `assertApiSuccessResponse()`, `assertApiErrorResponse()`, `assertApiValidationErrorResponse()`, and `assertApiTraceId()`

Example usage:

```php
$response = $this->getJson('/api/v1/users');

$this->assertApiSuccessResponse($response, 200);
$this->assertApiTraceId($response);
```

Validation case:

```php
$response = $this->postJson('/api/v1/users', []);

$this->assertApiValidationErrorResponse($response, [
	'email',
	'name',
]);
```

Priority:

- high

## Suggested roadmap

1. Add testing helpers for the package response contract.
2. Add configurable error codes and exception mapping.
3. Publish an official integration guide for Spatie Query Builder and Spatie Laravel Data.
4. Add optional version lifecycle headers and deprecation helpers.
5. Evaluate an alternate serializer mode for Problem Details or JSON:API-style errors without changing the default envelope.