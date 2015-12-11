# CashWay PrestaShop module

## Installation

Still in development. DO NOT INSTALL OR USE unless you know what you're doing.

Stable packages will be later available directly from https://www.cashway.fr/.


### Dependencies

This module depends on APIs provided by [CashWay](https://www.cashway.fr/):

 * api.cashway.fr
 * maps.cashway.fr


### Tests

 * Tests in `tests/php` are static tests to be run locally (`make test-CWD` or `make test-HEAD`).
 * Tests in `tests/spec` are to be run against a live PrestaShop test instance.


## Contributing

Issues, PR and ideas are welcome.

 * API documentation: https://help.cashway.fr/shops
 * Freenode IRC channel: #cashway_fr


## Licenses

 * the PrestaShop-specific code of this module is licensed under the
   Academic Free License 3.
 * CashWay API wrapper code is licensed under the
   [Apache License 2.0](http://www.apache.org/licenses/LICENSE-2.0)
   (`lib/cashway/cashway_lib.php`, `views/js/cashway_map.js`)

### Apache License

    Copyright 2015 CashWay

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.
