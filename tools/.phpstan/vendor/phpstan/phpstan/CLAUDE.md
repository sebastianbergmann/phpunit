# PHPStan - PHP Static Analysis Tool

## Project Overview

PHPStan finds errors in PHP code without running it. It catches bugs before tests are written, moving PHP closer to compiled languages. This is the **distribution repository** — the compiled PHAR and supporting infrastructure. The actual source code lives at [phpstan/phpstan-src](https://github.com/phpstan/phpstan-src).

- **Website:** https://phpstan.org/
- **Documentation:** https://phpstan.org/user-guide/getting-started
- **API Reference:** https://apiref.phpstan.org/

## Repository Structure

```
├── phpstan                    # CLI entry point (shell script loading the PHAR)
├── phpstan.phar               # Compiled PHAR archive (~26 MB)
├── phpstan.phar.asc           # GPG signature for the PHAR
├── bootstrap.php              # PHAR autoloader with PHP version polyfills
├── composer.json              # Package definition (requires PHP ^7.4|^8.0)
├── .phar-checksum             # MD5 + SHA1 checksums for reproducible builds
├── conf/
│   └── bleedingEdge.neon      # Bleeding edge configuration profile
├── e2e/                       # End-to-end tests (~67 test scenarios)
├── docker/                    # Dockerfiles for PHP 8.0–8.4
├── identifier-extractor/      # Tool to extract error identifiers from rule source code
├── playground-api/            # AWS Lambda API for the online playground (TypeScript)
├── playground-runner/         # AWS Lambda runner for the playground (PHP/Bref)
├── website/                   # phpstan.org static site (Eleventy + Vite + TailwindCSS)
└── .github/workflows/         # CI/CD workflows
```

## Key Concepts

### Distribution Model

This repository distributes a pre-built PHAR archive. The source code is in [phpstan/phpstan-src](https://github.com/phpstan/phpstan-src), which is on PHP 8.1+ internally but the PHAR build is downgraded to support PHP 7.4+. The `phpstan` script loads the PHAR and delegates to the bundled binary.

### PHP Version Support

- **This distribution package:** PHP ^7.4|^8.0 (defined in composer.json)
- **phpstan-src internally:** PHP 8.1+ (downgraded during PHAR build)
- **Other extension repositories** (phpstan-strict-rules, phpstan-doctrine, phpstan-symfony, etc.): still support PHP 7.4+
- **E2E tests run on:** PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4, 8.5 (Linux + Windows)

### Bleeding Edge

The `conf/bleedingEdge.neon` configuration enables experimental/strict checks before they become defaults. Users opt in by including this config.

### Error Identifiers

Every PHPStan error has a unique identifier (e.g., `argument.type`, `deadCode.unreachable`). The `identifier-extractor/` tool scans all PHPStan repositories to extract these identifiers, producing JSON used to generate documentation on the website.

## Development


### CI Workflows

Key workflows in `.github/workflows/`:

- **`tests.yml`** — Runs e2e tests on PHP 7.4–8.5, Linux + Windows. Triggered on changes to `e2e/**`, `phpstan`, `.phar-checksum`, `bootstrap.php`.
- **`other-tests.yml`** — Additional integration tests (PHP-Parser, React Promise, etc.)
- **`integration-tests.yml`** — Tests against major projects (Rector, Larastan, Carbon, etc.)
- **`extension-tests.yml`** — Tests 1st-party PHPStan extensions (PHPUnit, Doctrine, Symfony, etc.)
- **`release.yml`** — Creates GitHub release on tag push, uploads `phpstan.phar` and signature
- **`docker-stable.yml`** / **`docker-nightly.yml`** — Builds multi-arch Docker images (arm64 + amd64) pushed to ghcr.io
- **`extract-identifiers.yml`** — Extracts error identifiers from all PHPStan repos
- **`website.yml`** — Builds and deploys phpstan.org
- **`generate-error-docs.lock.yml`** — Generates error documentation using Claude

### Current Branch

The main development branch for this repository is `2.2.x`.

## Website

The website (phpstan.org) lives in `website/` and has its own `website/CLAUDE.md` with detailed instructions. Key points:

- Built with Eleventy (11ty) + Vite + TailwindCSS
- Two-stage build: `npm run build:11ty` then `npm run build:vite`
- Deployed to AWS S3 + CloudFront
- Error documentation in `website/errors/` is auto-generated (see `website/errors/CLAUDE.md`)

## Playground

The online playground at https://phpstan.org/try consists of two AWS Lambda services:

- **playground-api/** — TypeScript API that orchestrates analysis across multiple PHP versions
- **playground-runner/** — PHP Lambda (via Bref) that executes PHPStan against submitted code

Both are deployed via the Serverless Framework to eu-west-1.

## Docker

Dockerfiles in `docker/` build images for PHP 8.0–8.4, based on `php:*-cli-alpine`. Images are published to `ghcr.io` as multi-architecture (arm64 + amd64). The images install PHPStan via Composer and use `phpstan` as the entrypoint.

## Related Repositories

- [phpstan/phpstan-src](https://github.com/phpstan/phpstan-src) — Main source code (PHP 8.1+, downgraded for PHAR)
- [phpstan/phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) — Additional strict rules
- [phpstan/phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) — Deprecation detection
- [phpstan/phpstan-doctrine](https://github.com/phpstan/phpstan-doctrine) — Doctrine integration
- [phpstan/phpstan-symfony](https://github.com/phpstan/phpstan-symfony) — Symfony integration
- [phpstan/phpstan-phpunit](https://github.com/phpstan/phpstan-phpunit) — PHPUnit integration
- [phpstan/phpstan-nette](https://github.com/phpstan/phpstan-nette) — Nette integration
- [phpstan/phpstan-webmozart-assert](https://github.com/phpstan/phpstan-webmozart-assert) — Webmozart Assert integration
- [phpstan/phpdoc-parser](https://github.com/phpstan/phpdoc-parser) — PHPDoc parser library

Extension repositories support PHP 7.4+ and some support multiple versions of the libraries they analyse.

## Making Changes

- **Source code changes** belong in [phpstan/phpstan-src](https://github.com/phpstan/phpstan-src), not here
- **E2E tests** can be added or modified in `e2e/`
- **Website content** is in `website/src/` (see `website/CLAUDE.md`)
- **Error documentation** is in `website/errors/` (see `website/errors/CLAUDE.md`)
- Configuration uses NEON format (Nette Object Notation), similar to YAML
