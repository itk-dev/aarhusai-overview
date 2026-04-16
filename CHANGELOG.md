# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Symfony 7.4 project skeleton with Docker Compose setup
- OpenWebUI API sync service supporting multiple sites (production/test)
- CLI command `app:sync-openwebui` for syncing models
- Dashboard with models overview
- Site selector pills for filtering by site
- Health check indicators for configured OpenWebUI instances
- Sortable table columns and expandable detail rows via Stimulus controllers
- Tailwind CSS styling via symfonycasts/tailwind-bundle
- CI workflows for PHP, Twig, YAML, Markdown, and Composer checks
- Form-based login with email and password
- CLI command `app:create-user` for creating users with generated passwords
