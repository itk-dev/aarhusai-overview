# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AarhusAI Overview — a Symfony 7.4 project (PHP 8.4) by ITK Dev. Currently in early stage with just the skeleton in place.

## Development Environment

The project runs entirely via Docker Compose with Traefik for routing. Required env vars `COMPOSE_DOMAIN` and `COMPOSE_PROJECT_NAME` are set in `.env`.

```shell
# Start services (mariadb, phpfpm, nginx, mail)
docker compose up -d

# Install dependencies
docker compose exec phpfpm composer install

# Run Symfony console commands
docker compose exec phpfpm bin/console <command>
```

Docker compose files: `docker-compose.yml` (local dev), `docker-compose.dev.yml` (staging with basic auth), `docker-compose.server.yml` (production), `docker-compose.redirect.yml` (www redirect).

## Linting & Code Quality

All checks run inside Docker containers. CI runs these on PRs and pushes to `main`/`develop`.

```shell
# PHP coding standards (Symfony rules via php-cs-fixer)
docker compose exec phpfpm vendor/bin/php-cs-fixer fix
docker compose exec phpfpm vendor/bin/php-cs-fixer fix --dry-run --diff

# Twig linting
docker compose exec phpfpm vendor/bin/twig-cs-fixer lint

# Composer validation and normalization
docker compose exec phpfpm composer validate --strict
docker compose exec phpfpm composer normalize --dry-run

# Markdown linting
docker compose --profile dev exec markdownlint markdownlint '**/*.md'

# YAML and styles (Prettier)
docker compose --profile dev exec prettier '**/*.{yml,yaml}' --check
docker compose --profile dev exec prettier 'assets/**/*.{css,scss}' --check
```

## CI Workflows

GitHub Actions workflows in `.github/workflows/` — do **not** edit these directly; changes go via PRs to [itk-dev/devops_itkdev-docker](https://github.com/itk-dev/devops_itkdev-docker).

PRs must also update `CHANGELOG.md` (enforced by CI).

## Code Style

- PHP: Symfony coding standards (`@Symfony` ruleset in `.php-cs-fixer.dist.php`)
- Indentation: 4 spaces (2 spaces for compose YAML files), LF line endings
- Symfony YAML config: 4 spaces indent, single quotes (configured in `.prettierrc.yaml`)
- Composer packages are auto-sorted (`sort-packages: true`)

## Architecture

Standard Symfony structure with autowiring and autoconfiguration enabled. App namespace is `App\` mapped to `src/`. Services are auto-registered from `src/` in `config/services.yaml`.
