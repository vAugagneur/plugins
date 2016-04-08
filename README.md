# CashWay Plugins

[![Circle CI](https://circleci.com/gh/cshw/plugins.svg?style=svg)](https://circleci.com/gh/cshw/plugins)

Stable:

 * PrestaShop in `src/prestashop`
 * PHP library in `src/php`

Unstable:

 * Magento in `src/magento`, in development
 * WooCommerce in `src/woocommerce`

## Testing

Requires VirtualBox, Vagrant, Ansible.

Run all tests with `make test`. Or make it step by step:

```shell
$ make test-setup
$ make test-config-prestashop
$ make test-run-prestahop
```

## Contributing

 * each platform module lives in `src/{platform}`.
 * each platform has its own make rules:
   * `make cs` and `make csfix`
   * `make build`
   * `make release`
   * `make reset-module`
   * `make config-platform`
   * `make test-user`
 * please adhere to target platform code style;
   if there's no style guide, choose wisely and discuss;
 * try not to duplicate API facing code from a wrapper library;
   if there's no library yet, start one.
 
