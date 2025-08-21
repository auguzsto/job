up:
	@docker compose up -d

enter:
	@docker exec -it phpjob-dev sh

down:
	@docker compose down