# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Adds support of Symfony 7

## [3.0.2] - 2024-01-19

### Added

- Adds support of Symfony 6

## [3.0.1] - 2023-02-02

### Fixed

- Wrong service declaration for the `TranslatableCRUDController` has been fixed

## 3.0.0 - 2023-01-12

### Added

- Adds support of PHP 8.*
- Adds integration to EasyAdmin 4
- Adds compatibility with Twig 3 (>= 2.7) by updating method heads
- Adds compatibility with Symfony 5 (>= 4.3) by updating `onKernelRequest()` method head

### Changed

- Removes annotations in favor of attributes
- Updates `README` file to best suit the new version

### Removed

- Drops support of PHP versions older than 8.0
- Removes dev requirement of `symfony/var-dumper`

[Unreleased]: https://github.com/umanit/translation-bundle/compare/3.0.2...HEAD

[3.0.2] https://github.com/umanit/translation-bundle/compare/3.0.1...3.0.2

[3.0.1]: https://github.com/umanit/translation-bundle/compare/3.0.0...3.0.1
