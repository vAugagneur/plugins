BRANCH=$(shell git symbolic-ref --short HEAD)
TAG=$(shell git describe --tags)
RELEASE_FILE=releases/cashway-${BRANCH}-${TAG}.zip
TS=$(shell date +"%Y-%m-%dT%H:%M:%SZ")

usage:
	@echo "Available targets:"
	@cat Makefile | grep "^[A-z]" | awk '{print " - "$$1}' | sed "s/://g"

release:
	mkdir -p releases
	mkdir -p rel.tmp/cashway/lib/cashway
	git archive --prefix=cashway/ --format zip --output rel.tmp/cashway.zip ${TAG}
	cd ../ && git archive --prefix=cashway/lib/cashway/ --format zip --output prestashop/rel.tmp/lib.zip HEAD php/{cashway_lib,compat}.php
	cd rel.tmp && zip cashway.zip lib.zip && rm lib.zip
	mv rel.tmp/cashway.zip ${RELEASE_FILE}
	rm -fr rel.tmp
	gsed -i.bak "s|MODULE_ARCHIVE=.*|MODULE_ARCHIVE=../${RELEASE_FILE}|g" tests/.env

release-sign:
	shasum -a 256 ${RELEASE_FILE} > ${RELEASE_FILE}.sha256
	gpg --detach-sign --armor ${RELEASE_FILE}.sha256

release-verify:
	cat ${RELEASE_FILE}.sha256
	gpg --verify ${RELEASE_FILE}.sha256.asc
	shasum -c ${RELEASE_FILE}.sha256

cs:
	phpcs --config-set show_warnings 0
	phpcs --encoding=utf-8 \
		--standard=PSR2 \
		--colors \
		--ignore=lib/,vendor/,coverage/ \
		.

reset-epayment:
	cd tests; cp .env.epayment .env; bundle exec rspec spec/01_install_module_spec.rb

upgrade-epayment:
	cd tests; cp .env.epayment .env; bundle exec rspec spec/01b_upgrade_module_spec.rb

test-install:
	cd tests; cp .env.local .env; bundle exec rspec spec/01_install_module_spec.rb

test-upgrade:
	cd tests; cp .env.local .env; bundle exec rspec spec/01b_upgrade_module_spec.rb

config-platform: tests/.env prerelease \
	config-base remove-install-dir init-admin catch-admin-url \
	add-test-products \
	add-new-account \
	reset-epayment

tests/.env:
	ruby tests/build_env.rb > tests/.env

config-base:
	cd tests; cp .env.local .env; bundle; bundle exec rspec spec/config_base.rb

remove-install-dir:
	cd ../../tests/box; vagrant ssh -c "sudo rm -fr /var/www/html/prestashop/install"

catch-admin-url:
	$(eval ADMIN_PATH := $(shell basename $(shell cd ../../tests/box/; vagrant ssh -c 'ls -d /var/www/html/prestashop/admin*')))
	cd tests/ ; sed -i.bak "s|ADMIN_PATH=.*|ADMIN_PATH=/${ADMIN_PATH}|g" .env
	@echo "ADMIN_PATH is ${ADMIN_PATH}"

init-admin:
	cd tests; cp .env.local .env; bundle exec rspec spec/init_admin.rb

reset-db:
	cd ../../tests/box; vagrant ssh -c "mysql -uroot -e 'DROP DATABASE prestashop; CREATE DATABASE prestashop'"

add-test-products:
	cd tests; cp .env.local .env; bundle exec rspec	spec/add_test_products.rb

add_new_account:
	cd tests; cp .env.local .env; bundle exec rspec spec/add_new_account.rb

test-user: test-client-250 test-client-2500

test-client-ok:
	cd tests; cp .env.local .env; bundle exec rspec spec/client_use_spec.rb

test-client-250:
	cd tests; cp .env.local .env; bundle exec rspec spec/client_use_250e.rb

add-new-account:
	cd tests; cp .env.local .env; bundle exec rspec spec/add_new_account.rb

test-client-2500:
	cd tests; cp .env.local .env; bundle exec rspec spec/client_use_2500e.rb

test:
	phpunit .

uxtest: test-install test-user
