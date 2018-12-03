test: install
	php --version
	PHP_ERROR_EXCEPTION_DEPRECATIONS=1 vendor/bin/phpunit --no-coverage

coverage: install
	phpdbg --version
	PHP_ERROR_EXCEPTION_DEPRECATIONS=1 phpdbg -qrr vendor/bin/phpunit

open-coverage:
	open coverage/index.html

lint: test/bin/php-cs-fixer
	test/bin/php-cs-fixer fix --using-cache no

install:
	composer install

.PHONY: test coverage open-coverage lint install

test/bin/php-cs-fixer:
	curl -sSL https://cs.sensiolabs.org/download/php-cs-fixer-v2.phar -o test/bin/php-cs-fixer
	chmod +x test/bin/php-cs-fixer
