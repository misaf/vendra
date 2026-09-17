---
paths:
  - 'packages/*/src/Providers/**'
---

# Providers

## Package providers extend spatie PackageServiceProvider
Each package ships one `final class <Name>ServiceProvider extends PackageServiceProvider` in `src/Providers/`, declaring config/translations/views/migrations/commands in `configurePackage()`.
Register the package's Filament plugin in `packageRegistered()` via `Panel::configureUsing()`, guarded by `ResolvesConfiguredPanels::shouldRegisterOnPanel($panel->getId(), 'vendra-<name>')` — never unconditionally.
Declare `Relation::morphMap()` aliases in `packageBooted()` so persisted morph columns stay decoupled from model FQCNs.

## Gate::after callbacks must accept Authenticatable, not one package's user
Gate::after runs for whoever is checking an ability. Every guard in this app (`web`, `console`, `reseller`) authenticates the same canonical `User` (`misaf/vendra-user`); panel authorization is the admin role, an active `consoles` row, or being the main account (`resellers.user_id`) of an active reseller — never a separate user model. Still type the callback parameter Authenticatable and return null for anything that is not your package's user, so a check by a foreign authenticatable stays a denial instead of a TypeError.

Type the parameter Authenticatable and return null for anything that is not your package's user. UserServiceProvider::packageBooted() is the reference.
