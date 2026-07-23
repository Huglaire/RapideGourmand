# Rapide & Gourmand

Application web de gestion de commandes pour une entreprise de traiteur.

Le projet comprend :

- un site vitrine permettant aux visiteurs de découvrir l'entreprise, ses menus et ses avis clients ;
- un espace utilisateur permettant la gestion du profil, des commandes et des avis ;
- un espace employé dédié à la gestion opérationnelle ;
- un espace administrateur permettant la supervision de l'application.

---

# Sommaire

- [Présentation](#présentation)
- [Technologies utilisées](#technologies-utilisées)
- [Dépendances principales](#dépendances-principales)
- [Prérequis](#prérequis)
- [Installation du projet](#installation-du-projet)
- [Environnement Docker](#environnement-docker)
- [Configuration](#configuration)
- [Base de données](#base-de-données)
- [Authentification JWT](#authentification-jwt)
- [Documentation API](#documentation-api)
- [Structure du projet](#structure-du-projet)
- [Rôles utilisateurs](#rôles-utilisateurs)
- [Déploiement](#déploiement)
- [Commandes utiles](#commandes-utiles)

---

# Présentation

Rapide & Gourmand est une application web développée avec Symfony permettant à une entreprise de traiteur de présenter ses prestations et de gérer ses commandes en ligne.

L'application possède plusieurs espaces :

## Partie publique

Accessible aux visiteurs :

- consultation des menus ;
- consultation des détails d'un menu ;
- consultation des avis clients ;
- création d'un compte.

## Espace utilisateur

Après authentification :

- consultation et modification du profil ;
- gestion des commandes ;
- consultation de l'historique des commandes ;
- dépôt d'un avis ;
- désactivation et réactivation du compte.

## Espace employé

Permet :

- la gestion des menus ;
- le suivi des commandes ;
- la modération des avis ;
- la gestion de certaines informations du site.

## Espace administrateur

Permet :

- la gestion des comptes employés ;
- l'accès aux statistiques ;
- la supervision générale de l'application.

---

# Technologies utilisées

## Backend

- PHP 8.2
- Symfony 7.4 LTS
- Doctrine ORM
- Doctrine MongoDB ODM
- MySQL / MariaDB
- MongoDB
- LexikJWTAuthenticationBundle
- Swagger / OpenAPI

## Frontend

- Twig
- HTML5
- CSS3
- Bootstrap
- JavaScript
- Symfony AssetMapper

## Outils

- Git
- GitHub
- Docker
- Docker Compose
- Composer
- Symfony CLI

## Services externes

- Railway : hébergement de production
- Brevo : SMTP envoi d'emails 

---

# Dépendances principales

Les dépendances PHP du projet sont gérées avec Composer.

Elles sont installées automatiquement avec :

composer install

Les principales dépendances utilisées sont les suivantes.

---

## Framework Symfony

Le projet utilise Symfony 7.4 LTS afin de bénéficier d'une version maintenue sur le long terme.

Principaux composants Symfony utilisés :

- `symfony/framework-bundle` : cœur de l'application Symfony ;
- `symfony/security-bundle` : gestion de l'authentification et des autorisations ;
- `symfony/twig-bundle` : moteur de templates Twig ;
- `symfony/validator` : validation des données ;
- `symfony/serializer` : sérialisation des données pour les réponses API ;
- `symfony/mailer` : gestion des emails ;
- `symfony/http-client` : communication avec des services externes ;
- `symfony/asset-mapper` : gestion des ressources front-end ;
- `symfony/stimulus-bundle` : intégration de Stimulus.

---

## Gestion des bases de données

Les données sont gérées avec Doctrine.

Bundles utilisés :

- `doctrine/orm` : mapping objet-relationnel avec MySQL/MariaDB ;
- `doctrine/doctrine-bundle` : intégration de Doctrine dans Symfony ;
- `doctrine/doctrine-migrations-bundle` : gestion des migrations ;
- `doctrine/doctrine-fixtures-bundle` : chargement des données de démonstration ;
- `doctrine/mongodb-odm-bundle` : gestion des documents MongoDB utilisés pour les statistiques.

---

## API et authentification

L'application expose une API REST sécurisée.

Bundles utilisés :

- `lexik/jwt-authentication-bundle` : authentification avec JSON Web Tokens (JWT) ;
- `nelmio/api-doc-bundle` : génération de la documentation Swagger/OpenAPI ;
- `nelmio/cors-bundle` : gestion des autorisations CORS.

---

## Outils de développement

Ces dépendances sont utilisées pendant le développement :

- `symfony/maker-bundle` : génération automatique des composants Symfony (entités, contrôleurs, etc.) ;
- `phpunit/phpunit` : tests automatisés ;
- `symfony/debug-bundle` : outils de débogage ;
- `symfony/web-profiler-bundle` : barre de debug Symfony.

---

# Prérequis

Avant d'installer le projet, les éléments suivants doivent être installés :

- PHP 8.2 ou supérieur ;
- Composer ;
- Symfony CLI ;
- Docker Desktop ;
- Docker Compose ;
- Git.

Vérification des installations :

php -v

composer -V

symfony -v

docker -v

docker compose version

---

# Installation du projet

Cloner le dépôt GitHub :

git clone https://github.com/Huglaire/RapideGourmand.git

Accéder au dossier du backend :

cd back

Installer les dépendances PHP :

composer install

Créer le fichier d'environnement local :

cp .env .env.local

Configurer les variables d'environnement nécessaires avant de démarrer l'application.

---

# Environnement Docker

Le projet utilise Docker afin de fournir un environnement de développement reproductible.

L'environnement Docker permet d'exécuter l'application Symfony ainsi que ses services associés dans des conteneurs indépendants.

La configuration Docker est définie avec Docker Compose :

- `compose.yaml` : définition principale des services ;
- `compose.override.yaml` : configuration complémentaire utilisée pour l'environnement de développement.

---

## Services Docker

L'environnement contient les services suivants :

| Service | Rôle |
|---|---|
| `php` | Conteneur PHP exécutant l'application Symfony |
| `nginx` | Serveur web utilisé pour exposer l'application |
| `database` | Base de données MySQL |
| `mongodb` | Base MongoDB utilisée pour les statistiques |

Les données des bases sont conservées grâce aux volumes Docker :

- `mysql_data` pour MySQL ;
- `mongo_data` pour MongoDB.

---

## Construction des images

Lors de la première installation du projet ou après une modification du Dockerfile :

docker compose build

---

## Démarrage des conteneurs

Pour lancer l'environnement complet en arrière-plan :

docker compose up -d

L'application est ensuite accessible à l'adresse :

http://localhost:8000

---

## Vérifier l'état des conteneurs

Pour afficher les services actifs :

docker compose ps

---

## Consulter les logs

Afficher les logs de tous les services :

docker compose logs -f

Afficher les logs d'un service spécifique :

docker compose logs -f php

ou :

docker compose logs -f nginx

---

## Arrêter les conteneurs

Pour arrêter l'environnement Docker :

docker compose down

Cette commande arrête les conteneurs mais conserve les volumes contenant les données des bases.

---

## Réinitialiser complètement l'environnement Docker

Pour supprimer également les volumes :

docker compose down -v

Attention : cette commande supprime les données persistées des bases MySQL et MongoDB.

---

## Exécuter des commandes Symfony dans le conteneur PHP

Pour accéder au conteneur PHP :

docker compose exec php bash

Les commandes Symfony peuvent ensuite être exécutées :

php bin/console cache:clear

php bin/console doctrine:migrations:migrate

php bin/console asset-map:compile

---

# Configuration

Créer un fichier :

.env.local

Ce fichier contient les variables spécifiques à l'environnement local.

Les principales variables nécessaires sont :

APP_ENV

DATABASE_URL

MONGODB_URL

JWT_PASSPHRASE

MAILER_DSN

Les informations sensibles ne doivent jamais être envoyées sur GitHub.

---

# Base de données

L'application utilise deux systèmes de stockage :

- MySQL/MariaDB pour les données relationnelles ;
- MongoDB pour le stockage des statistiques.

---

## Base relationnelle

Doctrine ORM est utilisé pour gérer les entités et les relations entre les données.

Création de la base :

php bin/console doctrine:database:create

Création du schéma avec les migrations :

php bin/console doctrine:migrations:migrate

Chargement des données de démonstration :

php bin/console doctrine:fixtures:load

---

## Base MongoDB

MongoDB est utilisé avec Doctrine MongoDB ODM.

Il permet notamment de stocker les données statistiques liées aux commandes afin de permettre leur analyse sans surcharger la base relationnelle principale.

---

# Authentification JWT

L'application utilise une authentification basée sur des JSON Web Tokens (JWT).

Le bundle utilisé est :

- `lexik/jwt-authentication-bundle`

Le système permet :

- la connexion des utilisateurs ;
- la génération d'un token JWT ;
- la sécurisation des routes de l'API ;
- la gestion des droits selon les rôles utilisateurs.

---

## Génération des clés JWT

Les clés nécessaires à la signature des tokens sont générées avec :

php bin/console lexik:jwt:generate-keypair

Les clés générées permettent de signer et vérifier les tokens d'authentification.

---

# Documentation API

La documentation de l'API est générée avec Swagger/OpenAPI grâce au bundle :

- `nelmio/api-doc-bundle`

Elle permet :

- de consulter les routes disponibles ;
- de visualiser les paramètres attendus ;
- de tester les endpoints directement depuis une interface web.

Une fois l'application lancée, la documentation est accessible à l'adresse :

http://localhost:8000/api/doc

---

# Structure du projet

L'organisation principale du projet suit l'architecture Symfony :

src/

    Controller/
    Entity/
    Repository/
    Service/
    Document/
    Security/

templates/

assets/

config/

migrations/

---

## Rôle des principaux dossiers

### Controller

Contient la gestion des requêtes HTTP et des différentes routes de l'application.

### Entity

Contient les entités Doctrine représentant les données relationnelles.

### Repository

Contient les classes permettant l'accès et les requêtes sur les données.

### Service

Contient la logique métier réutilisable.

### Document

Contient les documents MongoDB utilisés avec Doctrine MongoDB ODM.

### Templates

Contient les vues Twig utilisées pour le rendu côté serveur.

### Assets

Contient les fichiers JavaScript et CSS gérés par Symfony AssetMapper.

### Migrations

Contient l'historique des évolutions du schéma de base de données.

---

# Rôles utilisateurs

L'application possède plusieurs niveaux d'accès définis avec le système de rôles Symfony.

---

## ROLE_USER

Correspond aux utilisateurs classiques.

Accès :

- consultation des menus ;
- création et gestion de commandes ;
- consultation de l'historique des commandes ;
- modification du profil ;
- dépôt d'avis.

---

## ROLE_EMPLOYEE

Correspond aux employés de l'entreprise.

Ce rôle hérite de `ROLE_USER`.

Accès supplémentaires :

- gestion des menus ;
- suivi des commandes ;
- modération des avis ;
- gestion des informations nécessaires au fonctionnement du site.

---

## ROLE_ADMIN

Correspond aux administrateurs.

Ce rôle hérite de `ROLE_EMPLOYEE`.

Accès supplémentaires :

- gestion des comptes employés ;
- accès aux statistiques ;
- administration générale de l'application.

---

# Déploiement

L'application est déployée sur Railway.

Le déploiement utilise un environnement Docker permettant de reproduire la configuration locale en production.

Les éléments nécessaires au déploiement sont :

- l'image Docker de l'application ;
- les variables d'environnement de production ;
- la connexion aux bases de données ;
- les clés JWT ;
- la configuration du service d'envoi d'emails.

---

## Variables d'environnement

Les variables sensibles doivent être configurées directement dans l'environnement de production.

Elles comprennent notamment :

- paramètres de connexion aux bases de données ;
- clés JWT ;
- configuration du service mail ;
- paramètres Symfony.

---

# Commandes utiles

## Symfony

Vider le cache :

php bin/console cache:clear

Lancer les migrations :

php bin/console doctrine:migrations:migrate

Charger les fixtures :

php bin/console doctrine:fixtures:load

Compiler les assets :

php bin/console asset-map:compile

---

## Composer

Installer les dépendances :

composer install

Mettre à jour les dépendances :

composer update

---

## Docker

Démarrer les conteneurs :

docker compose up -d

Arrêter les conteneurs :

docker compose down

Reconstruire les images :

docker compose build

Afficher les conteneurs actifs :

docker compose ps

---

# Auteur

Hugo Pollon