BRANCH=$(shell git symbolic-ref --short HEAD)
TAG=$(shell git describe --tags)
RELEASE_FILE=releases/cashway-${BRANCH}-${TAG}.zip

prerelease:
	#[[ ! -d "releases" ]] && mkdir "releases"
	git archive --prefix=cashway/ --format zip --output ${RELEASE_FILE} ${TAG}

signrelease:
	shasum -a 256 ${RELEASE_FILE} > ${RELEASE_FILE}.sha256
	gpg --sign --armor ${RELEASE_FILE}.sha256

cs:
	phpcs --standard=Prestashop --colors --ignore=lib/,upgrade/,vendor/ .

test_install:
	cd tests; bundle exec rspec spec/01_install_module_spec.rb

test_user:
	cd tests; bundle exec rspec spec/02_client_use_spec.rb

test: test_install test_user
