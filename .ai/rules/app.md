---
paths:
  - 'app/**'
---

# App

## Classes are final; no controller layer
Declare new classes `final` — the codebase is `final class` almost throughout, with `abstract` reserved for the handful of deliberate base classes.
There is no controller layer. The HTTP surface is Filament panels plus API Platform resources; business operations live in `app/Actions/` as `execute()` actions. Only add a controller for a genuine standalone endpoint, and make it a single-action `__invoke()` controller.
`Model::shouldBeStrict()` is on in `AppServiceProvider`, so lazy loading and discarded attributes throw — eager-load relations explicitly.
