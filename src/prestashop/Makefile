BRANCH=$(shell git symbolic-ref --short HEAD)
TAG=$(shell git describe --tags)
BUILD_DIR=build
BUILD_TMP=${BUILD_DIR}/tmp
RELEASE_FILE=${BUILD_DIR}/cashway-prestashop-${BRANCH}-${TAG}.zip
TS=$(shell date +"%Y-%m-%dT%H:%M:%SZ")
TEST_RUN=cd tests && bundle exec rspec
UPDATE_ENV_ON_RELEASE=0

usage:
	@echo "Available targets:"
	@cat Makefile | grep "^[A-z]" | awk '{print " - "$$1}' | sed "s/://g"

check-deps:
	which git zip unzip gsed bash shasum gpg phpcs phpcbf phpunit ruby bundle

build: release release-sign

release-file:
ifeq ($(RELEASE), 1)
	$(eval VAR := $(shell bash -c 'read -e -p "Set build tag: " -i "${BRANCH}-${TAG}" VAR; echo $$VAR'))
	$(eval RELEASE_FILE := '${BUILD_DIR}/cashway-prestashop-${VAR}.zip')
	echo ${RELEASE_FILE}
endif

release: release-file
	mkdir -p ${BUILD_TMP}
	# Export archives from src/prestashop...
	git archive --prefix=cashway/ --format zip --output ${BUILD_TMP}/cashway.zip ${TAG}
	# ...and src/php
	cd ../php/ && git archive --prefix=cashway/lib/cashway/ --format=zip --output=../prestashop/${BUILD_TMP}/lib.zip HEAD cashway_lib.php compat.php
	# Unzip lib.zip and rezip its content into main zip
	cd ${BUILD_TMP} && unzip lib.zip && zip -r cashway.zip cashway && rm lib.zip
	# Move into release dir, cleanup tmp
	mv ${BUILD_TMP}/cashway.zip ${RELEASE_FILE}
	rm -fr ${BUILD_TMP}
ifeq ($(UPDATE_ENV_ON_RELEASE), 1)
	# Update .env
	sed -i.bak "s|MODULE_ARCHIVE=.*|MODULE_ARCHIVE=../${RELEASE_FILE}|g" tests/.env
endif

release-sign: release-file
	shasum -a 256 ${RELEASE_FILE} > ${RELEASE_FILE}.sha256
	gpg --detach-sign --armor ${RELEASE_FILE}.sha256

release-verify: release-file
	cat ${RELEASE_FILE}.sha256
	gpg --verify ${RELEASE_FILE}.sha256.asc
	shasum -c ${RELEASE_FILE}.sha256

cs:
	phpcs --config-set show_warnings 0
	phpcs --encoding=utf-8 \
		--standard=PSR2 \
		--colors \
		--ignore=lib/,vendor/,coverage/,tests/ \
		.

csfix:
	phpcbf --encoding=utf-8 \
		--standard=PSR2 \
		--colors \
		--ignore=lib/,vendor/,coverage/ \
		.

reset-module:
	${TEST_RUN} spec/01_install_module_spec.rb

upgrade-module:
	${TEST_RUN} spec/01b_upgrade_module_spec.rb

config-platform: tests/.env release \
	config-base remove-install-dir init-admin catch-admin-url \
	add-test-products \
	add-new-account \
	reset-module

tests/.env:
	cd tests && bundle
	cd tests && bundle exec ruby build_env.rb > .env

config-base:
	cd tests && bundle
	${TEST_RUN} spec/config_base.rb

remove-install-dir:
	cd ../../tests/box; vagrant ssh -c "sudo rm -fr /var/www/html/prestashop/install"

remove-module:
	cd ../../tests/box; vagrant ssh -c "sudo rm -fr /var/www/html/prestashop/modules/cashway"

catch-admin-url:
	$(eval ADMIN_PATH := $(shell basename $(shell cd ../../tests/box/; vagrant ssh -c 'ls -d /var/www/html/prestashop/admin*')))
	cd tests/ ; sed -i.bak "s|ADMIN_PATH=.*|ADMIN_PATH=/${ADMIN_PATH}|g" .env
	@echo "ADMIN_PATH is ${ADMIN_PATH}"

init-admin:
	${TEST_RUN} spec/init_admin.rb

reset-db:
	cd ../../tests/box; vagrant ssh -c "mysql -uroot -e 'DROP DATABASE prestashop; CREATE DATABASE prestashop'"

add-test-products:
	${TEST_RUN}	spec/add_test_products.rb

add-new-account:
	${TEST_RUN} spec/add_new_account.rb

test-user:
	${TEST_RUN} spec/client_use.rb

# Run tests against the whole current working tree status:
# src/prestashop & src/php, be changes committed or not.
# Useful while hacking the whole tree.
test-CWD:
	cp ../php/cashway_lib.php lib/cashway/
	cp ../php/compat.php lib/cashway/
	phpunit --color . ; \
	status=$$? ; \
	rm -fr lib/cashway/cashway_lib.php lib/cashway/compat.php ; \
	exit $$status

# Run tests against current working tree status for src/prestashop only.
# src/php deps are taken at HEAD status.
# Useful to pre-validate code.
test-HEAD: test
test:
	cd ../php/ && git archive --prefix=cashway/ --format zip --output ../prestashop/lib/cashway.zip HEAD cashway_lib.php compat.php
	cd lib && unzip cashway.zip
	phpunit --color . ; \
	status=$$? ; \
	rm -fr lib/cashway/cashway_lib.php lib/cashway/compat.php lib/cashway.zip ; \
	exit $$status

uxtest: test-install test-user

-include Makefile.local
