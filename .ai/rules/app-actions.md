---
paths:
  - 'app/Actions/**'
---

# App Actions

## Action classes expose a single execute()
An Action is `final class <Verb><Noun>Action` with one public `execute(...)` method — never `handle()` or `__invoke()`. Dependencies come through constructor promotion; validation that belongs to the operation runs inside the action via `Validator::make`.
Add `Spatie\QueueableAction\QueueableAction` when the action must also run on the queue.
