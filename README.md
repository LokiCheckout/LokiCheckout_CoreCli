# LokiCheckout_CoreCli

<!-- badges.specs.start -->
![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://gitlab.yireo.com/loki-checkout/LokiCheckout_CoreCli/-/badges/release.svg)
<!-- badges.specs.end -->


**This Magento 2 module is an add-on to the Loki Checkout core. It allows listing all Loki Checkout modules in the CLI and dumping to the same listing to a file `app/etc/loki-checkout.json`. That file is used by some other mechanisms like the Luma Tailwind build. Most likely you will only need this as a dependency for other modules.**

### Installation
```bash
composer require loki-checkout/magento2-core-cli
bin/magento module:enable LokiCheckout_CoreCli
```

### Usage
```bash
bin/magento loki-checkout:modules:list
bin/magento loki-checkout:modules:dump
```

## Current status

<!-- badges.test.start -->
[![Static Tests](https://gitlab.yireo.com/loki-checkout/LokiCheckout_CoreCli/badges/main/pipeline.svg?job=static-tests)](https://gitlab.yireo.com/loki-checkout/LokiCheckout_CoreCli/-/pipelines)
[![Unit Tests](https://gitlab.yireo.com/loki-checkout/LokiCheckout_CoreCli/badges/main/pipeline.svg?job=unit-tests)](https://gitlab.yireo.com/loki-checkout/LokiCheckout_CoreCli/-/pipelines)
[![Integration Tests](https://gitlab.yireo.com/loki-checkout/LokiCheckout_CoreCli/badges/main/pipeline.svg?job=integration-tests)](https://gitlab.yireo.com/loki-checkout/LokiCheckout_CoreCli/-/pipelines)
[![Playwright](https://gitlab.yireo.com/loki-checkout/LokiCheckout_CoreCli/badges/main/pipeline.svg?job=playwright)](https://gitlab.yireo.com/loki-checkout/LokiCheckout_CoreCli/-/pipelines)
[![DI Compilation](https://gitlab.yireo.com/loki-checkout/LokiCheckout_CoreCli/badges/main/pipeline.svg?job=compile)](https://gitlab.yireo.com/loki-checkout/LokiCheckout_CoreCli/-/pipelines)
<!-- badges.test.end -->
