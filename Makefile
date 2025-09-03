up:
	@echo ">> Ensure ./src exists"
	@mkdir -p src
	@echo ">> Build & up as UID=$(UID) GID=$(GID)"
	@UID=$(UID) GID=$(GID) docker compose up -d --build
	@echo ">> Bootstrap Laravel into ./src only if missing"
	@docker compose exec -T app sh -lc "cd /var/www/html && \
		if [ ! -f composer.json ] || [ ! -f artisan ] || [ ! -f public/index.php ]; then \
			echo '[bootstrap] Laravel not found — creating skeleton...'; \
			tmpdir=\$$(mktemp -d); \
			composer create-project laravel/laravel \$$tmpdir; \
			tar -C \$$tmpdir -cf - . | tar -C . -xvf - --keep-old-files; \
			rm -rf \$$tmpdir; \
		else \
			echo '[bootstrap] Laravel detected — skipping create-project'; \
		fi"
	@echo ">> Ensure .env in ./src"
	@docker compose exec -T app sh -lc "cd /var/www/html && \
		if [ ! -f .env ]; then \
			if [ -f .env.example ]; then cp .env.example .env; \
			else \
				printf 'APP_NAME=Laravel\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost\n\nDB_CONNECTION=mysql\nDB_HOST=db\nDB_PORT=3306\nDB_DATABASE=laravel\nDB_USERNAME=laravel\nDB_PASSWORD=secret\n' > .env; \
			fi; \
		fi"
	@echo ">> Composer install"
	@docker compose exec -T app composer install --no-interaction
	@echo ">> Generate app key if empty"
	@docker compose exec -T app sh -lc "grep -qE '^APP_KEY=base64:' .env || php artisan key:generate || true"
	@echo ">> Storage symlink"
	@docker compose exec -T app php artisan storage:link || true
	@echo ">> Safe permissions for storage & cache"
	@docker compose exec -T app sh -lc "mkdir -p storage bootstrap/cache && chmod -R ug+rwX storage bootstrap/cache"
	@echo ">> Clear caches"
	@docker compose exec -T app php artisan optimize:clear || true
	@echo ">> Reload Nginx"
	@docker compose exec -T web nginx -s reload || true
	@echo ">> Done! Open http://localhost"
