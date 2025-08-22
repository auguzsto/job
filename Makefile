up:
	@docker compose up --build -d

composer.install:
	@docker exec phpjob-dev composer install

enter:
	@docker exec -it phpjob-dev sh

down:
	@docker compose down