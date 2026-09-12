# Vendra

> **Ecosystem documentation:** <https://misaf.github.io/vendra-ecosystem-docs>
>
> How this platform, the estate, and the storefront fit together — architecture,
> tenancy, provisioning, and operations — is documented there.
> This README covers the host application and the package monorepo.

Vendra is a modular Laravel 13 application for commerce, content, customer
management, and multi-tenant platform development. This repository contains the
host application and every first-party Vendra package in one Composer monorepo.

## Requirements

- PHP 8.4+
- Laravel 13
- Composer
- Node.js and npm
- MySQL or another Laravel-supported database

## Local Development

```bash
composer setup
composer dev
```

`composer setup` installs PHP and JavaScript dependencies, creates `.env`,
generates the application key, runs migrations, and builds frontend multimedia.
`composer dev` starts the web server, queue listener, logs, and Vite in watch
mode.

## Package Catalog

Packages are independently installable through Composer and are auto-discovered
by Laravel unless their README says otherwise.

| Area | Packages |
| --- | --- |
| Foundation | [Support](packages/vendra-support), [API](packages/vendra-api), [Tenant](packages/vendra-tenant), [Testing](packages/vendra-testing) |
| Standalone dependencies | [Email Validation](https://github.com/misaf/laravel-email-validation), [Emailable Driver](https://github.com/misaf/laravel-email-validation-emailable) |
| Catalog and sales | [Product](packages/vendra-product), [Attribute](packages/vendra-attribute), [Currency](packages/vendra-currency), [Cart](packages/vendra-cart), [Transaction](packages/vendra-transaction), [Subscription](packages/vendra-subscription) |
| Content and marketing | [Blog](packages/vendra-blog), [Custom Page](packages/vendra-custom-page), [FAQ](packages/vendra-faq), [Multimedia](packages/vendra-multimedia), [Tagger](packages/vendra-tagger), [Newsletter](packages/vendra-newsletter), [Affiliate](packages/vendra-affiliate) |
| Customers and access | [User](packages/vendra-user), [User Profile](packages/vendra-user-profile), [Address](packages/vendra-address), [Phone](packages/vendra-phone), [Document](packages/vendra-document), [Verification](packages/vendra-verification), [Permission](packages/vendra-permission), [Socialite](packages/vendra-socialite) |
| Operations and localization | [Activity Log](packages/vendra-activity-log), [Authify Log](packages/vendra-authify-log), [Developer Logins](packages/vendra-developer-logins) (development only), [Language](packages/vendra-language), [Localization](packages/vendra-localization) |
| API Platform modules | [Affiliate API](packages/vendra-affiliate-api), [Attribute API](packages/vendra-attribute-api), [Blog API](packages/vendra-blog-api), [Cart API](packages/vendra-cart-api), [Custom Page API](packages/vendra-custom-page-api), [FAQ API](packages/vendra-faq-api), [Multimedia API](packages/vendra-multimedia-api), [Product API](packages/vendra-product-api) |

Domain packages depend on the provider-neutral contracts in Vendra Support.
Installing Vendra Tenant activates tenant awareness by binding the concrete
resolver; domain and API packages do not depend on the tenant provider.

The host exposes the package-owned API Platform resources below `/api` and
publishes OpenAPI documentation at `/api/docs`. Frontend code should use the
dedicated `window.api` fetch client configured in `resources/js/bootstrap.js`.
Domain endpoints use `/api/{admin-navigation-group}/{model-resource}` so the
public API follows the Filament admin structure, for example
`/api/catalog/products`.

## Docker

The `docker/Dockerfile` builds the production image (FrankenPHP,
Composer install from the committed lock file, Vite asset build). Pushing a
`MAJOR.MINOR.PATCH` tag publishes `linux/amd64` and `linux/arm64` images to
`ghcr.io/misaf/vendra` (`:1.0.0`, `:1.0`, `:latest`).

The estate itself — Traefik, the platform services, and the optional marketing
site — lives in `docker/stacks/` and is driven by the host-level
`docker/stacks/bin/vendra` script. See [docker/stacks/README.md](docker/stacks/README.md).

Laravel stores tenant and storefront business state and owns the storefront
containers: its queued provisioning, retry, and reconciliation workflows talk to
a container runtime through `STOREFRONT_DOCKER_ENDPOINT` — Docker or a Podman
compatibility socket, since only Engine-API calls are used — creating one container per
storefront on the network named by `STOREFRONT_DOCKER_NETWORK`. It never creates
the network, the proxy, or the TLS material — those belong to the estate.

## Module Development

Modules are developed locally through symlinked Composer path repositories in
`packages/*`.

Typical workflow:

1. Edit the module inside `packages/<module-name>`.
2. Ensure the package is required in root `composer.json`.
3. Run `composer update <vendor/package>` or `composer dump-autoload` when needed.
4. Run the package's tests and static analysis as documented in its README.

For production builds, rely on installed Composer packages rather than local path repository workflows.

## Documentation and AI Guidance

Every package maintains the same documentation set:

- `README.md` is the user-facing contract for features, requirements,
  installation, usage, and package-level checks.
- `resources/boost/guidelines/core.blade.php` contains concise, always-loaded
  package boundaries and invariants.
- `resources/boost/skills/*/SKILL.md` contains the on-demand workflow and
  implementation guidance for that package.

Keep these files aligned with `composer.json`, the package source, and its
tests. Describe optional integrations as optional, do not document behavior
that is not implemented, and update all affected layers in the same change.
The root `AGENTS.md`, `CLAUDE.md`, and `.agents/skills` files are generated by
Laravel Boost and may be refreshed with:

```bash
php artisan boost:update --no-interaction
```

## Testing

- Run the smallest relevant test scope first, then expand to broader suites only
  when necessary.
- Use `php artisan test --parallel` by default for targeted tests and full
  suites to minimize feedback time.
- Omit `--parallel` only when debugging a failure, investigating race conditions
  or concurrency issues, using shared mutable state or external resources that
  cannot be isolated, or when the execution environment does not support
  parallel testing.
- Keep intentionally non-parallel coverage, profiling, mutation testing, and
  benchmarking commands unchanged unless parallel execution is clearly safe.

```bash
php artisan test --parallel --compact
php artisan test --parallel --compact --filter=testName
vendor/bin/pint --dirty --format agent
composer stan
```

## Troubleshooting

- If package changes are not reflected, run `composer dump-autoload`.
- If provider discovery seems stale, run `php artisan package:discover`.
- If configuration values look outdated, run `php artisan config:clear`.
- If frontend changes do not appear, run `npm run dev` or `npm run build`.

## License

MIT. See [LICENSE](LICENSE).
