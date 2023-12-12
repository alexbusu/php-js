init:
	docker run --rm -v $$(pwd):/pkg --workdir=/pkg composer:2.6.5 install

composer-update:
	docker run --rm -v $$(pwd):/pkg --workdir=/pkg composer:2.6.5 update

composer-env:
	docker run --rm -v $$(pwd):/pkg --workdir=/pkg -it --entrypoint=/bin/sh composer:2.6.5

phpunit:
	docker run --rm -v $$(pwd):/pkg --workdir=/pkg php:8.3.0-cli-alpine vendor/bin/phpunit

psalm:
	docker run --rm -v $$(pwd):/pkg --workdir=/pkg php:8.3.0-cli-alpine vendor/bin/psalm.phar --show-info=true

psalm-alter:
	docker run --rm -v $$(pwd):/pkg --workdir=/pkg php:8.3.0-cli-alpine vendor/bin/psalm.phar --alter --issues=all

rector-dry-run:
	docker run --rm -v $$(pwd):/pkg --workdir=/pkg php:8.3.0-cli-alpine vendor/bin/rector --dry-run

rector:
	docker run --rm -v $$(pwd):/pkg --workdir=/pkg --user 1000 php:8.3.0-cli-alpine vendor/bin/rector
