# Projet Symfony - Code Promo & Export Produits

## Installation
1. Cloner le projet :
   ```bash
   git clone https://github.com/mon-repo.git
   cd mon-repo

   composer install
   npm install && npm run dev

## Lancer l'environnement Docker
docker-compose up -d

## Acceder a mailpit
http://localhost:8025/

## Preparer la base de donnees
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:fixtures:load -n
