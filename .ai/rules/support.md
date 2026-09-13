---
paths:
  - 'app/Support/Storefront*.php'
---

# Support

## Read storefront settings through StorefrontDockerConfig, never Config
Every `services.storefront` value is read once by `App\Support\StorefrontDockerConfig::fromConfig()` and injected as an immutable value object. Do not add `Config::get('services.storefront.*')` calls, and do not re-add private string()/integer()/boolean() coercion helpers to consumers — add the field to the VO instead.

It is bound with `bind()`, not `singleton()`, so a config change (tests, reloads) is picked up on the next resolve. Keep it that way.
