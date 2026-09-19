---
paths:
  - '**/database/seeders/**'
---

# Seeders

## Seeders call Actions, never Artisan commands
A seeder must not run a command (`$this->command->call(...)`, `Artisan::call`). Commands are operator tools with prompts and CLI-only output; a seeder must also run outside Artisan (tests, `resolve(Seeder::class)->__invoke()`) and under `--no-interaction`. Inject the domain Action into the seeder's constructor and share derived values (e.g. `Support\ConsoleAddress`) instead of reaching into the command. Print only behind `isset($this->command)`, as Laravel's own `Seeder` does. Reference: vendra-console `ConsoleSeeder`.
