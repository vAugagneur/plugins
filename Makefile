BRANCH=$(shell git symbolic-ref --short HEAD)
TAG=$(shell git describe --tags)
RELEASE_FILE=releases/cashway-${BRANCH}-${TAG}.zip
TS=$(shell date +"%Y-%m-%dT%H:%M:%SZ")

usage:
	@echo "Available targets:"
	@cat Makefile | grep "^[A-z]" | awk '{print " - "$$1}' | sed "s/://g"

prerelease:
	#[[ ! -d "releases" ]] && mkdir "releases"
	git archive --prefix=cashway/ --format zip --output ${RELEASE_FILE} ${TAG}
	gsed -i.bak "s|MODULE_ARCHIVE=.*|MODULE_ARCHIVE=../${RELEASE_FILE}|g" tests/.env.local
	gsed -i.bak "s|MODULE_ARCHIVE=.*|MODULE_ARCHIVE=../${RELEASE_FILE}|g" tests/.env.epayment

signrelease:
	shasum -a 256 ${RELEASE_FILE} > ${RELEASE_FILE}.sha256
	gpg --sign --armor ${RELEASE_FILE}.sha256

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

config-platform: config-base add-test-products

config-base:
	cd tests; cp .env.local .env; bundle exec rspec spec/config_platform.rb

add-test-products:
	cd tests; cp .env.local .env; bundle exec rspec	spec/add_test_products.rb

test-user: client-ok client-250 client-2500

client-ok:
	cd tests; cp .env.local .env; bundle exec rspec spec/client_use_spec.rb

client-250:
	cd tests; cp .env.local .env; bundle exec	rspec spec/client_use_250e.rb

client-2500:
	cd tests; cp .env.local .env; bundle exec rspec spec/client_use_2500e.rb

test:
	phpunit .

uxtest: test-install test-user

copydeps:
	wget -O lib/cashway/cashway_lib.php https://raw.githubusercontent.com/cshw/api-helpers/master/php/cashway_lib.php?${TS}
	wget -O lib/cashway/compat.php https://raw.githubusercontent.com/cshw/api-helpers/master/php/compat.php?${TS}
