test:
	./vendor/bin/phpunit

lint:
	./vendor/bin/php-cs-fixer fix --verbose --diff --dry-run --config-file=.php_cs

fix:
	./vendor/bin/php-cs-fixer fix --verbose --diff --config-file=.php_cs

phpstan:
	phpstan analyse src -c phpstan.neon --level=8 --no-progress -vvv
