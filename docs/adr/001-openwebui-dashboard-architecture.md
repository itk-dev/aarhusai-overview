# 001: OpenWebUI Dashboard Architecture

| Field | Value |
|-------|-------|
| **Created By** | AarhusAI Team |
| **Date** | 2026-03-06 |
| **Decision Maker** | Project owner |
| **Stakeholders** | AarhusAI users and administrators |
| **Status** | Draft |

## Context

AarhusAI needs a web-based overview dashboard that displays data fetched from the OpenWebUI API. The dashboard should give administrators and users visibility into available models, user accounts, and usage analytics across the AarhusAI OpenWebUI instance.

The project already has a Symfony 7.4 skeleton with a Docker Compose-based development environment (PHP 8.4, MariaDB, Nginx, Traefik).

### Drivers

- **Functional:** Display models, users, and usage analytics from OpenWebUI. Provide a single overview page with clear data presentation. Support future addition of charts and filtering.
- **Non-functional:** Fast page loads via caching. Maintainable codebase using standard Symfony patterns. Secure handling of API credentials. Deployable via the existing Docker/Traefik infrastructure.

### Options Considered

1. **Symfony with server-rendered Twig templates**: Use the existing Symfony project with Twig for rendering, Symfony HttpClient for API calls, and AssetMapper for frontend assets. This is the natural fit given the existing project setup and team expertise.

No other frameworks or approaches were evaluated. Symfony was the established choice from the start given the existing project skeleton and infrastructure.

## Decision

Build the dashboard as a Symfony 7.4 application with server-rendered Twig templates, using a phased approach:

### Phase 1: Foundation and Core Data

**Dependencies to install:**

- `symfony/http-client` — HTTP client for OpenWebUI API calls
- `symfony/twig-bundle` — Twig templating
- `symfony/asset-mapper` — Modern frontend asset handling (CSS, JS)

**Backend architecture:**

- `App\Service\OpenWebUiClient` — Service wrapping all OpenWebUI API calls, returning typed DTOs. Configured via environment variables. Responses cached using `symfony/cache`.
- `App\Controller\DashboardController` — Serves the overview dashboard page.

**OpenWebUI API endpoints consumed:**

| Endpoint | Purpose |
|----------|---------|
| `GET /v1/models` | List available models with metadata |
| `GET /api/users` | List users and counts |
| `GET /api/v1/analytics/summary` | Token usage per model, message counts |

**Configuration (environment variables):**

- `OPENWEBUI_BASE_URL` — Base URL of the OpenWebUI instance
- `OPENWEBUI_API_KEY` — API key for authentication (Bearer token, sk-... format)

**Frontend:**

- Twig templates with a dashboard layout containing sections for models, users, and usage stats
- Symfony UX / Turbo for dynamic partial updates without a full JS framework
- Tailwind CSS via AssetMapper for styling

### Phase 2: Visualization and Filtering

- Charts for token usage trends over time (Chart.js via Symfony UX)
- Model comparison views (usage frequency, token consumption)
- User activity summaries
- Filterable date ranges

### Phase 3: Polish and Operations

- Dashboard authentication via Symfony Security
- Scheduled data fetching via Symfony Messenger/Scheduler to store and query historical data
- API rate limit handling and error resilience
- Deployment configuration for the existing Docker/Traefik setup

## Consequences

### Positive

- Leverages the existing Symfony skeleton and Docker infrastructure with no additional tooling
- Server-rendered Twig keeps the frontend simple and avoids the complexity of a separate SPA
- Symfony HttpClient with scoped clients and caching provides a clean, performant API integration layer
- AssetMapper avoids the need for Node.js/npm in the build pipeline
- Phased approach delivers a working dashboard early and allows iterative improvement

### Negative / Trade-offs

- Server-rendered pages are less interactive than a full SPA (mitigated by Turbo for partial updates)
- Dashboard data freshness depends on cache TTL configuration; real-time updates would require WebSockets or polling
- The dashboard is tightly coupled to OpenWebUI's API structure; API changes upstream will require updates

### Follow-up Actions

- [ ] Install Phase 1 dependencies (`http-client`, `twig-bundle`, `asset-mapper`)
- [ ] Create `OpenWebUiClient` service with `/v1/models` as initial proof of concept
- [ ] Build minimal dashboard page displaying the model list
- [ ] Configure environment variables for OpenWebUI connection
- [ ] Verify OpenWebUI API access and available endpoints against a running instance
