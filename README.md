# Gestion Evenements - Symfony

Application web de gestion d'evenements et de reservations.

## Installation

```bash
# Installer les dependances
composer install

# Creer la base de donnees et executer les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction

# Creer les utilisateurs de test
php bin/console app:create-user admin@test.com admin --admin
php bin/console app:create-user user@test.com user
```

## Lancer le serveur

```bash
php -S localhost:8000 -t public
```

Puis ouvrir : http://localhost:8000

## Identifiants de test

| Email | Mot de passe | Role |
|-------|--------------|------|
| admin@test.com | admin | ROLE_ADMIN |
| user@test.com | user | ROLE_USER |

## Migrations

Les migrations sont situees dans le dossier :
```
migrations/Version20260112095956.php
```

Migration deja executee - Base de donnees SQLite : `var/data.db`

## Structure du projet

- `src/Entity/` : Entites Doctrine (Event, Reservation, User)
- `src/Controller/` : Controleurs (EventController, AdminEventController, SecurityController)
- `src/Form/` : FormType (EventType)
- `src/Repository/` : Repositories avec methodes custom (findUpcomingEvents, findOneByUserAndEvent)
- `templates/` : Templates Twig avec heritage (base.html.twig)
- `config/packages/security.yaml` : Configuration securite (login, roles, access_control)
