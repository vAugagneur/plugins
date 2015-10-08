
RELEASE="cashway-$(shell git describe --tags)"

build:
	echo "TODO - compile PHP source files into one, get version number, sha1 => cashway-php-{v}-{sha}.php"

cs:
	phpcs --standard=PSR1,PSR2 --colors --ignore=tests/ .

test:
	cd tests; phpunit .
