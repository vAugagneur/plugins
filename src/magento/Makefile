BRANCH=$(shell git symbolic-ref --short HEAD)
TAG=$(shell git describe --tags)
RELEASE_FILE=releases/cashway-magento-${BRANCH}-${TAG}.zip
TS=$(shell date +"%Y-%m-%dT%H:%M:%SZ")
TEST_RUN=cd tests; bundle exec rspec

usage:
	@echo "Available targets:"
	@cat Makefile | grep "^[A-z]" | awk '{print " - "$$1}' | sed "s/://g"

install-build-deps:
	git clone https://github.com/astorm/MagentoTarToConnect tools/mttc

update-build-deps:
	cd tools/mttc && git pull

package:
	tar cf build/Sirateck_Cashway.tar app
	tools/mttc/magento-tar-to-connect.phar config.php

clean:
	rm -fr build/*

cs:
	phpcs --config-set show_warnings 0
	phpcs --encoding=utf-8 \
		--standard=PSR2 \
		--colors \
		--ignore=lib/,vendor/,coverage/,tools/,build/,archives/ \
		.

csfix:
	phpcbf --encoding=utf-8 \
		--standard=PSR2 \
		--colors \
		--ignore=lib/,vendor/,coverage/,tools/,build/,archives \
		.

test:
	phpunit .

install-cashway:
	${TEST_RUN} spec/install-cashway.rb

add-new-account:
	${TEST_RUN} spec/add_new_account.rb

test-user:
	${TEST_RUN} spec/02_client_use_spec.rb

add-products:
	${TEST_RUN} spec/admin_add_products.rb

test-user-250e:
	${TEST_RUN} spec/02_client_use_250e.rb

test-user-2500e:
	${TEST_RUN} spec/02_client_use_2500e.rb

config-shipping:
	${TEST_RUN} spec/set-shipping-price.rb

config-magento:
	${TEST_RUN} spec/install_magento.rb
