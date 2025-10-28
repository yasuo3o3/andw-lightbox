# Developer Documentation

This document outlines the development workflow, testing procedures, and contribution guidelines for andW Lightbox.

## Development Environment Setup

### Prerequisites

- **WordPress**: 6.0+ (recommended: latest stable)
- **PHP**: 7.4+ (recommended: 8.1+)
- **Node.js**: 16+ (for asset building and linting)
- **Composer**: For PHP dependency management

### Local Development

1. Clone the repository into your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone [repository-url] andw-lightbox
   cd andw-lightbox
   ```

2. Install development dependencies:
   ```bash
   composer install --dev
   npm install --dev
   ```

3. Activate the plugin in WordPress admin

## Code Quality & Standards

### WordPress Coding Standards (PHPCS)

Run PHPCS to check code compliance:

```bash
# Check all PHP files
composer run phpcs

# Check specific file
./vendor/bin/phpcs includes/class-andw-assets.php

# Auto-fix issues where possible
composer run phpcbf
```

### WordPress Plugin Check

Use the official Plugin Check tool before submission:

```bash
# Install Plugin Check plugin or use WP-CLI
wp plugin install plugin-check --activate

# Run checks via WP-CLI
wp plugin-check check andw-lightbox

# Or use the admin interface at Tools → Plugin Check
```

### JavaScript/CSS Linting

```bash
# Lint JavaScript files
npm run lint:js

# Lint CSS files
npm run lint:css

# Fix auto-fixable issues
npm run lint:js:fix
npm run lint:css:fix
```

## Testing Procedures

### Manual Testing Checklist

#### Core Functionality
- [ ] Image blocks display lightbox on click
- [ ] Gallery blocks group images correctly
- [ ] Media & Text blocks process images
- [ ] Classic editor images get lightbox treatment
- [ ] Settings page saves and applies changes
- [ ] CDN fallback works when network fails

#### Browser Compatibility
- [ ] Chrome (latest 2 versions)
- [ ] Firefox (latest 2 versions)
- [ ] Safari (latest 2 versions)
- [ ] Edge (latest 2 versions)

#### Accessibility Testing
- [ ] Keyboard navigation works
- [ ] Screen reader compatibility
- [ ] Focus management in lightbox
- [ ] ARIA attributes present
- [ ] Color contrast compliance

#### Performance Testing
- [ ] Assets load only when needed
- [ ] No JavaScript errors in console
- [ ] Reasonable load times on slow connections
- [ ] Memory usage within acceptable limits

### Test WordPress Versions

Test with minimum and recommended WordPress versions:

```bash
# Using WP-ENV (recommended)
npm install -g @wordpress/env

# Start test environment with specific WP version
wp-env start --wp-version=6.0
wp-env start --wp-version=latest
```

### Test PHP Versions

Test with supported PHP versions using Docker:

```bash
# PHP 7.4
docker run --rm -v $(pwd):/app -w /app php:7.4-cli php -l includes/

# PHP 8.1
docker run --rm -v $(pwd):/app -w /app php:8.1-cli php -l includes/

# PHP 8.2
docker run --rm -v $(pwd):/app -w /app php:8.2-cli php -l includes/
```

## Build Process

### Asset Compilation

```bash
# Development build (with source maps)
npm run build:dev

# Production build (minified)
npm run build

# Watch mode for development
npm run watch
```

### Distribution Package

Create a clean distribution package:

```bash
# Generate plugin ZIP file
npm run package

# This creates: dist/andw-lightbox.zip
```

## Release Process

### Pre-Release Checklist

1. **Code Quality**
   - [ ] All PHPCS checks pass
   - [ ] Plugin Check tool passes
   - [ ] JavaScript/CSS linting passes
   - [ ] No PHP errors or warnings

2. **Testing**
   - [ ] Manual testing completed
   - [ ] Browser compatibility verified
   - [ ] Accessibility testing passed
   - [ ] Performance benchmarks met

3. **Documentation**
   - [ ] README.md updated
   - [ ] CHANGELOG.txt updated
   - [ ] readme.txt version bumped
   - [ ] Code comments accurate

4. **Version Management**
   - [ ] Plugin header version updated
   - [ ] Constant ANDW_LIGHTBOX_VERSION updated
   - [ ] readme.txt Stable tag updated
   - [ ] Git tag created

### Version Bumping

```bash
# Update version in all necessary files
npm run version:bump -- 0.1.2

# Or manually update:
# - andw-lightbox.php (Plugin Name header + constant)
# - readme.txt (Stable tag)
```

### WordPress.org Submission

1. **Prepare submission package:**
   ```bash
   npm run package:wporg
   ```

2. **SVN workflow for WordPress.org:**
   ```bash
   # Checkout SVN repository
   svn co https://plugins.svn.wordpress.org/andw-lightbox

   # Update trunk
   cp -r dist/andw-lightbox/* andw-lightbox/trunk/

   # Add new files
   svn add andw-lightbox/trunk/*

   # Commit changes
   svn ci andw-lightbox/trunk -m "Version 0.1.2 release"

   # Create tag
   svn cp andw-lightbox/trunk andw-lightbox/tags/0.1.2
   svn ci andw-lightbox/tags/0.1.2 -m "Tagging version 0.1.2"
   ```

## CI/CD Implementation Notes

### GitHub Actions Workflow (Planned)

```yaml
# .github/workflows/test.yml
name: Test
on: [push, pull_request]
jobs:
  phpcs:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Install dependencies
        run: composer install
      - name: Run PHPCS
        run: composer run phpcs

  plugin-check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup WordPress
        uses: wordpress/setup-wordpress@v1
      - name: Run Plugin Check
        run: wp plugin-check check andw-lightbox
```

### Automated Testing Goals

- **Unit Tests**: PHPUnit for core functionality
- **Integration Tests**: WP-Browser for WordPress integration
- **E2E Tests**: Playwright for frontend behavior
- **Performance Tests**: Lighthouse CI for performance monitoring

## Contributing Guidelines

### Pull Request Process

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Make changes following coding standards
4. Add/update tests as needed
5. Run quality checks: `composer run phpcs && npm run lint`
6. Update documentation if needed
7. Submit pull request with clear description

### Code Review Criteria

- WordPress Coding Standards compliance
- Backward compatibility maintained
- Performance impact considered
- Security best practices followed
- Accessibility requirements met
- Tests pass and coverage maintained

## Architecture Notes

### Plugin Structure

```
andw-lightbox/
├── andw-lightbox.php          # Main plugin file
├── includes/                  # PHP classes
│   ├── class-andw-admin.php
│   ├── class-andw-assets.php
│   ├── class-andw-frontend.php
│   ├── class-andw-settings.php
│   └── helpers.php
├── assets/                    # Frontend assets
│   ├── css/
│   ├── js/
│   └── images/
├── docs/                      # Documentation
└── tests/                     # Test files
```

### Key Design Decisions

- **Singleton Pattern**: Settings class for global state
- **Hook-based Architecture**: WordPress actions/filters for extensibility
- **Conditional Loading**: Assets loaded only when content requires them
- **Graceful Degradation**: Fallbacks for CDN failures and JavaScript disabled

### Future Roadmap

- **Block Variations**: Custom lightbox block variations
- **REST API**: Settings management via REST endpoints
- **React Components**: Modern admin interface
- **WebP Support**: Enhanced image format handling
- **Performance Monitoring**: Built-in performance metrics