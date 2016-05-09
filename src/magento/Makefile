BRANCH=$(shell git symbolic-ref --short HEAD)
TAG=$(shell git tag -l ma* |head -1)
REF=$(shell git rev-parse --short HEAD)
TS=$(shell date +"%s")
BUILD=build
VERSION=$(shell grep 'version' app/code/community/Sirateck/Cashway/etc/config.xml | awk -F">" '{print $$2}' | awk -F"<" '{print $$1}' | awk 'NF > 0')
RELEASE_FILE=${BUILD}/cashway-magento-${VERSION}-dev.${BRANCH}.${REF}+${TS}.tgz
TEST_RUN=cd tests; bundle exec rspec

usage:
	@echo "Available targets:"
	@cat Makefile | grep "^[A-z]" | awk '{print " - "$$1}' | sed "s/://g"

install-build-deps:
	git clone https://github.com/astorm/MagentoTarToConnect tools/mttc

update-build-deps:
	cd tools/mttc && git pull

release: isclean package sign

isclean:
ifneq (${BRANCH}, 'master')
	@echo "E: not releasing, because not on master branch."
	exit 1
endif
ifneq ($(shell git diff --shortstat 2> /dev/null | tail -n1), '')
	@echo "E: not releasing, because of a dirty Git index."
	exit 2
endif
ifneq ($(shell expr `git status --porcelain 2>/dev/null| grep "^??" | wc -l`), 0)
	@echo "E: not releasing, because there are untracked files."
	exit 3
endif

package:
	mkdir -p ${BUILD}
	tar cf build/Sirateck_Cashway.tar app
	tools/mttc/magento-tar-to-connect.phar config.php
	cp ${BUILD}/CashWay-${VERSION}.tgz ${RELEASE_FILE}
	#Update .env
	sed -i.bak "s|MODULE_VERSION=.*|MODULE_VERSION=${VERSION}|g" tests/.env

sign:
	shasum -a 256 ${RELEASE_FILE} > ${RELEASE_FILE}.sha256
	gpg --sign --armor ${RELEASE_FILE}.sha256

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

test-fees:
	${TEST_RUN} spec/test_fees.rb
