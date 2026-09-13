---
paths:
  - packages/vendra-store/src/Jobs/CompleteStoreProvisioningJob.php
---

# Jobs

## Provisioning retries must remain dispatchable
Do not make CompleteStoreProvisioningJob implement ShouldBeUnique: a stale dispatch lock can suppress recovery and leave a store pending. Queue replacement attempts and serialize their execution per store with WithoutOverlapping; the checkpointed handler is idempotent.
