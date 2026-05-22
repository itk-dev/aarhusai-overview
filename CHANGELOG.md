# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

- [PR-10](https://github.com/itk-dev/aarhusai-overview/pull/10)
  - Ability to search by name.
- [PR-9](https://github.com/itk-dev/aarhusai-overview/pull/9)
    - Ability to sort by column.
- [PR-7](https://github.com/itk-dev/aarhusai-overview/pull/7)
  - Added pagination to the models fetching logic.
- [PR-6](https://github.com/itk-dev/aarhusai-overview/pull/6)
  - Made the pair `(external_id, site)` primary key for models
    as the same external id can be used across multiple sites.
- [PR-5](https://github.com/itk-dev/aarhusai-overview/pull/5)
  - Removed basic auth since we have normal login.
- [PR-3](https://github.com/itk-dev/aarhusai-overview/pull/3)
  - Modern dashboard frontend with hand-written CSS (Geist + Geist Mono),
    sticky translucent masthead, segmented site tabs, animated health pings,
    and a centered card login.
  - Live sort-status indicator that reflects the active column and direction.
- [PR-2](https://github.com/itk-dev/aarhusai-overview/pull/2)
  - Form-based login with email and password.
  - CLI command `app:create-user` for creating users with generated passwords.
- [PR-1](https://github.com/itk-dev/aarhusai-overview/pull/1)
  - Symfony 7.4 project skeleton with Docker Compose setup.
  - OpenWebUI API sync service supporting multiple sites (production/staging/dev).
  - CLI command `app:sync-openwebui` for syncing models.
  - Dashboard with models overview.
  - Site selector pills for filtering by site.
  - Health check indicators for configured OpenWebUI instances.
  - Sortable table columns and expandable detail rows via Stimulus controllers.
  - CI workflows for PHP, Twig, YAML, Markdown, and Composer checks.
