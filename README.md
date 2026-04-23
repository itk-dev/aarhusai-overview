# AarhusAI Overview

A dashboard application for monitoring and managing
[OpenWebUI](https://github.com/open-webui/open-webui) instances. Syncs models
from multiple OpenWebUI sites into a local database and presents them in a
unified overview.

## Requirements

- Docker and Docker Compose
- A running [Traefik](https://traefik.io/) instance on the `frontend` network
- API keys for each OpenWebUI instance you want to monitor

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/itk-dev/aarhusai-overview.git
   cd aarhusai-overview
   ```

2. Configure environment variables:

   ```bash
   cp .env .env.local
   ```

   Set the required values in `.env.local`:

   | Variable                        | Description                                   |
   |---------------------------------|-----------------------------------------------|
   | `COMPOSE_DOMAIN`                | Local domain for Traefik routing              |
   | `COMPOSE_PROJECT_NAME`          | Docker Compose project name                   |
   | `DATABASE_URL`                  | Doctrine database connection string           |
   | `OPENWEBUI_PRODUCTION_BASE_URL` | Base URL of the production OpenWebUI instance |
   | `OPENWEBUI_PRODUCTION_API_KEY`  | API key for production                        |
   | `OPENWEBUI_TEST_BASE_URL`       | Base URL of the test OpenWebUI instance       |
   | `OPENWEBUI_TEST_API_KEY`        | API key for test                              |

3. Pull Docker images:

   ```bash
   docker compose pull
   ```

4. Start the Docker environment:

   ```bash
   docker compose up -d
   ```

5. Install dependencies:

   ```bash
   docker compose exec phpfpm composer install
   ```

6. Run database migrations:

   ```bash
   docker compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
   ```

7. Build css:

  ```bash
   docker compose exec phpfpm bin/console tailwind:build
   ```

8. Create a user account:

   ```bash
   docker compose exec phpfpm bin/console app:create-user admin@example.com
   ```

   A random password will be generated and printed in the terminal.

8. Access the site at `https://<COMPOSE_DOMAIN>` and log in with the created
   credentials.

## Usage

### Authentication

The dashboard requires login. Users are managed via the CLI:

```bash
# Create a new user (generates and displays a random password)
docker compose exec phpfpm bin/console app:create-user user@example.com
```

### Syncing data

Pull models from all configured OpenWebUI sites:

```bash
docker compose exec phpfpm bin/console app:sync-openwebui
```

Sync a single site:

```bash
docker compose exec phpfpm bin/console app:sync-openwebui --site=production
```

### Dashboard

The web dashboard shows a models overview with:

- Site selector pills for filtering by OpenWebUI instance
- Sortable table columns
- Health check indicators for configured instances

## Development

### Tailwind CSS

Tailwind is managed via
[symfonycasts/tailwind-bundle](https://github.com/symfonycasts/tailwind-bundle).
Asset compilation happens automatically through Symfony AssetMapper.

Build the Tailwind CSS output:

```bash
docker compose exec phpfpm php bin/console tailwind:build
```

Watch for changes during development:

```bash
docker compose exec phpfpm php bin/console tailwind:build --watch
```

### Code standards

```bash
# PHP (Symfony coding standards via php-cs-fixer)
docker compose exec phpfpm vendor/bin/php-cs-fixer fix

# Twig
docker compose exec phpfpm vendor/bin/twig-cs-fixer lint

# Composer validation
docker compose exec phpfpm composer validate --strict
docker compose exec phpfpm composer normalize --dry-run

# Markdown
docker compose run --rm markdownlint markdownlint '**/*.md'

# YAML and CSS (Prettier)
docker compose run --rm prettier '**/*.{yml,yaml}' --check
docker compose run --rm prettier 'assets/**/*.{css,scss}' --check
```

## License

This project is licensed under the [MIT License](LICENSE).
