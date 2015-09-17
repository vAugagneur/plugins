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
