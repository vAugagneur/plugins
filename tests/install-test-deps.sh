#!/bin/sh

set -o nounset
set -o errexit

# Main apps
#brew install Caskroom/cask/firefox # or manually install/download it from Mozilla

# Main utilities
brew install \
	git \
	git-extras \
	Caskroom/cask/virtualbox \
	Caskroom/cask/vagrant \
	ansible \
	gnu-sed

# Install Ruby
brew install rbenv
rbenv install 2.2.4
rbenv rehash
echo 'eval "$(rbenv init -)"' >> ~/.bash_profile

# Needed to have qmake, needed by capybara-webkit build
brew install qt
brew linkapps qt


