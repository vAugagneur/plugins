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
	phpcs --encoding=utf-8 \
		--standard=./php.standard.xml \
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

config-platform: config-base \
	config-magento config-shipping add-test-products add-new-account install-cashway

config-base:
	cd tests && bundle

install-cashway:
	${TEST_RUN} spec/install-cashway.rb

add-new-account:
	${TEST_RUN} spec/add_new_account.rb

test-user:
	${TEST_RUN} spec/client_use.rb

add-test-products:
	${TEST_RUN} spec/add_test_products.rb

config-shipping:
	${TEST_RUN} spec/set-shipping-price.rb

config-magento:
	${TEST_RUN} spec/install_magento.rb
