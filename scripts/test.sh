#!/usr/bin/env bash

set -xeo pipefail

export PATH=~/.docksal/bin:${PATH} # Use binaries (docker-compose) installed by Docksal
export BEHAT_IMAGE=docksal/behat:dev

cd example

echo
echo "Checking Behat version..."
docker run --rm -v $(pwd):/src ${BEHAT_IMAGE} --version

echo
echo "Testing Behat with goutte..."
docker run --rm -v $(pwd):/src ${BEHAT_IMAGE} --version
docker run --rm -v $(pwd):/src ${BEHAT_IMAGE} --colors features/blackbox.feature

echo
echo "Testing Behat with Selenium Chrome..."
docker-compose images
docker-compose up -d
./run-behat features/blackbox-javascript.feature
