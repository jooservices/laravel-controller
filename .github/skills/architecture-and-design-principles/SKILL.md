# Architecture And Design Principles

Module ownership:

- response payload assembly belongs in `HasApiResponses`
- exception normalization belongs in `HandlesApiExceptions`
- bootstrapping, publishing, and route registration belong in the service provider
- package endpoints belong in `src/Http/Controllers/`

Design rules:

- do not move public behavior into new abstractions without clear need
- keep the package focused on API response infrastructure rather than application business logic
- follow Request -> Controller -> FormRequest -> Service -> Repository -> Model in examples
- Laravel Resource remains the presentation transformer
- response envelopes wrap output and do not replace Resources
- DTO/data objects may be normalized as input only
