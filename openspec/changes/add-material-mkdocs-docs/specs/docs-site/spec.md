# Delta for docs-site

## ADDED Requirements

### Requirement: MkDocs Material scaffold

The project MUST provide a MkDocs site using the Material theme as the official project documentation.

#### Scenario: Local preview

- **WHEN** a contributor runs `mkdocs serve` after installing `requirements-docs.txt`
- **THEN** the site MUST be available at a local URL with navigation tabs for Getting Started, CLI Reference, and Integrations

#### Scenario: Strict build

- **WHEN** a contributor runs `mkdocs build --strict`
- **THEN** the build MUST succeed without broken internal links once all documentation pages contain real content

### Requirement: Site metadata

The documentation site MUST declare project name, repository URL, and canonical site URL for GitHub Pages.

#### Scenario: Repository link

- **WHEN** a reader views any documentation page
- **THEN** the site MUST expose a link to `https://github.com/marceloxp/php-surface`

### Requirement: Build output ignored

Generated static site output MUST NOT be committed to version control.

#### Scenario: Gitignore

- **WHEN** `mkdocs build` creates a `site/` directory
- **THEN** that directory MUST be listed in `.gitignore`

### Requirement: English-only published content

All pages under `docs/` MUST be written in English.

#### Scenario: No i18n

- **WHEN** the site is built and deployed
- **THEN** there MUST be no language switcher and no translated duplicate pages

### Requirement: Home page

The site MUST include a home page describing php-surface purpose, AI-first exploration, and a quick start command.

#### Scenario: First visit

- **WHEN** a reader opens the site root
- **THEN** they MUST see what php-surface does and a link to Getting Started

### Requirement: GitHub Actions deployment

The site MUST be deployed exclusively via a GitHub Actions workflow on push to `main`.

#### Scenario: Automated publish

- **WHEN** documentation files change on `main`
- **THEN** the workflow MUST build with `mkdocs build --strict` and publish to GitHub Pages without manual deploy steps

### Requirement: Material theme features

The MkDocs configuration MUST enable search, code copy buttons, and light/dark palette toggle.

#### Scenario: Code copy

- **WHEN** a reader views a fenced code block with a shell command
- **THEN** the page MUST offer a copy-to-clipboard control

#### Scenario: Search

- **WHEN** a reader uses the search box with a CLI flag name such as `--stats`
- **THEN** relevant documentation pages MUST appear in results
