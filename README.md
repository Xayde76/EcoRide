# EcoRide – Guide de déploiement local

Plateforme de covoiturage écologique. Stack : PHP 8.2 · Apache · MySQL 8 · MongoDB · Docker.

---

## Prérequis

- [Docker](https://www.docker.com/) et Docker Compose installés
- Git

---

## Installation

### 1. Cloner le dépôt

```bash
git clone <url-du-repo>
cd EcoRide
```

### 2. Configurer les variables d'environnement

```bash
cp .env.example .env
```

Les valeurs par défaut du `.env.example` fonctionnent directement en local, aucune modification nécessaire pour démarrer.

### 3. Lancer les conteneurs

```bash
docker compose up -d --build
```

Cela démarre :
| Conteneur | Rôle | Port local |
|---|---|---|
| `ecoride_web` | Apache + PHP 8.2 | [http://localhost:8080](http://localhost:8080) |
| `ecoride_db` | MySQL 8.0 | `3307` |
| `ecoride_phpmyadmin` | phpMyAdmin | [http://localhost:8081](http://localhost:8081) |
| `ecoride_mongodb` | MongoDB (stats) | `27017` |

### 4. Importer le schéma de base de données

1. Ouvre [http://localhost:8081](http://localhost:8081) (phpMyAdmin)
2. Connecte-toi avec :
   - **Utilisateur** : `ecouser`
   - **Mot de passe** : `ecopass`
3. Sélectionne la base `ecoride`
4. Onglet **SQL**, colle le contenu du fichier `www/BDD/BDD.txt` et exécute

### 5. Ajouter la colonne manquante

La colonne `heure_arrivee` n'est pas dans le schéma initial. Exécute ce SQL dans phpMyAdmin :

```sql
ALTER TABLE covoiturage
ADD COLUMN heure_arrivee TIME NULL AFTER heure_depart;
```

### 6. Vérifier

Ouvre [http://localhost:8080](http://localhost:8080) — l'application doit s'afficher.

---

## Créer un compte administrateur

Par défaut, aucun admin n'existe. Pour en créer un manuellement via phpMyAdmin :

```sql
-- 1. Créer l'utilisateur (mot de passe : Admin1234)
INSERT INTO utilisateurs (nom, email, password, role_id, actif)
VALUES ('Admin', 'admin@ecoride.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1);

-- 2. Lui attribuer un rôle métier
INSERT INTO roles_utilisateurs (utilisateur_id, role)
VALUES (LAST_INSERT_ID(), 'passager');
```

> Le hash correspond au mot de passe `password`. Change-le après connexion.

---

## Arrêter / relancer

```bash
# Arrêter sans supprimer les données
docker compose stop

# Relancer
docker compose start

# Tout supprimer (données incluses)
docker compose down -v
```

---

## Structure du projet

```
EcoRide/
├── docker-compose.yml      # Orchestration des services
├── Dockerfile              # Image PHP + Apache
├── .env.example            # Variables d'environnement à copier en .env
├── www/                    # Code source PHP
│   ├── index.php
│   ├── pages/              # Pages du site
│   ├── actions/            # Endpoints AJAX (POST)
│   ├── classes/            # Managers (UserManager, VoyageManager…)
│   ├── services/           # Services utilitaires (Logger, Validation…)
│   ├── assets/             # CSS / JS / images
│   ├── BDD/BDD.txt         # Schéma SQL à importer
│   └── logs/               # Logs applicatifs (ignorés par git)
├── apache/                 # Config Apache
├── mongo-init/             # Init MongoDB
└── php/                    # Config PHP
```

---

## Déploiement en production (Railway)

Le projet est configuré pour Railway. Les variables d'environnement sont à renseigner dans le dashboard Railway. Se référer aux commentaires du fichier `.env.example` pour les valeurs de production.
