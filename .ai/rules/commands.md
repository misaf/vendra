---
paths:
  - 'packages/**/src/Console/Commands/*'
---

# Commands

## Do not override run() in console commands
Never declare a private `run()` helper inside a command class: `Illuminate\Console\Command::run()` is public, so a private `run()` triggers an "Access level must be public" fatal at discover/boot. Name private helpers like `perform()` instead (see StorefrontLifecycleCommand).
