RELEASE=cashway-$(shell git describe --tags)
RELEASE_FILE=releases/${RELEASE}.zip

prerelease:
	#[[ ! -d "releases" ]] && mkdir "releases"
	git archive --prefix=cashway/ --format zip --output ${RELEASE_FILE} master

signrelease:
	shasum -a 256 ${RELEASE_FILE} > ${RELEASE_FILE}.sha256
	gpg --sign --armor ${RELEASE_FILE}.sha256

cs:
	phpcs --standard=Prestashop --colors --ignore=lib/,upgrade/,vendor/ .
