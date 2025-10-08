NAME					=	camagru
DOCKER_COMPOSE_CMD		=	docker-compose
DOCKER_COMPOSE_PATH		=	docker-compose.yml

#  Lancer le projet (build + up)
all:
	@if [ -f ".env" ]; then \
		echo "Creation of volumes..."; \
		mkdir -p volumes/db_data; \
		echo "Starting containers..."; \
		$(DOCKER_COMPOSE_CMD) --env-file .env -p $(NAME) -f $(DOCKER_COMPOSE_PATH) up --build -d; \
	else \
		echo ".env file missing. Create a .env file before running 'make'."; \
	fi

# Stopper les conteneurs (sans les supprimer)
stop:
	$(DOCKER_COMPOSE_CMD) -p $(NAME) -f $(DOCKER_COMPOSE_PATH) stop

#  Supprimer les conteneurs (GARDE les volumes/données)
down:
	$(DOCKER_COMPOSE_CMD) -p $(NAME) -f $(DOCKER_COMPOSE_PATH) down

#  Supprimer conteneurs + volumes (EFFACE toutes les données)
clean: 
	$(DOCKER_COMPOSE_CMD) -p $(NAME) -f $(DOCKER_COMPOSE_PATH) down -v
	@echo "Data cleaned. Volumes removed."

#  Rebuild complet (force nouveau certificat)
re: down
	$(DOCKER_COMPOSE_CMD) --env-file .env -p $(NAME) -f $(DOCKER_COMPOSE_PATH) build --no-cache web
	$(DOCKER_COMPOSE_CMD) --env-file .env -p $(NAME) -f $(DOCKER_COMPOSE_PATH) up -d

#  Nettoyer base MySQL (vide le volume)
clean-db:
	docker volume rm $$(docker volume ls -q | grep "${NAME}_db_data") || true
	mkdir -p volumes/db_data

#  Logs conteneur web
logs:
	$(DOCKER_COMPOSE_CMD) -p $(NAME) -f $(DOCKER_COMPOSE_PATH) logs -f web

#  Entrer dans le conteneur web (bash)
bash:
	docker exec -it ${NAME}_web bash

#  Entrer dans le conteneur MySQL
mysql:
	docker exec -it ${NAME}_db mysql -u camagru_user -puserpassword camagru

# le HTTPS
check-https:
	@echo "🔍 Vérification HTTPS..."
	@curl -vk https://localhost:8443 || echo "❌ HTTPS ne répond pas"

.PHONY: all stop down clean restart clean-db logs bash mysql check-https
