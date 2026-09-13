---
paths:
  - 'tests/Feature/**'
---

# Feature

## Storefront provisioning tests use Laravel Docker Engine + vendra-store config
Storefront runtime config lives in `container.default` (env `CONTAINER_DRIVER`) and the selected driver's host (`DOCKER_HOST` or `PODMAN_HOST`); storefront business config lives in `vendra-store.storefront.*` (env `STOREFRONT_*`). The old `services.storefront.docker.*` block was removed from `config/services.php` and nothing reads it. In tests, use the shared `fakeDockerEngine()` helper defined in `tests/Pest.php`; it registers a stateful SDK transport through Laravel Docker Engine's `ContainerManager`. The container inspect payload must include `Id` and `Name`.
