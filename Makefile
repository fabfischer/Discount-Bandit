include .env
export WWWUSER=${WWWUSER:-$UID}
export WWWGROUP=${WWWGROUP:-$(id -g)}

.PHONY: check-requirements
check-requirements:
	@command -v docker > /dev/null || (echo "Docker is not installed" && exit 1)
	@command -v docker-compose > /dev/null || (echo "Docker-compose is not installed" && exit 1)
	@test -f .env || (echo ".env file is missing" && exit 1)

.PHONY: setup
setup:
	$(MAKE) check-requirements
	docker-compose build
	$(MAKE) start
	until </dev/tcp/localhost/${FORWARD_DB_PORT}; do sleep 1; done
	docker-compose exec -u www-data php-fpm composer install
	# $(MAKE) migrations.migrate

.PHONE: destroy
destroy:
	docker-compose down -v

.PHONY: start
start:
	$(MAKE) check-requirements
	docker-compose up -d

.PHONY: stop
stop:
	docker-compose down

.PHONY: shell
shell:
	docker-compose exec -u www-data php-fpm /bin/bash

.PHONY: shell-root
shell-root:
	docker-compose exec php-fpm /bin/bash

.PHONY: migrations.migrate
migrations.migrate:
	docker-compose exec -u www-data php-fpm php artisan migrate --force

.PHONY: restart-worker
restart-worker:
	docker-compose exec php-fpm supervisorctl restart laravel-worker:*
