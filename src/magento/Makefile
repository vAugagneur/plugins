RELEASE="cashway-magento-$(shell git describe --tags)"

localrelease:
	git archive --prefix=cashway/ --format zip --output ${RELEASE}.zip master

cs:
	phpcs --standard=PSR2 --colors .

csfix:
	phpcbf --standard=PSR2 .
