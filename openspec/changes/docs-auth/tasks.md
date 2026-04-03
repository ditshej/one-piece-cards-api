## 1. Register Bearer Token Security Scheme in Scramble

- [ ] 1.1 In `AppServiceProvider::boot()`, call `Scramble::afterOpenApiGenerated()` to inject `components.securitySchemes.BearerToken` (type: http, scheme: bearer, bearerFormat: API Key) and a global `security: [{BearerToken: []}]` into the OpenAPI document array.

## 2. Tests

- [ ] 2.1 Write a feature test asserting that `GET /docs/api.json` returns a JSON body containing `components.securitySchemes.BearerToken` with the correct type and scheme values.
- [ ] 2.2 Write a feature test asserting that `GET /docs/api.json` returns a JSON body with a top-level `security` array containing `{BearerToken: []}`.
- [ ] 2.3 Run the full test suite and confirm all tests pass.
