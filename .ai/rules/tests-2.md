---
paths:
  - 'tests/**'
---

# Tests 2

## Pest tests use it(), bound to the host TestCase
Write every test with `it('...')` — `test()` is not used anywhere. Group related cases with `describe()` when a file grows.
`tests/Pest.php` binds `Tests\TestCase` to `Unit`, `Feature` and `../packages/*/tests`, and `phpunit.xml` registers one suite per package, so package tests run from the project root too: `php artisan test --compact --testsuite=vendra-<name>`.

## Keep unit tests framework-free
Tests under tests/Unit use PHPUnit's default TestCase and must not boot Laravel. Any test needing the application container, config, facades, Eloquent, translations, Filament/Livewire, or the database belongs in tests/Feature. tests/Pest.php binds Tests\TestCase only to Feature paths.

## Pest tests use it(), bound to the host TestCase
Write every test with it(); group related cases with describe(). tests/Pest.php binds Tests\TestCase only to Feature and ../packages/*/tests/Feature. Unit tests keep Pest's default PHPUnit TestCase, while phpunit.xml still registers host and package suites for root-level execution.

## Feature-only Laravel binding is authoritative
Treat the feature-only convention as authoritative: tests/Pest.php binds Tests\TestCase to Feature paths only. Unit paths always use Pest's default PHPUnit TestCase.
