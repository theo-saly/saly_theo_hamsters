## Auteur
- Développé par Theo S.

# Symfony Hamster Game API

API backend pour un jeu de gestion de hamsters avec utilisateurs et gold. Deux rôles : ROLE_USER et ROLE_ADMIN.

---

## Prérequis

- PHP >= 8.1
- Composer
- Symfony
- Base de données MySQL
- Postman pour tester l’API

---

## Installation

1. Cloner le projet
```bash
git clone https://github.com/TheoSly/saly_theo_hamsters.git
cd saly_theo_hamsters
```

2. Installer les dépendances
```bash
composer install
```

3. Configurer l’environnement
```bash
cp .env .env.local
```
Éditez `.env.local` pour la base de données et JWT :
```dotenv
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
```

4. Créer la base de données
```bash
php bin/console doctrine:database:create
```

5. Créer les tables
```bash
php bin/console doctrine:migrations:migrate
```

6. Charger les fixtures
```bash
php bin/console doctrine:fixtures:load
```
> ⚠️ Attention : supprime toutes les données existantes.

7. Générer les clés JWT
```bash
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

Ajouter la passphrase dans `.env.local` :
```dotenv
JWT_PASSPHRASE="votre_passphrase"
```

---

## Lancer le serveur Symfony
```bash
symfony server:start
```
API accessible sur : http://127.0.0.1:8000

---

## Routes API

### Utilisateurs
| Méthode | Route | Body | Accès | Description |
|---------|-------|------|-------|-------------|
| POST | `/api/register` | `{ "email": "...", "password": "..." }` | Public | Crée un utilisateur et 4 hamsters par défaut |
| POST | `/api/login` | `{ "email": "...", "password": "..." }` | Public | Retourne un token JWT |
| DELETE | `/api/delete/{id}` | - | Admin | Supprime un utilisateur et ses hamsters |
| GET | `/api/user` | - | Auth | Retourne l’utilisateur courant et ses hamsters |

### Hamsters
| Méthode | Route | Body | Accès | Description |
|---------|-------|------|-------|-------------|
| GET | `/api/hamsters` | - | Auth | Retourne tous les hamsters de l’utilisateur |
| GET | `/api/hamsters/{id}` | - | Auth/Admin | Retourne un hamster spécifique |
| POST | `/api/hamsters/reproduce` | `{ "idHamster1": xx, "idHamster2": yy }` | Auth/Admin | Crée un nouveau hamster |
| POST | `/api/hamsters/{id}/feed` | - | Auth/Admin | Nourrit le hamster et débite le gold |
| POST | `/api/hamsters/{id}/sell` | - | Auth/Admin | Vend le hamster pour 300 gold |
| POST | `/api/hamsters/sleep/{nbDays}` | - | Auth | Vieillit tous les hamsters de l’utilisateur |
| PUT | `/api/hamsters/{id}/rename` | `{ "name": "nouveauNom" }` | Auth/Admin | Renomme un hamster |

---

## Règles du jeu
- Gold initial : 500
- Vieillissement automatique (+5 jours, -5 hunger) après feed, sell, reproduce
- Hamster inactive si age > 500 ou hunger < 0
- Fin de jeu : gold <= 0 → toutes les requêtes renvoient 400
- Les admins ne sont pas affectés

---
