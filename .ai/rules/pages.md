---
paths:
  - 'packages/*/src/Filament/Resources/Stores/Pages/CreateStore.php'
---

# Pages

## Store creation pages extend CreateStorePage
Both panels' `CreateStore` extend `Misaf\VendraStore\Filament\Pages\CreateStorePage`, which owns the whole flow: provision the store, catch `SubscriptionLimitException` → notify → `Halt`, send administrator credentials, request the storefront.

Subclasses implement only `resolveReseller(array $data): ?SubscriptionSubscriber` — the console reads `reseller_id` from the form and returns null for a platform-owned store; the reseller panel returns the authenticated reseller. Do not copy the flow into a panel: the two pages were byte-identical copies before this.

## Console alone may skip managed storefront creation
The console CreateStore wizard exposes create_storefront (default true) and uses optional storefront field rules; explicit false creates only the store and domain. The reseller CreateStore keeps storefront creation mandatory. Reuse CreateStorePage and StorefrontConfigurationFields rather than branching the domain action.
