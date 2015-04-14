RELEASE="cashway-$(shell git describe --tags)"

localrelease:
	git archive --prefix=cashway/ --format zip --output ${RELEASE}.zip master

cs:
	phpcs --standard=Prestashop --colors --ignore=lib/,upgrade/,vendor/ .
