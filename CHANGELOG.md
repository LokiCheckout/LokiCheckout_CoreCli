# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.10] - 06 November 2026
### Fixed
- Only catch filesystem exceptions declared by the write directory
- Keep the dumped JSON file non-executable after the atomic rename
- Check that the dumped JSON file is not executable
- Reject unknown list formats and fail on JSON encoding errors
- Accept any Symfony console exception for a missing format value
- Write app/etc/loki-checkout.json atomically and fail on encoding or write errors
- Only strip the Magento root from the start of module paths

## [1.0.9] - 15 May 2026
### Fixed
- Fix Symfony 7 console command compat

## [1.0.8] - 24 February 2026
### Fixed
- Make sure to remove the slash as well

## [1.0.7] - 12 February 2026
### Fixed
- Add modules with their relative paths if possible
- Copy generic CI/CD files

## [1.0.6] - 22 October 2025
### Fixed
- Remove int return type from Symfony commands for Symfony 5 compat
- Update README

## [1.0.5] - 16 September 2025
### Fixed
- Remove slashes from paths in app/etc/loki-checkout.json
- Copy generic CI/CD files

## [1.0.4] - 02 September 2025
### Fixed
- Add CI files
- Replace yireo/opensearch with yireo/opensearch-dummy in Gitlab CI

## [1.0.3] - 19 August 2025
### Fixed
- Lower requirements to PHP 8.1

## [1.0.2] - 07 August 2025
### Fixed
- Lower PHP requirement to PHP 8.2+

## [1.0.1] - 22 July 2025
### Fixed
- Fix PSR-4 namespace

## [1.0.0] - 22 July 2025
### Fixed
- Move CLI from core module
- Init files
- Adding all files to git via Yireo Command
