# Changelog for CashWay PrestaShop module

## 2016-04-08 - 1.2.0

 * fix service fees display bug
 * fix a potential issue on payment reporting (#2, core #94, core #112)
 * fix localized dates again
 * update test cases
 * update translations
 * better reporting to & from API

## 2016-03-04 - 1.1.0

 * fix localized date display
 * fix tests
 * validated package for addons
 * remove registration form from admin

## 2016-02-17 - 1.0.1

 * official release version for addons

## 2016-01-04 - 0.14.1

 * fix installation compliance test for PS 1.5

## 2015-12-21 - 0.14

 * code & tests refactoring for better errors detection at run time.
 * fix proper CW icon in admin menu

## 2015-12-04 - 0.13

 * fix a notification error on paid transactions,
   refactor checkForPayments + better tests
 * more specific return codes on notifications

## 2015-11-17 - 0.12

 * rephrase terms of use agreement

## 2015-10-12 - 0.11

 * add configurable paid order status
 * refactor admin config forms & update translations
 * lots of improvements in tests, new release process
 * import into new plugins/ repository

## 2015-09-29 - 0.10

 * improve test suites
 * refactor notifications reception
 * fix information links in payment page

## 2015-09-15 - 0.9.1

 * add/rework tests

## 2015-09-02 - 0.9

 * add KYC conditions on matching transactions
 * force limits on country/currency
 * code style fixes

## 2015-08-17 - 0.8

 * enforce CGU customer check
 * improve conversion email message (post failed payment)
 * update wrapper lib and refactor notification handlers
 * host action buttons images in the plugin

## 12 August 2015 - 0.7.1

Fixes:

 * missed notifications, requiring manual action
 * notification total reporting

Dev notes:

 * style fixes (quoting, escaping, PSR2 standard, headers)
 * add usage markers
 * Makefile and tests updates
 * update cashway_lib to 0.5 with more tests
 * add getallheaders() when missing
 * refactor code

## 4 August 2015 - 0.6

New:

 * payment pages have 2 configurable buttons
 * account registration is available from the module, for first-time users
 * notifications may be received and sent from/to API now, for several events
   (payment_failed, transaction_*, conversion_expired, status_check)

Removed:

 * Cron task

Dev notes:

 * reorganized code, lots of fixes, updated dependencies (cashway_lib, maps)
 * added CircleCI config, Makefile, Capybara tests (install, basic customer path)
 * intermediary 0.4.x & 0.5.x releases

## 9 avril - 0.3.0, dev version

 * removed root {validation,payment}.php out-of-sync
   endpoints (and deprecated since PS 1.5, so breaks
   pre 1.5 compatibility)
 * style fixes

## 8 avril - 0.2.0, dev version

 * fix points highlighted by code audit
 * move status update cron into new front controller
 * manage API failures better
 * JS map is now loaded from remote

## 31 mars - 0.1.1, dev version

 * fix install process

## 18 mars - 0.1.0, dev version

 * added cron task: check local orders pending payment,
   check transactions status by CashWay service,
   compare, update.

## 12 mars 2015 - 0.0.1, dev version

 * [+] MO : new CashWay module with basic functionality:
   - installs itself with basic config.
   - creates a new transaction on payment method confirmation.
