# LokiCheckout_CoreCli

<!-- badges.specs.start -->
<div class="github-specs-badges">

![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/loki-checkout/magento2-core-cli)

</div>
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
<div class="github-pipeline-badges">

![Static Tests](https://img.shields.io/github/actions/workflow/status/LokiCheckout/LokiCheckout_CoreCli/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/LokiCheckout/LokiCheckout_CoreCli/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/LokiCheckout/LokiCheckout_CoreCli/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/LokiCheckout/LokiCheckout_CoreCli/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/LokiCheckout/LokiCheckout_CoreCli/compile.yml?label=compile)

</div>
<!-- badges.test.end -->
