# Deployment: building Docker images locally

This app uses a **multi-stage** [`Dockerfile`](Dockerfile):

| Stage / target     | Role                                                               |
| ------------------ | ------------------------------------------------------------------ |
| `node-builder`     | `npm ci` + `vite build` (needs `VITE_*` build args for production) |
| `composer-builder` | `composer install --no-dev`                                        |
| `nginx`            | Serves `public/` + built assets; proxies PHP to the `app` service  |
| `app`              | PHP 8.4-FPM (default final image if you do not pass `--target`)    |

`VITE_REVERB_*` values are **compiled into the JavaScript bundle** at image build time. They must match the public Reverb URL users will use in the browser (same values you would put in production `.env` for `VITE_REVERB_*`). Runtime `.env` on the server does **not** change already-built assets inside the image.

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/) with BuildKit enabled (default on current Docker Desktop).
- Repository root as build context (where the `Dockerfile` lives).

## Production image build (recommended)

From the project root, pass **all four** Reverb-related build arguments (use your real app key and host):

```bash
docker build \
  --build-arg VITE_REVERB_APP_KEY="pk_your_public_key" \
  --build-arg VITE_REVERB_HOST="poker.example.com" \
  --build-arg VITE_REVERB_PORT="443" \
  --build-arg VITE_REVERB_SCHEME="https" \
  -t livewire-planning-poker:latest \
  .
```

This produces the **default** final stage (`app`, PHP-FPM). To tag the **Nginx** image separately (e.g. if you split web and app in Compose):

```bash
docker build \
  --target nginx \
  --build-arg VITE_REVERB_APP_KEY="pk_your_public_key" \
  --build-arg VITE_REVERB_HOST="poker.example.com" \
  --build-arg VITE_REVERB_PORT="443" \
  --build-arg VITE_REVERB_SCHEME="https" \
  -t livewire-planning-poker-web:latest \
  .
```

`--build-arg` is required for **every** build that includes the `node-builder` stage (both `app` and `nginx` targets use it).

## Using a local env file for build args

`docker build` does not read a `.env` file for build args by itself. Options:

**1. Export variables then build**

Create a file (e.g. `.env.docker-build`, **gitignored**) with only:

```env
VITE_REVERB_APP_KEY=pk_...
VITE_REVERB_HOST=poker.example.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

Then:

```bash
set -a
source .env.docker-build
set +a

docker build \
  --build-arg VITE_REVERB_APP_KEY \
  --build-arg VITE_REVERB_HOST \
  --build-arg VITE_REVERB_PORT \
  --build-arg VITE_REVERB_SCHEME \
  -t livewire-planning-poker:latest \
  .
```

Omitting the `=` value after `--build-arg NAME` tells Docker to take the value from the **current shell environment**.

**2. Docker Compose**

Use `build.args` with `${VITE_REVERB_HOST}` etc., and run `docker compose --env-file .env.docker-build build`. See [Compose Build specification](https://docs.docker.com/compose/compose-file/build/).

Keep build-time files limited to **`VITE_*`** (public client config). Do not put database passwords or `REVERB_APP_SECRET` into a file used only for frontend build unless you also use that file for something else—prefer separate files.

## Apple Silicon → Linux server

If the server runs **amd64** Linux and your Mac is **arm64**, build for the server platform:

```bash
docker build \
  --platform linux/amd64 \
  --build-arg VITE_REVERB_APP_KEY="pk_..." \
  --build-arg VITE_REVERB_HOST="poker.example.com" \
  --build-arg VITE_REVERB_PORT="443" \
  --build-arg VITE_REVERB_SCHEME="https" \
  -t livewire-planning-poker:latest \
  .
```

## GitHub Container Registry (ghcr.io)

Images for this project are published to **GitHub Packages** ([`ghcr.io`](https://docs.github.com/packages/working-with-a-github-packages-registry/working-with-the-container-registry)). You need two images if you run Nginx and PHP-FPM as separate services (see targets above): one for **`app`** (default Dockerfile stage) and one for **`nginx`**.

Replace `OWNER` with your GitHub user or organization name. Image names below match the local tags from [`build-docker-images.sh`](build-docker-images.sh) (`livewire-planning-poker` and `livewire-planning-poker-web`).

### Login (machine that pushes: your laptop or CI)

Create a [Personal Access Token](https://github.com/settings/tokens) with **`write:packages`** (and `read:packages` if you only pull). For organization packages, authorize the token for the org if required.

```bash
echo YOUR_GITHUB_PAT | docker login ghcr.io -u YOUR_GITHUB_USERNAME --password-stdin
```

In **GitHub Actions**, the default `GITHUB_TOKEN` can push to ghcr if the workflow has `permissions: packages: write` (and the job uses the correct `registry` login action or `docker login` with `GITHUB_TOKEN`).

### Tag and push (after local `docker build`)

```bash
# PHP-FPM (default / final stage)
docker tag livewire-planning-poker:latest ghcr.io/OWNER/livewire-planning-poker:latest

# Nginx
docker tag livewire-planning-poker-web:latest ghcr.io/OWNER/livewire-planning-poker-web:latest

docker push ghcr.io/OWNER/livewire-planning-poker:latest
docker push ghcr.io/OWNER/livewire-planning-poker-web:latest
```

Use a version tag (e.g. `v1.0.0` or git SHA) instead of or in addition to `latest` for reproducible deploys.

Packages appear under the repository **Packages** sidebar on GitHub. Set **package visibility** (public / private) under the package **Settings** on GitHub.

### Pull on the production server

Use a PAT with at least **`read:packages`** (same user/org as the package owner):

```bash
echo YOUR_GITHUB_PAT | docker login ghcr.io -u YOUR_GITHUB_USERNAME --password-stdin

docker pull ghcr.io/OWNER/livewire-planning-poker:latest
docker pull ghcr.io/OWNER/livewire-planning-poker-web:latest
```

Private packages require login on every host that pulls.

### Other registries

For a generic registry host, tag and push the same way with your registry hostname:

```bash
docker tag livewire-planning-poker:latest your-registry.example.com/planning-poker:latest
docker push your-registry.example.com/planning-poker:latest
```

## Runtime configuration

PHP/Laravel settings (`APP_KEY`, `DB_*`, `REVERB_APP_SECRET`, Redis, etc.) are **not** set by the Node build args. Provide them at runtime (Compose `env_file`, Docker secrets, or orchestrator env) on the server. Nginx in this repo expects the PHP-FPM upstream hostname **`app`** on port **9000** (see [`.docker/nginx.conf`](.docker/nginx.conf)).

## CI

In GitHub Actions (or similar), inject the same four `VITE_REVERB_*` values from **repository variables** or **secrets**, then run `docker build` with `--build-arg` as above. Avoid committing production `.env` files.
