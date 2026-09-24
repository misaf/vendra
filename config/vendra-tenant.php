<?php

declare(strict_types=1);

use Illuminate\Support\Uri;
use Misaf\VendraStore\Models\Store;

return [
    /*
     | Vendra's tenant is the Store. `misaf/vendra-tenant` is a generic engine
     | and names no business model of its own — this is the one place the
     | ecommerce Store is wired into it. Another application built on the same
     | engine points this at its own aggregate (`Company`, `Workspace`,
     | `Organization`), which only has to be an Eloquent model implementing
     | `Misaf\VendraTenant\Contracts\TenantContract`.
     */
    'model' => Store::class,

    /*
     | The foreign key tenant-scoped tables carry. Reusable, tenant-aware
     | packages share this one neutral column — `products.tenant_id`,
     | `blog_posts.tenant_id`, `roles.tenant_id` — so they keep working under any
     | tenant model. Only records describing the Store itself (its domains, its
     | storefront deployment) name it outright with `store_id`, and those belong
     | to `misaf/vendra-store` rather than to the tenancy mechanism.
     */
    'foreign_key' => 'tenant_id',

    /*
     | The platform's own host, taken from APP_URL. The platform surfaces live
     | beneath it: `console.`, `reseller.`, `api.`, and each store's
     | administration at `<store slug>.admin.<central host>`.
     */
    'central_host' => Uri::of((string) env('APP_URL', 'http://localhost'))->host(),
];
