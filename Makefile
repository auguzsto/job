up:
	@docker compose up --build -d

enter:
	@docker exec -it phpjob-dev sh

down:
	@docker compose down