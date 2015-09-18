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
	tar cf build/Sirateck_Cashway.tar Sirateck_Cashway
	tools/mttc/magento-tar-to-connect.phar config.php

clean:
	rm -fr build/*

cs:
	phpcs --encoding=utf-8 --standard=PSR2 --colors .

csfix:
	phpcbf --encoding=utf-8 --standard=PSR2 .

test:
	phpunit .

reset_epayment:
	cd tests; cp .env.epayment .env; bundle exec rspec spec/01_install_module_spec.rb

upgrade_epayment:
	cd tests; cp .env.epayment .env; bundle exec rspec spec/01b_upgrade_module_spec.rb

test_install:
	cd tests; cp .env.local .env; bundle exec rspec spec/01_install_module_spec.rb

test_upgrade:
	cd tests; cp .env.local .env; bundle exec rspec spec/01b_upgrade_module_spec.rb

test_user:
	cd tests; cp .env.local .env; bundle exec rspec spec/02_client_use_spec.rb

add_products:
	cd tests; cp .env.local .env; bundle exec rspec spec/03_add_products.rb

test_user_250e:
	cd tests; cp .env.local .env; bundle exec rspec spec/02_client_use_250e.rb

test_user_2500e:
		cd tests; cp .env.local .env; bundle exec rspec spec/02_client_use_2500e.rb

test_google:
	cd tests; cp .env.local .env; bundle exec rspec spec/test_scenario.rb
