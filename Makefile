PS := prestashop

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
	rm -fr src/*

test-download-magento:
	@echo "TODO"
