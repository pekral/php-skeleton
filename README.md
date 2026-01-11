# PHP Skeleton

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen.svg)](https://phpstan.org/)
[![Pest](https://img.shields.io/badge/Pest-v4-f472b6.svg)](https://pestphp.com/)
[![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg)]()

> 🚀 Modern PHP package skeleton with strict quality rules and best practices baked in.

Start your next PHP package with confidence. This skeleton comes pre-configured with industry-standard tools for static analysis, testing, and code quality — and includes an **interactive installer** that configures everything for you.

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
| **Interactive Installer** | Automatic project configuration |

---

## 📦 Installation

```bash
composer create-project pekral/php-skeleton my-package
```

The interactive installer will guide you through the configuration process.

---

## 🚀 Interactive Installer

When you run `composer create-project`, an interactive installer automatically starts and helps you configure your new package.

### What the Installer Does

1. **Collects Project Information**
   - Package name (vendor/package format)
   - Root PSR-4 namespace
   - Test namespace
   - Display name
   - GitHub repository URL

2. **Optional: Project Specification**
   - Paste multi-line project specification text
   - Creates `SPEC.md` file (automatically added to `.gitignore`)

3. **Performs Automatic Configuration**
   - Updates `composer.json` with your package details
   - Replaces namespaces across all files
   - Updates `README.md` and `LICENSE`
   - Cleans up `phpstan.neon` and `rector.php`
   - Moves dev dependencies to `require-dev`
   - Creates example class and test

4. **Creates Example Files**
   - `src/{ClassName}.php` — Example class with `greet()` method
   - `tests/Unit/{ClassName}Test.php` — Pest test for the example class

5. **Optional Features**
   - **GitHub Actions** — Keep or remove CI/CD workflows
   - **Cursor Rules** — Install AI coding assistant rules
   - **Git Repository** — Initialize with custom branch name
   - **Push to Remote** — Force push initial commit to GitHub

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
   Initialize git repository? [yes]: 
   Branch name [main]: 
   ✓ Git repository initialized
   ✓ Initial commit created

 ✓   Project configured successfully!
```

### Cancellation & Cleanup

If you press `Ctrl+C` during installation, the installer will:
- Display a cancellation message
- Automatically delete the partially created project directory
- Exit cleanly

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

### Apply All Fixes

```bash
composer fix
```

Automatically fixes code style and applies refactoring.

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

## 📁 Project Structure

After installation, your project will have:

```
my-package/
├── .github/
│   └── workflows/
│       └── pr.yml              # GitHub Actions CI workflow
├── docs/                       # Documentation folder
├── src/
│   └── MyPackage.php           # Example class (your namespace)
├── tests/
│   └── Unit/
│       └── MyPackageTest.php   # Example Pest test
├── .gitignore                  # Git ignore rules
├── composer.json               # Dependencies and scripts
├── phpstan.neon                # PHPStan configuration (level max)
├── phpunit.xml                 # PHPUnit/Pest configuration
├── pint.json                   # Laravel Pint rules
├── rector.php                  # Rector configuration
├── ruleset.xml                 # PHP CodeSniffer rules
├── CHANGELOG.md                # Version history (empty)
├── README.md                   # Project readme (empty)
└── LICENSE                     # MIT License
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
