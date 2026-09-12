# Vendra Estate

Everything needed to run Vendra in production: the Traefik edge, the platform
services, and the optional marketing site. The platform is the source of truth
for them; no external controller is involved.

```
docker/stacks/
  bin/vendra              estate control (host-level)
  proxy/                  Traefik, its static config, and file-provider dynamic config
  platform/               mysql, redis, php, php-api, horizon, storefront-worker, scheduler, pulse
  website/                optional marketing site
```

## Quick start

```sh
cp .env.example .env
cp docker/stacks/.env.example docker/stacks/.env
$EDITOR .env                        # Laravel and application settings
$EDITOR docker/stacks/.env          # estate, images, socket, ports, TLS
docker/stacks/bin/vendra up
```

Configuration is split by ownership, not duplicated. The root `.env` owns Laravel
and application behavior. `docker/stacks/.env` owns host orchestration: domains,
images, the host runtime socket, published ports, database administration, and
estate TLS. Runtime selection and storefront network remain in the root `.env`
because Laravel reads them directly. Compose reads both and explicitly replaces host-only addresses
such as `DB_HOST=127.0.0.1` with service names such as `DB_HOST=mysql` inside
containers. The CLI rejects any key present in both files, preserving exactly one
source of truth per setting.

Set `VENDRA_ENV_FILE` to select a non-default application environment and
`VENDRA_DOCKER_ENV_FILE` to select a non-default estate environment.

`vendra config` is a read-only preflight. It reports both active environment
files, runtime, domain, enabled stacks, and network state without printing
secrets. The CLI parses the few host-side values it needs as dotenv data; it
never executes either file as a shell script. `vendra up` runs the same validation
before changing anything.

`up` creates the network, prepares the state directory, then starts proxy →
platform → website, waiting for each to become healthy.

| Command | |
| --- | --- |
| `vendra config` | validate required values, Compose files, runtime access, and network state |
| `vendra up` | validate config, ensure the network, start every stack |
| `vendra down` | stop every stack |
| `vendra restart` | down then up |
| `vendra ps` | stack services plus the storefront fleet |
| `vendra logs [target]` | follow logs — `proxy`, `website`, a platform service, or a container |
| `vendra artisan <args>` | run artisan inside the platform container |
| `vendra urls` | print the estate's URLs |
| `vendra hosts` | print an `/etc/hosts` block for a local estate |
| `vendra certs` | issue a local certificate with mkcert |

## Local source development

Use development mode when the platform should run from the current checkout
instead of rebuilding an image after every source change:

```sh
docker/stacks/bin/vendra dev config
docker/stacks/bin/vendra dev up
```

The local override builds the lightweight `development` Docker target as
`vendra-platform:dev`. That target supplies only PHP, extensions, FrankenPHP,
and system configuration; it does not copy, install, or build the application.
The complete repository, including the host's `vendor/`, is bind-mounted at
`/app`, so run `composer install` on the host before starting it. Only
container-generated `bootstrap/cache/` remains in a named volume. The override
sets `APP_ENV=local`, `APP_DEBUG=true`, and `LOG_LEVEL=debug`. Production cache
warming is disabled and Laravel's caches are cleared when the web containers
start. Development mode skips the explicit image-pull phase and reuses the local
runtime image after its first build; application source changes never rebuild it.

Normal PHP, Blade, configuration, route, and package source edits are visible
without rebuilding the image. Restart the long-running processes after changing
code they have already loaded:

```sh
docker/stacks/bin/vendra dev restart-workers
```

Run frontend development tooling on the host so Vite writes its hot file into
the mounted checkout:

```sh
npm run dev
```

Use `vendra dev restart` when startup configuration or the container environment
changes. After changing Composer dependencies, run `composer install` or
`composer update` on the host; the containers see the resulting `vendor/` changes
through the same bind mount without rebuilding the platform image.

## Why the CLI is a shell script

The platform runs *inside* the stack it would otherwise be asked to start, so it
cannot bootstrap itself — something on the host has to run the first
`compose up`. Everything after bring-up is an artisan command inside the
container and is reachable through `vendra artisan`.

## Docker or Podman

Both serve the Engine API these stacks and the platform speak, so either runs the
estate. Name the runtime in the root `.env` and its host socket in
`docker/stacks/.env`:

```sh
# .env
CONTAINER_RUNTIME=podman
STOREFRONT_LOG_DRIVER=k8s-file

# docker/stacks/.env
CONTAINER_SOCKET=/run/user/1000/podman/podman.sock   # rootless
```

`bin/vendra` drives `$CONTAINER_RUNTIME compose|network|ps|inspect|logs`, and the
socket is mounted into `storefront-worker` at the Docker path either way, so the
container's `DOCKER_HOST` and `PODMAN_HOST` are both
`unix:///var/run/docker.sock`. Compose maps `CONTAINER_RUNTIME` to the platform's
`CONTAINER_DRIVER`, which selects the matching Laravel Docker Engine driver.

Rootless Podman is the safer choice, and it is the one thing that changes the
security story of this estate: the socket that container holds stops being
root-equivalent on the host. Four things to know before switching:

- Enable the socket: `systemctl --user enable --now podman.socket`, plus
  `loginctl enable-linger <user>` so it survives logout.
- **Logging.** `json-file` and its `max-size`/`max-file` options are Docker's.
  Podman logs through `k8s-file` or journald and rejects options for a driver it
  is not using. Set `STOREFRONT_LOG_DRIVER` accordingly, or empty to inherit.
- **Health checks.** Podman runs a container's `HEALTHCHECK` through transient
  systemd timers. Without systemd the health state never populates, the
  provisioner's gate degrades to "started rather than healthy", and it logs a
  warning each time it does. Deployments still land; you just stop learning that
  a storefront booted broken.
- **Restart on boot.** `unless-stopped` is honored while Podman is running, but
  surviving a reboot needs `podman-restart.service` enabled. Docker's daemon does
  this for free.

## Storefronts are not here

The platform creates, replaces, and health-gates storefront containers itself
through the Engine API — see `App\Services\DockerStorefrontProvisioner`.
There is no compose project per store. A storefront is one container carrying
the `traefik.*` labels Traefik discovers and `io.vendra.*` labels marking it as
platform-owned, so the platform never touches a container it did not place.

`ProvisionStorefrontJob` runs on its own `storefronts` queue, served by the
`storefront-worker` container. That is the only container in the estate holding a
runtime socket, which is root-equivalent on the host under Docker (though not
under rootless Podman — see above). Horizon's supervisor
deliberately does not list that queue. **Do not** widen the worker's queues or
hand the socket to Horizon — that gives every queued job in the system host root.

Useful commands, all through `vendra artisan`:

```sh
vendra artisan storefront:status          # the fleet as the database sees it
vendra artisan storefront:reconcile       # correct whatever has drifted
vendra artisan storefront:retry-failed    # re-provision only the failures
vendra artisan storefront:redeploy        # rebuild everything (an outage)
```

`reconcile` compares each storefront against what the database intends and
applies the narrowest correction — starting a stopped container, stopping one
that should be down, rebuilding only what is missing, unhealthy, or serving the
wrong image. A converged estate comes through it untouched, so it is safe to run
often. Add `--sync` to run it in the foreground and see a per-outcome tally.

`redeploy` is the blunt instrument: it removes and recreates every storefront
meant to be running, each one down for its own pull and health check, one after
another. Reach for it when the change is one convergence cannot see — an image
republished under the same reference, say — and expect an outage.

## Certificates

Traefik owns TLS. Nothing in this repository generates a CA.

**Production** — set `CERT_RESOLVER=letsencrypt` and `ACME_EMAIL`. Traefik obtains
and renews every certificate over the HTTP challenge, storefront domains
included. Ports 80 and 443 must be reachable from the internet, and a customer
domain must resolve here before its certificate can be issued.

One exception: the per-tenant panels at `<slug>.admin.<base>` are matched by a
pattern, not a host, so the HTTP challenge has no single domain to prove. Serving
them over ACME needs a wildcard certificate for `*.admin.<base>`, which requires
the DNS challenge and a provider credential — add a `dnsChallenge` resolver and
`tls.domains` to the `tenant-panels` router in `proxy/dynamic/vendra.yml`. Without
that they fall back to the default certificate.

**Local** — leave `CERT_RESOLVER` empty and run `vendra certs`, which issues a
certificate with mkcert into `<state>/certificates`. Traefik serves it as the
default certificate. Re-run it after adding a store so the new domain is
covered, then `vendra restart`.

Storefronts also make server-side calls to the API. Under mkcert, Node rejects
that certificate and every call fails before it is sent — the page still renders,
so it shows only as empty sections. Set `STOREFRONT_CA_FILE` to have the CA
mounted read-only into each storefront and trusted.

HSTS is off unless `CERT_RESOLVER` is set. Sending it with a certificate the
browser does not trust pins the host to HTTPS *and* removes the "proceed anyway"
bypass, leaving a local `.test` host unreachable until HSTS state is cleared by
hand.

## Local estates

`.test` does not resolve publicly:

```sh
vendra hosts | sudo tee -a /etc/hosts
```

Traefik carries network aliases for `BASE_DOMAIN` and `api.BASE_DOMAIN` on
`traefik-public`. Without them, containers would follow the host's
`/etc/hosts` entries to `127.0.0.1` — which inside a container is the container
itself, so a storefront's server-side call to the API fails with `ECONNREFUSED`
before it leaves the process.

## Exposure

Only ports 80 and 443 are public. MySQL and the Traefik dashboard bind to
loopback, reachable from the host or over `ssh -N -L …`. The dashboard is
unauthenticated — do not widen its address, and do not add a public router for
`api@internal`.
