BRANCH=$(shell git symbolic-ref --short HEAD)
TAG=$(shell git describe --tags)
RELEASE_FILE=releases/cashway-magento-${BRANCH}-${TAG}.zip
TS=$(shell date +"%Y-%m-%dT%H:%M:%SZ")

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
	phpcs --encoding=utf-8 --standard=PSR2 --colors .

csfix:
	phpcbf --encoding=utf-8 --standard=PSR2 .

test:
	phpunit .

install-cashway:
	cd tests; bundle exec rspec spec/install-cashway.rb

add-new-account:
	cd tests; bundle exec rspec spec/add_new_account.rb

test-user:
	cd tests; bundle exec rspec spec/02_client_use_spec.rb

add-products:
	cd tests; bundle exec rspec spec/admin_add_products.rb

test-user-250e:
	cd tests; bundle exec rspec spec/02_client_use_250e.rb

test-user-2500e:
	cd tests; bundle exec rspec spec/02_client_use_2500e.rb

config-shipping:
	cd tests; bundle exec rspec spec/set-shipping-price.rb

config-magento:
	cd tests; bundle exec rspec spec/install_magento.rb
