# OPEN Gesti Courrier v1.3 📬

## Présentation de la GED
**OPEN Gesti Courrier** est un système de Gestion Électronique de Documents (GED) moderne et open source, spécialement conçu pour la gestion optimisée, centralisée et sécurisée des correspondances d'une organisation. 

L'application permet d'enregistrer, de suivre, d'archiver et de purger légalement les courriers entrants et sortants au sein d'une interface utilisateur intuitive et réactive. Elle vise à dématérialiser complètement le traitement administratif tout en assurant une traçabilité rigoureuse.

---

## Configuration et Prérequis
Cette application est architecturée pour fonctionner de manière optimale sur des serveurs **Linux**. 

Les distributions recommandées et officiellement supportées sont :
- **Debian** (11 Bullseye, 12 Bookworm ou supérieur)
- **Ubuntu** (20.04 LTS, 22.04 LTS, 24.04 LTS)

---

## Technologies Utilisées
L'application repose sur un socle technologique robuste, éprouvé et facile à maintenir :
- **Backend :** PHP (Orienté Procédural et Objet léger)
- **Base de données :** MySQL / MariaDB
- **Frontend :** HTML5, CSS3, JavaScript (Vanilla JS), Bootstrap
- **Génération de Documents :** FPDF (génération dynamique de certificats et rapports PDF)
- **Infrastructure Serveur :** Apache2 ou Nginx
- **Conteneurisation :** Docker et Docker Compose (déploiement isolé)

---

## Comment l'installer
Gesti Courrier propose un utilitaire d'installation en ligne de commande ultra-intuitif et interactif en Bash (avec un magnifique en-tête en ASCII Art !), qui gère automatiquement la détection et l'installation de toutes les dépendances.

1. **Cloner le dépôt sur votre serveur :**
   ```bash
   git clone <votre-url-github>
   cd GestiCourrier
   ```

2. **Lancer l'assistant d'installation (nécessite les privilèges super-utilisateur) :**
   ```bash
   chmod +x install.sh
   sudo ./install.sh
   ```

3. **Suivre le guide interactif :**
   L'assistant vous demandera de choisir entre deux méthodes de déploiement :
   - **Machine Hôte (Classique) :** L'outil installe automatiquement Apache, les extensions PHP manquantes et MariaDB directement sur votre système, tout en configurant les droits d'accès sécurisés (`www-data`).
   - **Docker (Recommandé) :** Isole l'application et la base de données dans des conteneurs via Docker Compose, en configurant automatiquement le fichier d'environnement.

---

## Fonctionnalités Détaillées
OPEN Gesti Courrier propose un ensemble complet de modules pensés pour les flux de travail professionnels :

### 📥 Gestion des Courriers Entrants
- **Enregistrement et Indexation :** Numérotation chronologique, date de réception, sélection de l'expéditeur via l'annuaire, et définition de l'objet du courrier.
- **Catégorisation :** Classement précis par types (Facture, Lettre recommandée, Circulaire, etc.) pour des statistiques claires.
- **Gestion des Pièces Jointes :** Téléversement sécurisé des fichiers liés au courrier (PDF, Word, Excel, images) sur le serveur.
- **Statut de Traitement :** Suivi visuel du cycle de vie du courrier (À traiter, En cours, Clôturé).

### 📤 Gestion des Courriers Sortants
- **Liaison Intelligente "Entrant-Sortant" :** Capacité unique de lier un courrier sortant (réponse) à un courrier entrant spécifique, générant ainsi un historique complet de correspondance.
- **Carnet de Destinataires :** Sélection rapide et autocomplétée depuis les contacts enregistrés.
- **Suivi des expéditions :** Date d'envoi, nature du courrier envoyé et fichiers associés.

### 📇 Annuaire des Contacts centralisé
- Base de données unifiée des expéditeurs et destinataires fréquents.
- Modules de création, modification, et recherche instantanée pour fluidifier la saisie documentaire.

### 🔍 Moteur de Recherche et Tableaux de Bord
- **Recherche Multi-critères :** Filtrez instantanément la base par mots-clés, expéditeur/destinataire, plages de dates, ou catégories.
- **Rapports Complets :** Les tableaux de résultats (exportables en PDF, Word, Excel) affichent désormais dynamiquement le **nombre de documents/fichiers liés** à chaque enregistrement.

### ⚙️ Paramétrage et Administration
- **Gestion Dynamique des Catégories :** Les administrateurs peuvent créer et éditer la liste des catégories de courriers selon les besoins de leur structure.
- **Sauvegarde Globale :** Module permettant la génération et le téléchargement d'archives compressées (ZIP) contenant une sauvegarde SQL intégrale, protégeant ainsi l'organisation contre la perte de données.
- **(Fonctionnalité à venir) Restauration système :** Restauration instantanée d'une sauvegarde existante.
- **(Fonctionnalité à venir) Verrouillage "Take the Lead" :** Système de prise de contrôle exclusif empêchant la modification simultanée des données par plusieurs collaborateurs.

### ⚖️ Module de Purge Légale (Conformité RGPD)
- **Purge Réglementaire :** Mécanisme de suppression automatisée et définitive des données obsolètes selon des durées de conservation personnalisables (ex: 3 ans, 4 ans, 5 ans).
- **Cohérence Structurelle (File-linking) :** L'algorithme vérifie en temps réel les liens entre courriers entrants et sortants. Il empêche la destruction isolée d'un courrier s'il fait partie d'une chaîne de correspondance dont une partie doit encore être conservée.
- **Sélection Manuelle (Opt-out) :** Liste récapitulative pré-cochée permettant aux opérateurs d'exclure ponctuellement certains courriers "sensibles" ou "historiques" de la destruction de masse.
- **Traçabilité et Preuve Juridique :** À l'issue de chaque session de purge, le système consigne l'opération dans un journal d'audit immuable (`destruction_logs`). Il génère instantanément un **Certificat de Destruction au format PDF** attestant de la date, de l'heure, de l'opérateur responsable et listant exhaustivement les références des courriers détruits et purgés du serveur.
