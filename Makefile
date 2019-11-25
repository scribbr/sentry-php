gc -am .PHONY: test
DIR := ${CURDIR}

develop: update-submodules
	composer install --dev
	make setup-git

update-submodules:
	git submodule init
	git submodule update

cs:
	docker run --rm -v $(DIR):/project -w /project jakzal/phpqa:alpine php-cs-fixer fix --verbose --diff

cs-dry-run:
	docker run --rm -v $(DIR):/project -w /project jakzal/phpqa:alpine php-cs-fixer fix --verbose --diff --dry-run

cs-fix:
	docker run --rm -v $(DIR):/project -w /project jakzal/phpqa:alpine php-cs-fixer fix

phpstan:
	vendor/bin/phpstan analyse

test: cs-fix phpstan
	vendor/bin/phpunit --verbose

setup-git:
	git config branch.autosetuprebase always
