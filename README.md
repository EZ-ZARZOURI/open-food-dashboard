# Dashboard Open Food Facts - Projet Symfony

## Description du projet

Application web développée avec **Symfony 8** permettant de consulter et d'exploiter des données issues de l'API **Open Food Facts** à travers un **Dashboard personnalisable**.
Chaque utilisateur peut configurer ses widgets pour afficher des produits filtrés par catégorie ou marque.

---

## Prérequis

* Docker
* Docker Compose

---

## Installation et lancement

Le projet est entièrement conteneurisé avec Docker et utilise **FrankenPHP** pour des performances optimales et le hot reload.

1. Cloner le dépôt :

```bash
git clone <URL_DU_DEPOT>
```

2. Lancer les conteneurs Docker :

```bash
docker compose up --build 
```

3. Accéder à l'application :

* **Interface d’administration des utilisateurs :** https://localhost:8000/user

  * Identifiants par défaut (Fixtures) :

    * Admin : `admin@gmail.com` / `password`

* **Dashboard :** https://localhost:8000/dashboard

---

## Sécurité

* **Authentification :** Système standard Symfony.
* **Gestion des accès :**

  * Les utilisateurs ne peuvent pas s’inscrire eux-mêmes.
  * Seul un administrateur peut créer, modifier ou supprimer des comptes.
* **Protection CSRF :** Activée sur tous les formulaires et actions critiques.
* **Isolation des données :** Chaque utilisateur ne peut voir ou modifier que ses propres widgets (vérification dans `DashboardController`).
* **Gestion complète par l’admin :** création, modification, suppression, attribution de rôles.
* **Note sur le test technique :**

  * Les fonctionnalités de 2FA et de blocage après 5 tentatives sont prévues mais non implémentées.

---

## Dashboard & Widgets

Le Dashboard est conçu comme une **collection de widgets dynamiques** :

* **Filtrage :** par catégorie ou marque via l'API Open Food Facts.
* **Persistance :** chaque configuration de widget est sauvegardée en base pour chaque utilisateur.
* **Actions disponibles pour l’utilisateur :**

  * Ajouter ou retirer des widgets
  * Modifier la configuration des widgets (type et valeur du filtre)
* **Affichage :** chaque widget montre 4 produits selon le filtre choisi.
* **Note :** le glisser-déposer pour réorganiser les widgets n’est pas encore implémenté.

---

## API Open Food Facts

* Les widgets consomment les données de l’API via le **service `ProductService`**.

---

## Stack utilisée

* **Framework :** Symfony 8
* **Serveur web :** FrankenPHP avec hot reload
* **Base de données :** PostgreSQL
* **Interface utilisateur :** Shadcn UI via Symfony UX + Tailwind CSS
* **Containerisation :** Docker + Docker Compose


