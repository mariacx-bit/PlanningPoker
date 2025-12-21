# 🃏 Planning Poker

## 📌 Présentation du projet

Ce projet est une **application de Planning Poker** développée dans le cadre d’un projet académique.  
Elle permet à une équipe de **collaborer pour estimer la difficulté de tâches** (backlog) selon les règles du Planning Poker, de manière **interactive et en temps réel** grâce aux WebSockets.

L’application respecte les règles vues en cours et intègre plusieurs fonctionnalités avancées pour faciliter les séances d’estimation.

---

## 🎯 Objectifs pédagogiques

- Mettre en œuvre une **architecture MVC en PHP**
- Implémenter un **système temps réel** avec WebSockets
- Gérer la **collaboration multi-utilisateurs**
- Mettre en place :
  - des **tests automatisés**
  - une **documentation automatique**
  - une gestion de projet avec **Git/GitHub**
- Appliquer les règles du **Planning Poker**

---

## 🧩 Fonctionnalités principales

### 👥 Gestion des joueurs
- Création de parties avec un **animateur**
- Connexion des joueurs via un **pseudo**
- Visualisation en temps réel des joueurs connectés

### 📋 Gestion du backlog
- Ajout de tâches **simplement via un formulaire**
- Conversion automatique en **JSON**
- Sélection de la tâche à débattre
- Suivi de l’état des tâches :
  - en attente
  - en cours
  - validée

### 🗳️ Planning Poker
- Cartes de vote (1, 2, 3, 5, 8, 13, …)
- Carte **☕ Café** (pause)
- Votes anonymes jusqu’à la révélation
- Animation lors de la révélation des cartes
- Affichage des votes par joueur

### ⚖️ Règles de validation
- **Mode strict (unanimité)**
- **Mode moyenne**
- Premier tour toujours en unanimité
- Tours suivants selon le mode choisi
- Gestion automatique :
  - validation
  - désaccord (nouveau tour)
  - pause café

### ⏱️ Chronomètre
- Temps défini par le créateur de la partie
- Chronomètre visible pendant chaque décision

### 💾 Sauvegarde & reprise
- Sauvegarde du backlog et de l’avancement en base de données
- Possibilité de reprendre une partie interrompue
- Export du résultat final en **JSON**

---

## 🛠️ Technologies utilisées

- **Backend** : PHP 8+
- **Frontend** : HTML, CSS, JavaScript, Bootstrap
- **Temps réel** : WebSockets (Ratchet)
- **Base de données** : MySQL
- **Tests** : PHPUnit
- **Documentation automatique** : Doxygen
- **Gestion de version** : Git / GitHub

---

## 🧱 Architecture du projet


---

## ▶️ Installation et lancement

### 1️⃣ Prérequis
- PHP 8+
- Composer
- MySQL
- (Optionnel) Homebrew pour macOS

### 2️⃣ Installation des dépendances
```bash
composer install
