.PHONY: help install uninstall outdated proxy start stop log

default: help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

# GESTION DES PAQUETS ##################################################################################################

install: ## Installation des dépendances PHP
	composer install

uninstall: ## Désinstallation des dépendances PHP
	rm -rf vendor/*

upgrade: ## Mise à jour des dépendances PHP
	composer update

outdated: ## Vérifier que les dépendances sont à jour
	composer outdated --direct

# SERVEUR SYMFONY ######################################################################################################

proxy: ## Démarre le proxy du serveur Symfony
	symfony proxy:start

start: proxy node ## Server start
	symfony server:start -d

stop: ## Server stop
	symfony server:stop

log: ## Affichage des messages de log du serveur Symfony
	symfony server:log