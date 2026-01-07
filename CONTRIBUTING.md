# Contributing to PHP Skeleton

First off, thank you for considering contributing! 🎉

This document provides guidelines for contributing to PHP Skeleton. Following these guidelines helps maintain code quality and makes the contribution process smooth for everyone.

---

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Workflow](#development-workflow)
- [Pull Request Process](#pull-request-process)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)

---

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to:

- Be respectful and inclusive
- Accept constructive criticism gracefully
- Focus on what's best for the community
- Show empathy towards others

---

## Getting Started

### Prerequisites

- PHP 8.4 or higher
- Composer 2.x
- Git

### Setup

1. Fork the repository on GitHub

2. Clone your fork locally:
   ```bash
   git clone https://github.com/YOUR-USERNAME/php-skeleton.git
   cd php-skeleton
   ```

3. Install dependencies:
   ```bash
   composer install
   ```

4. Create a branch for your changes:
   ```bash
   git checkout -b feature/your-feature-name
   ```

---

## Development Workflow

### Before Making Changes

1. **Pull latest changes** from upstream:
   ```bash
   git fetch origin
   git rebase origin/main
   ```

2. **Run the full check** to ensure everything works:
   ```bash
   composer check
   ```

### Making Changes

1. Write your code following our [coding standards](#coding-standards)
2. Add tests for new functionality
3. Ensure all tests pass with 100% coverage
4. Run quality checks before committing

### Quality Checks

Run all checks before submitting:

```bash
# Run everything
composer check

# Or run individually:
composer analyse          # PHPStan
composer pint-check       # Code style
composer phpcs-check      # Coding standards
composer rector-check     # Refactoring opportunities
composer test:coverage    # Tests with coverage
```

### Auto-fix Issues

Many issues can be fixed automatically:

```bash
composer fix
```

---

## Pull Request Process

1. **Update documentation** if you're changing functionality
2. **Ensure all checks pass** — PRs with failing checks won't be merged
3. **Write a clear PR description** explaining:
   - What changes you made
   - Why you made them
   - Any breaking changes
4. **Link related issues** using keywords like "Fixes #123"
5. **Request review** from maintainers

### PR Title Format

Use conventional commit format:

```
feat: add new feature
fix: resolve bug in X
docs: update README
refactor: improve code structure
test: add missing tests
chore: update dependencies
```

### Review Process

- At least one maintainer approval required
- All conversations must be resolved
- All CI checks must pass

---

## Coding Standards

### PHP Style

- Follow **PSR-12** coding standard
- Use **strict types** in all files:
  ```php
  <?php

  declare(strict_types=1);
  ```
- Mark classes as `final` unless designed for extension
- Use **typed properties** and return types
- Prefer **constructor property promotion**

### Naming Conventions

| Element | Convention | Example |
|---------|------------|---------|
| Classes | PascalCase | `UserService` |
| Methods | camelCase | `getUserById` |
| Properties | camelCase | `$firstName` |
| Constants | UPPER_SNAKE | `MAX_RETRIES` |

### Code Quality

- Single responsibility per class/method
- No magic numbers — use constants
- Avoid deep nesting — extract methods
- Write self-documenting code
- Only add PHPDoc when it provides additional value

---

## Testing

### Writing Tests

- Use **Pest** syntax
- Follow **Arrange-Act-Assert** pattern
- Test edge cases and error conditions
- Use descriptive test names

```php
it('returns true when condition is met', function (): void {
    // Arrange
    $service = new MyService();

    // Act
    $result = $service->check();

    // Assert
    expect($result)->toBeTrue();
});
```

### Coverage Requirements

- **100% code coverage** is required
- Run coverage locally before submitting:
  ```bash
  composer test:coverage
  ```

### Test Organization

```
tests/
└── Unit/           # Unit tests
    └── *.php       # Test files
```

---

## Reporting Bugs

### Before Reporting

1. Check [existing issues](https://github.com/pekral/php-skeleton/issues) to avoid duplicates
2. Ensure you're using the latest version
3. Verify it's a bug, not a configuration issue

### Bug Report Template

```markdown
**Describe the bug**
A clear description of what the bug is.

**To Reproduce**
1. Step one
2. Step two
3. ...

**Expected behavior**
What you expected to happen.

**Environment**
- PHP version:
- Composer version:
- OS:

**Additional context**
Any other relevant information.
```

---

## Suggesting Features

### Before Suggesting

1. Check [existing issues](https://github.com/pekral/php-skeleton/issues) for similar suggestions
2. Consider if it fits the project scope

### Feature Request Template

```markdown
**Problem**
Describe the problem this feature would solve.

**Proposed Solution**
Your idea for implementing this feature.

**Alternatives Considered**
Other solutions you've thought about.

**Additional Context**
Any other relevant information.
```

---

## Questions?

Feel free to:

- Open a [discussion](https://github.com/pekral/php-skeleton/discussions)
- Contact the maintainer via email

---

Thank you for contributing! 🚀

