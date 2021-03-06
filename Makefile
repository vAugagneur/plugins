PS := prestashop

usage:
	@echo "Available targets:"
	@cat Makefile | grep "^[A-z]" | awk '{print " - "$$1}' | sed "s/://g"

build-all:
	cd src/prestashop && make build
	mv src/prestashop/build/* build/

push-builds:
	rsync -avz --delete -e ssh build-official/ deploy@help.cashway.fr:/var/www/apps/releases

test-deps:
	which git
	which make
	which vagrant
	which ansible

test-all: test-setup test-checkout test-config test-run

test-setup:
	cd tests/box && vagrant up

test-halt:
	cd tests/box && vagrant halt

test-provision:
	cd tests/box && vagrant provision

test-checkout:
	git clone https://github.com/cshw/cashway-prestashop src/prestashop
	git clone https://github.com/cshw/cashway-magento src/magento
	git clone https://github.com/cshw/cashway-woocommerce src/woocommerce

test-config: test-config-$(PS)

test-run: test-run-$(PS)

test-config-$(PS):
	cd src/$(PS); make config-platform

test-run-$(PS):
	#cd $(PS); make test
	cd src/$(PS); make test-user

test-clean:
	cd tests/box && vagrant halt && vagrant destroy --force

test-download-magento:
	@echo "TODO"
