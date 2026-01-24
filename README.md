# PHP Skeleton

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen.svg)](https://phpstan.org/)
[![Pest](https://img.shields.io/badge/Pest-v4-f472b6.svg)](https://pestphp.com/)
[![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg)](https://github.com/pekral/php-skeleton)

Modern PHP package skeleton with strict quality rules and best practices baked in.

Start your next PHP package with confidence. This skeleton comes pre-configured with industry-standard tools for static analysis, testing, and code quality — and includes an **interactive installer** that configures everything for you.

---

## Features

| Tool | Purpose |
|------|---------|
| **[Pest](https://pestphp.com/)** | Elegant testing framework with 100% coverage requirement |
| **[PHPStan](https://phpstan.org/)** | Static analysis at maximum level |
| **[Laravel Pint](https://laravel.com/docs/pint)** | Opinionated PHP code style fixer |
| **[Rector](https://getrector.org/)** | Automated code refactoring |
| **[PHP CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer)** | Coding standard enforcement |
| **[Security Advisories](https://github.com/Roave/SecurityAdvisories)** | Dependency vulnerability checking |
| **Interactive Installer** | Automatic project configuration |

---

## Installation

```bash
composer create-project pekral/php-skeleton my-package
```

The interactive installer will guide you through the configuration process.

---

## Interactive Installer

When you run `composer create-project`, an interactive installer automatically starts and helps you configure your new package.

### What the Installer Does

1. **Collects Project Information**
   - Package name (vendor/package format)
   - Root PSR-4 namespace (auto-suggested from package name)
   - Test namespace (auto-suggested as `{Namespace}Test`)
   - Display name (auto-suggested from package name)
   - GitHub repository URL (auto-suggested from package name)

2. **Optional: Project Specification**
   - Paste multi-line project specification text
   - Creates `SPEC.md` file (automatically added to `.gitignore`)
   - End input with two empty lines or skip with Enter

3. **Performs Automatic Configuration**
   - Updates `composer.json` with your package details
   - Replaces namespaces across all PHP files
   - Clears `README.md` and `CHANGELOG.md` for your content
   - Updates `LICENSE` with your information
   - Cleans up `phpstan.neon` (removes build-package path)
   - Cleans up `rector.php` (removes build-package path)
   - Moves all dependencies to `require-dev` (except PHP)
   - Removes skeleton-specific scripts and autoload entries

4. **Creates Example Files**
   - `src/{ClassName}.php` — Example class with `greet()` method
   - `tests/Unit/{ClassName}Test.php` — Pest test for the example class

5. **Runs Quality Pipeline**
   - Installs dependencies with `composer update`
   - Runs `composer fix` to apply code style fixes
   - Runs `composer check` to verify all quality checks pass

6. **Optional Features**
   - **GitHub Actions** — Keep or remove CI/CD workflows
   - **Cursor Rules** — Install AI coding assistant rules via `pekral/cursor-rules`
   - **Git Repository** — Initialize with custom branch name
   - **Push to Remote** — Force push initial commit to GitHub (converts HTTPS to SSH URL)

### Installer Flow Example

```
 ⚡   PHP Skeleton
 Project Configuration

📝  Package Configuration
╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌
   Package name (vendor/package): acme/my-package
   Root namespace [Acme\MyPackage]: 
   Test namespace [Acme\MyPackageTest]: 
   Display name [My Package]: 
   GitHub URL [https://github.com/acme/my-package]: 

📋 Review Configuration
╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌
   GitHub          https://github.com/acme/my-package
   Name            My Package
   Namespace       Acme\MyPackage
   Package         acme/my-package
   Tests           Acme\MyPackageTest

   Proceed? [yes]: 

⚡  Processing Files
   ✓ Updated: composer.json
   ✓ Updated: README.md
   ✓ Created src/MyPackage.php
   ✓ Created tests/Unit/MyPackageTest.php

🚀  GitHub Actions
   Install GitHub Actions? [yes]: 

📋  Cursor Rules
   Install cursor rules? [yes]: 

📦  Git Repository
   Initialize git? [yes]: 
   Default branch [main]: 
   ✓ Initialized with initial commit

 ✓   Done!
     Your project is ready.
```

### Cancellation & Cleanup

If you press `Ctrl+C` during installation, the installer will:
- Display a cancellation message
- Automatically delete the partially created project directory
- Exit cleanly

---

## Available Commands

### Run All Quality Checks

```bash
composer check
```

This runs the complete quality pipeline:
- Composer normalize
- PHP CodeSniffer
- Laravel Pint
- Rector
- PHPStan
- Security audit
- Tests with 100% coverage

### Apply All Fixes

```bash
composer fix
```

Automatically fixes code style and applies refactoring:
- Composer normalize
- Rector refactoring
- Laravel Pint formatting
- PHP CodeSniffer fixes

### Individual Commands

| Command | Description |
|---------|-------------|
| `composer test:coverage` | Run tests with coverage (min 100%) |
| `composer analyse` | Run PHPStan static analysis |
| `composer pint-check` | Check code style |
| `composer pint-fix` | Fix code style |
| `composer rector-check` | Check for refactoring opportunities |
| `composer rector-fix` | Apply automated refactoring |
| `composer phpcs-check` | Check coding standards |
| `composer phpcs-fix` | Fix coding standard violations |
| `composer composer-normalize-check` | Check composer.json normalization |
| `composer composer-normalize-fix` | Normalize composer.json |
| `composer security-audit` | Check for vulnerable dependencies |

---

## Project Structure

After installation, your project will have:

```
my-package/
├── .github/
│   └── workflows/
│       ├── pr.yml                 # Pull request quality checks
│       ├── composer-update.yml    # Automated dependency updates
│       ├── release.yml            # Release automation
│       ├── security.yml           # Security scanning
│       ├── stale.yml              # Stale issue management
│       └── update-changelog.yml   # Changelog automation
├── docs/                          # Documentation folder
├── src/
│   └── MyPackage.php              # Example class (your namespace)
├── tests/
│   └── Unit/
│       └── MyPackageTest.php      # Example Pest test
├── .gitignore                     # Git ignore rules
├── composer.json                  # Dependencies and scripts
├── phpstan.neon                   # PHPStan configuration (level max)
├── phpunit.xml                    # PHPUnit/Pest configuration
├── pint.json                      # Laravel Pint rules
├── rector.php                     # Rector configuration
├── ruleset.xml                    # PHP CodeSniffer rules
├── CHANGELOG.md                   # Version history (empty)
├── README.md                      # Project readme (empty)
└── LICENSE                        # MIT License
```

---

## Configuration

### PHPStan

Static analysis runs at **maximum level** with additional rules:
- Deprecation rules
- Mockery extension

```yaml
# phpstan.neon
parameters:
    level: max
    treatPhpDocTypesAsCertain: false
```

### Testing

Tests use **Pest v4** with strict coverage requirements:

```bash
# Runs with 100% minimum coverage using PCOV
composer test:coverage
```

### Code Style

Laravel Pint enforces PSR-12 with additional rules for clean, consistent code.

### Rector

Automated refactoring uses rules from `pekral/rector-rules` package.

---

## GitHub Actions

The skeleton includes comprehensive CI/CD workflows:

| Workflow | Purpose |
|----------|---------|
| `pr.yml` | Runs all quality checks on pull requests and pushes to master |
| `composer-update.yml` | Automated dependency updates |
| `release.yml` | Release automation |
| `security.yml` | Security vulnerability scanning |
| `stale.yml` | Marks and closes stale issues/PRs |
| `update-changelog.yml` | Automated changelog updates |

All workflows run on PHP 8.4 with matrix support for additional versions.

---

## Requirements

- PHP 8.4 or higher
- Composer 2.x

---

## Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) before submitting a Pull Request.

---

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## Author

**Petr Král**

- GitHub: [@pekral](https://github.com/pekral)
- Email: kral.petr.88@gmail.com
- X (Twitter): https://x.com/kral_petr_88

---

<p align="center">
  <sub>Built with care for the PHP community</sub>
</p>
