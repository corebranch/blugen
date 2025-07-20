# Blugen

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](https://github.com/shahmal1yev/blugen)

A powerful PHP library that generates code based on ATProto lexicons. Blugen reads lexicon JSON files and generates corresponding PHP classes with proper namespacing, type definitions, and structure following PSR standards.

## Features

- **🚀 Automated Code Generation**: Generate PHP classes from ATProto lexicon definitions
- **📁 Smart Namespacing**: Automatic PHP namespace resolution from lexicon paths
- **🔧 Multi-file Generation**: Support for generating multiple related classes from single lexicons
- **⚡ CLI Interface**: Easy-to-use command-line interface for code generation
- **📋 PSR Compliant**: Follows PSR-4 autoloading and PSR coding standards
- **🔍 Type Safety**: Generates type-safe PHP classes with proper docblocks

## Installation

### Using Composer

```bash
composer require corebranch/blugen
```

### Development Installation

```bash
git clone https://github.com/shahmal1yev/blugen.git
cd blugen
composer install
```

## Quick Start

### Basic Usage

Generate PHP classes from ATProto lexicons:

```bash
./bin/blugen generate
```

### Configuration

Default configuration is stored in `config/codegen.php`:

```php
return [
    'lexicons' => [
        'source' => __DIR__ . "/../atproto/lexicons",
    ],
    'output' => [
        'base_namespace' => "BlugenGenerator\\",
    ],
];
```

## Development

### Running Tests

```bash
composer test
# or
vendor/bin/phpunit
```

### Static Analysis

```bash
vendor/bin/phpstan analyse
```

### Development Commands

```bash
# Install dependencies
composer install

# Run tests
composer test

# Generate code from lexicons
./bin/blugen generate
```

## Configuration

### Custom Configuration

Create a custom configuration file:

```php
// config/custom.php
return [
    'lexicons' => [
        'source' => __DIR__ . "/lexicons",
    ],
    'output' => [
        'base_namespace' => "Codegen\\",
    ],
];
```

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Setup

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes
4. Run tests: `composer test`
5. Run static analysis: `vendor/bin/phpstan analyse`
6. Commit changes: `git commit -m 'Add amazing feature'`
7. Push to branch: `git push origin feature/amazing-feature`
8. Submit a pull request

### Coding Standards

- Follow PSR-12 coding standards
- Write comprehensive tests
- Document public APIs

## Requirements

- PHP 8.1 or higher
- Composer

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.

## Support

- 🐛 [Issue Tracker](https://github.com/shahmal1yev/blugen/issues)
- 💬 [Discussions](https://github.com/shahmal1yev/blugen/discussions)

---

Made with ❤️ by the [@shahmal1yev](https://github.com/shahmal1yev). Built for the ATProto ecosystem.
