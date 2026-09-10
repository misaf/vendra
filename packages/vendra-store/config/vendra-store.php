<?php

declare(strict_types=1);

return [
    /*
     | One store owns one storefront: a single container built from a
     | storefront image, carrying the labels the estate's reverse proxy
     | discovers. Everything here describes *what* should be deployed — how a
     | the runtime is told to do it belongs to `misaf/laravel-docker-engine`,
     | whose selected driver owns the endpoint and API negotiation.
     */
    'storefront' => [
        /*
         | The container network the storefronts join. The platform does not
         | create it: the network, the reverse proxy, and the TLS material belong
         | to whoever runs the estate. Deployment fails with a pointed error when
         | it is absent rather than inventing one the proxy is not attached to.
         */
        'network' => env('STOREFRONT_NETWORK', 'traefik-public'),

        /*
         | Container name prefix. The slug is appended, so a storefront is always
         | addressable by name and a redeploy replaces it in place.
         */
        'name_prefix' => env('STOREFRONT_NAME_PREFIX', 'vendra-storefront-'),

        'port' => (int) env('STOREFRONT_PORT', 3000),
        'health_path' => env('STOREFRONT_HEALTH_PATH', '/api/health'),

        /*
         | Seconds to wait for the container to report healthy before the
         | deployment is recorded as requested rather than ready.
         */
        'health_timeout' => (int) env('STOREFRONT_HEALTH_TIMEOUT', 120),

        // Skip the registry pull so a locally built image can be started.
        'pull' => filter_var(env('STOREFRONT_PULL', true), FILTER_VALIDATE_BOOL),

        /*
         | Log driver for the storefront containers. "json-file" and its rotation
         | options are Docker's; Podman logs through k8s-file or journald. Empty
         | leaves logging to the runtime's own configuration.
         */
        'log_driver' => env('STOREFRONT_LOG_DRIVER', 'json-file') ?: '',
        'log_options' => [
            'max-size' => env('STOREFRONT_LOG_MAX_SIZE', '10m'),
            'max-file' => env('STOREFRONT_LOG_MAX_FILE', '5'),
        ],

        /*
         | Per-storefront resource caps. One noisy storefront on a shared host
         | is how every other storefront on it gets slow, so the fleet is capped
         | by default. Empty or zero lifts a cap, which is what a single-store
         | box wants.
         |
         | The PID cap bounds processes *and* threads. A Next.js server holds a
         | few dozen of both, so 512 is headroom rather than a ceiling — what it
         | stops is a runaway spawning until the host's own pid_max is gone,
         | which no other cap here prevents.
         */
        'cpus' => (float) env('STOREFRONT_CPUS', 0.5),
        'memory_megabytes' => (int) env('STOREFRONT_MEMORY_MB', 512),
        'memory_reservation_megabytes' => (int) env('STOREFRONT_MEMORY_RESERVATION_MB', 0),
        'pids_limit' => (int) env('STOREFRONT_PIDS_LIMIT', 512),

        'base_domain' => env('STOREFRONT_BASE_DOMAIN', '') ?: '',
        'api_url' => env('STOREFRONT_API_URL', '') ?: '',
        'cert_resolver' => env('STOREFRONT_CERT_RESOLVER', '') ?: '',

        /*
         | Read-only certificate bind mount, matching the estate's state dir.
         | Needed only while the API is served with a certificate no public root
         | signs: Node rejects it, and every server-side call the storefront makes
         | fails before it is sent. Empty disables the mount, which is correct
         | under ACME.
         */
        'certificates_path' => env('STOREFRONT_CERTIFICATES_PATH', '') ?: '',
        'ca_file' => env('STOREFRONT_CA_FILE', '') ?: '',

        'storage_base_url' => env('STOREFRONT_STORAGE_BASE_URL', '') ?: '',
        'neshan_service_key' => env('NESHAN_SERVICE_KEY', '') ?: '',
        'traefik_middlewares' => env(
            'STOREFRONT_TRAEFIK_MIDDLEWARES',
            'www-redirect@file,security-headers@file,compression@file',
        ) ?: '',
    ],
];
