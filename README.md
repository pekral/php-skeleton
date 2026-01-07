<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/pekral/php-skeleton/main/docs/logo-dark.svg">
  <source media="(prefers-color-scheme: light)" srcset="https://raw.githubusercontent.com/pekral/php-skeleton/main/docs/logo-light.svg">
  <img alt="PHP Skeleton" src="https://raw.githubusercontent.com/pekral/php-skeleton/main/docs/logo-light.svg" width="400">
</picture>

# PHP Skeleton

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen.svg)](https://phpstan.org/)
[![Pest](https://img.shields.io/badge/Pest-v4-f472b6.svg)](https://pestphp.com/)
[![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg)]()

> 🚀 Modern PHP package skeleton with strict quality rules and best practices baked in.

Start your next PHP package with confidence. This skeleton comes pre-configured with industry-standard tools for static analysis, testing, and code quality.

---

## ✨ Features

| Tool | Purpose |
|------|---------|
| **[Pest](https://pestphp.com/)** | Elegant testing framework with 100% coverage requirement |
| **[PHPStan](https://phpstan.org/)** | Static analysis at maximum level |
| **[Laravel Pint](https://laravel.com/docs/pint)** | Opinionated PHP code style fixer |
| **[Rector](https://getrector.org/)** | Automated code refactoring |
| **[PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)** | Coding standard enforcement |
| **[Security Advisories](https://github.com/Roave/SecurityAdvisories)** | Dependency vulnerability checking |

---

## 📦 Installation

```bash
composer create-project pekral/php-skeleton my-package
```

Or clone and customize:

```bash
git clone https://github.com/pekral/php-skeleton.git my-package
cd my-package
rm -rf .git
git init
composer install
```

---

## 🛠️ Quick Start

After installation, customize the skeleton for your package:

1. **Update `composer.json`** — Change package name, description, and namespaces
2. **Rename namespaces** — Update `Pekral\Example` to your vendor/package namespace
3. **Clear example files** — Remove `src/Example.php` and `tests/Unit/Example.php`
4. **Start building** — Create your classes in `src/` and tests in `tests/`

---

## 🎯 Available Commands

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
| `composer fix` | Run all auto-fixers |
| `composer security-audit` | Check for vulnerable dependencies |

---

## 📁 Project Structure

```
├── src/                    # Your package source code
├── tests/
│   └── Unit/               # Unit tests
├── composer.json           # Dependencies and scripts
├── phpstan.neon            # PHPStan configuration (level max)
├── phpunit.xml             # PHPUnit/Pest configuration
├── pint.json               # Laravel Pint rules
├── rector.php              # Rector configuration
├── ruleset.xml             # PHP CodeSniffer rules
└── LICENSE                 # MIT License
```

---

## 🔧 Configuration

### PHPStan

Static analysis runs at **maximum level** with additional rules:
- Deprecation rules
- Mockery extension
- PHPUnit extension

```yaml
# phpstan.neon
parameters:
    level: max
```

### Testing

Tests use **Pest** with strict coverage requirements:

```bash
# Runs with 100% minimum coverage
composer test:coverage
```

### Code Style

Laravel Pint enforces PSR-12 with additional rules for clean, consistent code.

---

## 📋 Requirements

- PHP 8.4 or higher
- Composer 2.x

---

## 🤝 Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) before submitting a Pull Request.

---

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## 👤 Author

**Petr Král**

- GitHub: [@pekral](https://github.com/pekral)
- Email: kral.petr.88@gmail.com

---

<p align="center">
  <sub>Built with ❤️ for the PHP community</sub>
</p>

