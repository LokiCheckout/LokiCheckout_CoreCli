# LokiCheckout_CoreCli

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
