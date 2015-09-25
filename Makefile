PS := prestashop

test-all: test-setup test-checkout test-config test-run

test-setup:
	cd ci/box && vagrant up

test-halt:
	cd ci/box && vagrant halt

test-provision:
	cd ci/box && vagrant provision

test-checkout:
	git clone https://github.com/cshw/cashway-prestashop src/prestashop
	git clone https://github.com/cshw/cashway-magento src/magento
	git clone https://github.com/cshw/cashway-woocommerce src/woocommerce

test-config: test-config-$(PS)

test-run: test-$(PS)

test-config-$(PS):
	cd src/$(PS); make config-platform

test-run-$(PS):
	#cd $(PS); make test
	cd src/$(PS); make test-user

test-clean:
	rm -fr src/*

test-download-magento:
	@echo "TODO"
