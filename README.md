# WorkNest - Guide Docker

## Prérequis
- Docker Desktop installé
- Docker Compose v2.0+

## Démarrage rapide

### 1. Configuration initiale
```bash
# Copier le fichier d'environnement Docker
copy .env.docker .env

# Ou sur Linux/Mac
cp .env.docker .env

# Générer la clé d'application Laravel (si nécessaire)
docker-compose run --rm app php artisan key:generate
```

### 2. Lancer les conteneurs
```bash
# Démarrer tous les services
docker-compose up -d

# Vérifier que tous les conteneurs sont actifs
docker-compose ps
```

### 3. Installation et migration
```bash
# Installer les dépendances PHP
docker-compose exec app composer install

# Installer les dépendances Node.js (déjà fait automatiquement)
docker-compose exec node npm install

# Exécuter les migrations
docker-compose exec app php artisan migrate --seed

# Créer le lien de stockage
docker-compose exec app php artisan storage:link

# Builder les assets CSS/JS (IMPORTANT !)
docker-compose exec node npm run build
```

### 4. Accéder à l'application
- **Application** : http://localhost:8000
- **Vite Dev Server** : http://localhost:5173
- **MySQL** : localhost:3306
- **Redis** : localhost:6379

## Commandes utiles

### Gestion des conteneurs
```bash
# Démarrer les services
docker-compose up -d

# Arrêter les services
docker-compose down

# Redémarrer un service
docker-compose restart app

# Voir les logs
docker-compose logs -f app

# Voir les logs d'un service spécifique
docker-compose logs -f web
docker-compose logs -f db
```

### Artisan
```bash
# Exécuter une commande Artisan
docker-compose exec app php artisan [commande]

# Exemples
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:list
```

### Composer
```bash
# Installer une dépendance
docker-compose exec app composer require [package]

# Mettre à jour les dépendances
docker-compose exec app composer update
```

### NPM
```bash
# Installer une dépendance
docker-compose exec node npm install [package]

# Build pour production
docker-compose exec node npm run build
```

### Base de données
```bash
# Accéder à MySQL
docker-compose exec db mysql -u worknest_user -p worknest

# Backup de la base de données
docker-compose exec db mysqldump -u worknest_user -pworknest_password worknest > backup.sql

# Restaurer un backup
docker-compose exec -T db mysql -u worknest_user -pworknest_password worknest < backup.sql
```

### Redis
```bash
# Accéder à Redis CLI
docker-compose exec redis redis-cli

# Vider le cache Redis
docker-compose exec redis redis-cli FLUSHALL
```

## Structure des conteneurs

- **app** : PHP-FPM 8.2 avec Laravel
- **web** : Nginx pour servir l'application
- **db** : MySQL 8.0
- **redis** : Redis pour cache et queues
- **node** : Node.js 20 pour Vite
- **queue** : Worker Laravel pour les jobs en arrière-plan

## Résolution de problèmes

### Assets CSS/JS non chargés
Si les styles ne s'affichent pas, c'est que les assets ne sont pas buildés :
```bash
# Builder les assets
docker-compose exec node npm run build

# Ou redémarrer le service Vite en mode dev
docker-compose restart node
```

### Erreur de permissions
```bash
# Fixer les permissions storage et cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www:www-data storage bootstrap/cache
```

### Erreur de connexion base de données
```bash
# Vérifier que MySQL est prêt
docker-compose exec db mysqladmin ping -h localhost

# Recréer la base de données
docker-compose exec app php artisan migrate:fresh --seed
```

### Vider tous les caches
```bash
docker-compose exec app php artisan optimize:clear
```

### Reconstruire les conteneurs
```bash
# Reconstruire tous les conteneurs
docker-compose build --no-cache

# Reconstruire et redémarrer
docker-compose up -d --build
```

## Mode production

Pour la production, modifiez `.env` :
```env
APP_ENV=production
APP_DEBUG=false
QUEUE_CONNECTION=redis
CACHE_STORE=redis
```

Puis lancez :
```bash
docker-compose exec app composer install --optimize-autoloader --no-dev
docker-compose exec node npm run build
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

## Arrêt et nettoyage

```bash
# Arrêter tous les services
docker-compose down

# Arrêter et supprimer les volumes (attention: perte de données!)
docker-compose down -v

# Nettoyer tous les conteneurs et images Docker
docker system prune -a
```
