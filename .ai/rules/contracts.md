---
paths:
  - app/Contracts/StorefrontProvisioner.php
---

# Contracts

## The provisioner port speaks in typed DTOs
`provision()` takes `StorefrontProvisionRequest` and returns `StorefrontProvisionResult`. Do not widen it back to arrays — the array shape used to push validation of every field onto each caller.

Build the request with `StorefrontProvisionRequest::for($deployment, $config)`, which re-derives slug/domain/theme from the deployment row rather than trusting the stored configuration. `encodedConfiguration()` owns the base64/JSON encoding; jobs must not encode payloads themselves.
