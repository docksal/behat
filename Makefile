-include env_make

FROM ?= alpine:3.9
VERSION ?= dev
TAG ?= $(VERSION)

REPO ?= docksal/behat
NAME = docksal-behat

.PHONY: build test push shell run start stop logs clean release

build:
	docker build -t $(REPO):$(TAG) --build-arg FROM=$(FROM) .

test:
	IMAGE=$(REPO):$(TAG) NAME=$(NAME) VERSION=$(VERSION) ./tests/test.bats

push:
	docker push $(REPO):$(TAG)

shell: clean
	docker run --rm --name $(NAME) -it $(PORTS) $(VOLUMES) $(ENV) $(REPO):$(TAG) /bin/bash

run: clean
	docker run --rm --name $(NAME) -it $(PORTS) $(VOLUMES) $(ENV) $(REPO):$(TAG)

start: clean
	docker run -d --name $(NAME) $(PORTS) $(VOLUMES) $(ENV) $(REPO):$(TAG)

exec:
	docker exec $(NAME) /bin/bash -c "$(CMD)"

stop:
	docker stop $(NAME)

logs:
	docker logs $(NAME)

clean:
	docker rm -f $(NAME) >/dev/null 2>&1 || true

release: build push

default: build
