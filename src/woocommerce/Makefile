BRANCH=$(shell git symbolic-ref --short HEAD)
TAG=$(shell git describe --tags)
BUILD_DIR=build
BUILD_TMP=${BUILD_DIR}/tmp
RELEASE_FILE=${BUILD_DIR}/cashway-woocommerce-${BRANCH}-${TAG}.zip
TS=$(shell date +"%Y-%m-%dT%H:%M:%SZ")
TEST_RUN=cd tests && bundle exec rspec

usage:
	@echo "Available targets:"
	@cat Makefile | grep "^[A-z]" | awk '{print " - "$$1}' | sed "s/://g"

build: release

release:
	mkdir -p ${BUILD_DIR}
	mkdir -p ${BUILD_TMP}/cashway/lib/cashway
	git archive --prefix=cashway/ --format zip --output ${BUILD_TMP}/cashway.zip ${TAG}
	cd ../php/ && git archive --prefix=cashway/lib/cashway/ --format=zip --output=../woocommerce/${BUILD_TMP}/lib.zip HEAD cashway_lib.php compat.php
	cd ${BUILD_TMP} && unzip lib.zip && zip -r cashway.zip cashway && rm lib.zip
	mv ${BUILD_TMP}/cashway.zip ${RELEASE_FILE}
	rm -fr ${BUILD_TMP}
	gsed -i.bak "s|MODULE_ARCHIVE=.*|MODULE_ARCHIVE=../${RELEASE_FILE}|g" tests/.env

prerelease:
	#[[ ! -d "releases" ]] && mkdir "releases"
	git archive --prefix=cashway/ --format zip --output ${RELEASE_FILE} ${TAG}
	gsed -i.bak "s|MODULE_ARCHIVE=.*|MODULE_ARCHIVE=../${RELEASE_FILE}|g" tests/.env

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

csfix:
	phpcbf --encoding=utf-8 \
		--standard=PSR2 \
		--colors \
		--ignore=lib/,vendor/,coverage/ \
		.

config-base:
	${TEST_RUN} spec/install_woocommerce.rb

module-install:
	${TEST_RUN} spec/install_module.rb

config-platform: tests/.env release \
	config-base \
	add-test-products \
	add-new-account \
	module-install

tests/.env:
	ruby tests/build_env.rb > tests/.env

init-admin:
	${TEST_RUN} spec/init_admin.rb

reset-db:
	cd ../../tests/box; vagrant ssh -c "mysql -uroot -e 'DROP DATABASE prestashop; CREATE DATABASE prestashop'"

add-test-products:
	${TEST_RUN} spec/add_test_products.rb

add_new_account:
	${TEST_RUN} spec/add_new_account.rb

test-user: test-client-50 test-client-250 test-client-2500

test-client-50:
	${TEST_RUN} spec/client_use_50.rb

test-client-250:
	${TEST_RUN} spec/client_use_250e.rb

add-new-account:
	${TEST_RUN} spec/add_new_account.rb

test-client-2500:
	${TEST_RUN} spec/client_use_2500e.rb

test-admin-credentials:
	${TEST_RUN} spec/add_admin_credentials.rb

test-surcharge:
	${TEST_RUN} spec/surcharge.rb

test:
	phpunit .

uxtest: test-install test-user
