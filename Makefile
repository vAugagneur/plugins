
usage:
	@echo "Available targets:"
	@cat Makefile | grep "^[A-z]" | awk '{print " - "$$1}' | sed "s/://g"

build-all:
	cd src/prestashop && make build
	mv src/prestashop/build/* build/

push-builds:
	rsync -avz --delete -e ssh build-official/ cw-prod-release:/var/www/apps/releases

list: src/
	@ls -la src/

install-test-deps:
	./tests/install-test-deps.sh

test-deps:
	which git
	which make
	which vagrant
	which ansible
	which gsed
	which bundle
	which ruby

test: test-setup test-config test-run test-clean

test-setup:
	cd tests/box && vagrant up

test-halt:
	cd tests/box && vagrant halt

test-provision:
	cd tests/box && vagrant provision

test-config: test-config-prestashop

test-run: test-run-prestashop

test-config-%:
	cd src/$*; make config-platform

test-run-%:
	#cd src/$*; make test
	cd src/$*; make test-user

test-clean:
	cd tests/box && vagrant halt && vagrant destroy --force

test-download-magento:
	@echo "TODO"
