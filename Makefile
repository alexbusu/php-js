init:
	docker run --rm -v $$(pwd):/pkg --workdir=/pkg composer:2.6.5 install

composer-update:
	docker run --rm -v $$(pwd):/pkg --workdir=/pkg composer:2.6.5 update

composer-update-lowest:
	docker run --rm -v $$(pwd):/pkg --workdir=/pkg composer:2.6.5 update --prefer-lowest

composer-shell:
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

define DOUBLE_HASH
#
endef

publish:
	@if [ "$$(git status -sb | head -1)" != '## master...origin/master' ]; then \
		echo "You can only publish from master branch!"; \
		read -p "Do you want to checkout the origin/master? [y/N] " push; \
		if [ $${push:-N} = "y" ]; then \
			git fetch && git checkout -B master origin/master; \
		else \
			echo "> Aborting checkout and publish."; \
			exit 2; \
		fi; \
	fi;
	@read -p "Enter the new version: " version; \
	git tag -a $$version -m "Release $$version"; \
	git push origin $$version
