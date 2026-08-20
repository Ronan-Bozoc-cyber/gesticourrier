#!/usr/bin/env bash
# ==============================================================================
# OpenGestiCourrier - Assistant d'Installation
# ==============================================================================

# --- Couleurs et Styles ---
C_BLUE='\e[34m'
C_CYAN='\e[36m'
C_GREEN='\e[32m'
C_YELLOW='\e[33m'
C_RED='\e[31m'
C_BOLD='\e[1m'
C_RESET='\e[0m'

# --- Fonctions d'affichage ---
function draw_header() {
    clear
    echo -e "${C_CYAN} ──────────────────────────────────────────────────────────────────────${C_RESET}"
    echo -e "${C_BLUE}${C_BOLD}   ___  ____  _____ _   _    ____           _   _                  ${C_RESET}"
    echo -e "${C_BLUE}${C_BOLD}  / _ \|  _ \| ____| \ | |  / ___| ___  ___| |_(_)                 ${C_RESET}"
    echo -e "${C_BLUE}${C_BOLD} | | | | |_) |  _| |  \| | | |  _ / _ \/ __| __| |                 ${C_RESET}"
    echo -e "${C_BLUE}${C_BOLD} | |_| |  __/| |___| |\  | | |_| |  __/\__ \ |_| |                 ${C_RESET}"
    echo -e "${C_BLUE}${C_BOLD}  \___/|_|   |_____|_| \_|  \____|\___||___/\__|_|                 ${C_RESET}"
    echo -e "${C_BLUE}${C_BOLD}   ____                      _                   _   _____         ${C_RESET}"
    echo -e "${C_BLUE}${C_BOLD}  / ___|___  _   _ _ __ _ __(_) ___ _ __  __   _/ | |___ /         ${C_RESET}"
    echo -e "${C_BLUE}${C_BOLD} | |   / _ \| | | | '__| '__| |/ _ \ '__| \ \ / / |   |_ \         ${C_RESET}"
    echo -e "${C_BLUE}${C_BOLD} | |__| (_) | |_| | |  | |  | |  __/ |     \ V /| |_ ___) |        ${C_RESET}"
    echo -e "${C_BLUE}${C_BOLD}  \____\___/ \__,_|_|  |_|  |_|\___|_|      \_/ |_(_)____/         ${C_RESET}"
    echo -e "                                                                       "
    echo -e "${C_YELLOW}${C_BOLD}                    📬 ( Assistant d'Installation )                ${C_RESET}"
    echo -e "${C_CYAN} ──────────────────────────────────────────────────────────────────────${C_RESET}\n"
    echo -e " Bienvenue ! Cet utilitaire va configurer OPEN Gesti Courrier v1.3."
    echo -e " Nous allons vérifier vos prérequis, vous expliquer chaque étape,"
    echo -e " installer les modules manquants et préparer votre environnement."
    echo -e "${C_CYAN} ──────────────────────────────────────────────────────────────────────${C_RESET}\n"
}

function print_step() {
    echo -e "\n${C_BLUE}${C_BOLD}▶ $1 ${C_RESET}"
    echo -e "${C_BLUE}----------------------------------------------------------------------${C_RESET}"
}

function print_success() {
    echo -e "  ${C_GREEN}[✓] $1${C_RESET}"
}

function print_warning() {
    echo -e "  ${C_YELLOW}[!] $1${C_RESET}"
}

function print_error() {
    echo -e "  ${C_RED}[x] $1${C_RESET}"
}

function print_info() {
    echo -e "      ${C_CYAN}ℹ${C_RESET} $1"
}

function print_pedagogy() {
    echo -e "      ${C_YELLOW}💡 $1${C_RESET}"
}

# --- Fonction de confirmation ---
function ask_yes_no() {
    local prompt="$1"
    local default="$2"
    local reply
    
    if [[ "$default" == "O" || "$default" == "o" ]]; then
        prompt+=" [O/n]"
    else
        prompt+=" [o/N]"
    fi
    
    while true; do
        echo -en "  ${C_CYAN}${C_BOLD}?${C_RESET} ${prompt} : "
        read reply
        
        if [[ -z "$reply" ]]; then
            reply=$default
        fi
        
        case "$reply" in
            [OoYy]* ) return 0 ;;
            [Nn]* ) return 1 ;;
            * ) print_error "Réponse invalide. Veuillez répondre par 'o' ou 'n'." ;;
        esac
    done
}

# --- Vérification de l'utilisateur root ---
function check_root() {
    if [[ $EUID -ne 0 ]]; then
        print_error "Ce script doit être exécuté en tant que super-utilisateur (root)."
        print_info "Veuillez utiliser : sudo ./install.sh"
        exit 1
    fi
}

# --- Vérification d'un paquet debian ---
function is_dpkg_installed() {
    dpkg -l | grep -qw "$1"
    return $?
}

# --- Installation via APT ---
function apt_install() {
    local packages="$1"
    print_info "Mise à jour de la liste des paquets (apt-get update)..."
    apt-get update -qq
    print_info "Téléchargement et installation de : $packages"
    apt-get install -y -qq $packages
    if [[ $? -eq 0 ]]; then
        print_success "Paquets installés avec succès."
    else
        print_error "Erreur lors de l'installation des paquets."
        exit 1
    fi
}

# ==============================================================================
# ROUTINE 1 : Installation sur l'Hôte (Apache/PHP/MariaDB)
# ==============================================================================

function setup_host() {
    # 1. Serveur Web
    print_step "1. Le Serveur Web (Apache / Nginx)"
    print_pedagogy "Le serveur web est le cœur de l'application. Il permet d'écouter les requêtes"
    print_pedagogy "de votre navigateur et de lui renvoyer les pages de GestiCourrier."
    
    if command -v apache2 >/dev/null 2>&1 || command -v httpd >/dev/null 2>&1; then
        print_success "Serveur web Apache détecté sur votre système."
    elif command -v nginx >/dev/null 2>&1; then
        print_success "Serveur web Nginx détecté sur votre système."
    else
        print_warning "Aucun serveur web n'est installé."
        if ask_yes_no "Voulez-vous installer et configurer 'Apache2' (serveur web standard) ?" "O"; then
            apt_install "apache2"
            systemctl enable apache2 >/dev/null 2>&1
            systemctl start apache2 >/dev/null 2>&1
        else
            print_warning "Installation ignorée. (GestiCourrier nécessitera un serveur web pour s'afficher)."
        fi
    fi
    sleep 1
    
    # 2. PHP et Extensions
    print_step "2. Le Langage PHP et ses Extensions"
    print_pedagogy "GestiCourrier est codé en PHP. Pour fonctionner, il a besoin du moteur PHP"
    print_pedagogy "et de certaines 'extensions' (ex: php-zip pour créer des archives de sauvegarde,"
    print_pedagogy "php-mysql pour parler à la base de données)."
    
    local missing_pkgs=""
    
    for pkg in php libapache2-mod-php php-mysql php-mbstring php-xml php-zip php-gd; do
        if [[ "$pkg" == "libapache2-mod-php" ]] && ! command -v apache2 >/dev/null 2>&1; then
            continue
        fi
        if is_dpkg_installed "$pkg"; then
            print_success "L'extension $pkg est déjà présente."
        else
            missing_pkgs="$missing_pkgs $pkg"
        fi
    done
    
    if [[ -n "$missing_pkgs" ]]; then
        print_warning "Il vous manque ces éléments vitaux :$missing_pkgs"
        if ask_yes_no "Puis-je les installer pour vous ?" "O"; then
            apt_install "$missing_pkgs"
            if command -v apache2 >/dev/null 2>&1; then
                print_info "Redémarrage d'Apache pour qu'il prenne en compte les nouveautés..."
                systemctl restart apache2
            fi
        else
            print_warning "L'application risque d'afficher des erreurs bloquantes."
        fi
    fi
    sleep 1
    
    # 3. Base de données
    print_step "3. Le Serveur de Base de Données (MySQL/MariaDB)"
    print_pedagogy "La base de données est l'endroit où tous vos courriers, contacts et paramètres"
    print_pedagogy "seront stockés en sécurité."
    
    if command -v mysql >/dev/null 2>&1 || command -v mariadb >/dev/null 2>&1; then
        print_success "Serveur MariaDB ou MySQL détecté."
    else
        print_warning "Aucun moteur de base de données local n'a été trouvé."
        print_pedagogy "Si votre base de données se trouve sur un autre serveur, vous pouvez ignorer."
        if ask_yes_no "Voulez-vous installer 'MariaDB' sur CETTE machine ?" "O"; then
            apt_install "mariadb-server mariadb-client"
            systemctl enable mariadb >/dev/null 2>&1
            systemctl start mariadb >/dev/null 2>&1
        fi
    fi
    sleep 1
    
    # 4. Permissions
    print_step "4. Sécurisation et Permissions des fichiers"
    print_pedagogy "Le serveur web (utilisateur 'www-data') a besoin d'avoir le droit d'écrire"
    print_pedagogy "dans certains dossiers (pour stocker vos PDF ou sauvegardes) tout en"
    print_pedagogy "empêchant les utilisateurs non autorisés de lire ou modifier le code source."
    
    local app_dir
    app_dir=$(dirname "$(readlink -f "$0")")
    
    if ask_yes_no "Voulez-vous sécuriser automatiquement les dossiers de GestiCourrier ?" "O"; then
        print_info "Attribution du dossier $app_dir à l'utilisateur 'www-data'..."
        chown -R www-data:www-data "$app_dir"
        print_info "Mise en place des droits standards stricts (755 pour les dossiers, 644 pour les fichiers)..."
        find "$app_dir" -type d -exec chmod 755 {} \;
        find "$app_dir" -type f -exec chmod 644 {} \;
        chmod +x "$0"
        print_success "Sécurisation terminée !"
    fi
    sleep 1
    
    # 5. Configuration BDD
    print_step "5. Connexion à la Base de Données (.env)"
    print_pedagogy "L'application a besoin d'identifiants pour se connecter à la base de données."
    print_pedagogy "Nous allons les enregistrer dans un fichier caché ultra-sécurisé nommé '.env'."
    
    local env_file="$app_dir/.env"
    
    if [[ -f "$env_file" ]]; then
        print_warning "Un fichier de configuration '.env' existe déjà."
        if ! ask_yes_no "Voulez-vous le remplacer par de nouveaux identifiants ?" "N"; then
            return
        fi
    fi
    
    echo ""
    print_info "Veuillez entrer les identifiants de votre base de données MySQL :"
    read -p "  Serveur/Hôte [localhost] : " db_host
    db_host=${db_host:-localhost}
    read -p "  Nom d'utilisateur [root] : " db_user
    db_user=${db_user:-root}
    read -sp "  Mot de passe [] : " db_pass
    echo ""
    read -p "  Nom de la base à utiliser [courriers_db] : " db_name
    db_name=${db_name:-courriers_db}
    
    cat > "$env_file" <<EOF
DB_HOST=$db_host
DB_USER=$db_user
DB_PASS=$db_pass
DB_NAME=$db_name
EOF
    print_success "Fichier '.env' généré et sauvegardé."
    
    if command -v mysql >/dev/null 2>&1; then
        print_info "Vérification de vos identifiants SQL en temps réel..."
        local auth_str="-h $db_host -u $db_user"
        if [[ -n "$db_pass" ]]; then
            auth_str="$auth_str -p$db_pass"
        fi
        
        if mysql $auth_str -e "SELECT 1;" >/dev/null 2>&1; then
            print_success "Bingo ! La connexion SQL fonctionne."
            print_info "Création de la base de données '$db_name' si elle n'existe pas..."
            mysql $auth_str -e "CREATE DATABASE IF NOT EXISTS \`$db_name\`;"
            
            local sql_file="$app_dir/BD/schema.sql"
            if [[ -f "$sql_file" ]]; then
                print_pedagogy "J'ai trouvé un fichier décrivant la structure des tables (BD/schema.sql)."
                print_warning "ATTENTION : Si la base contient déjà des données, elles seront écrasées."
                if ask_yes_no "Voulez-vous installer/réinitialiser les tables maintenant ?" "N"; then
                    print_info "Création des tables en cours..."
                    if sed -E 's/\/\*!50017 DEFINER=`[^`]*`@`[^`]*`\*\///g; s/DEFINER=`[^`]*`@`[^`]*`//g' "$sql_file" | mysql $auth_str "$db_name"; then
                        print_success "La structure de base de données est prête !"
                    else
                        print_error "Échec de l'importation de la structure."
                    fi
                fi
            fi
        else
            print_error "La connexion a échoué. Veuillez vérifier vos identifiants dans le fichier .env plus tard."
        fi
    fi
}

# ==============================================================================
# ROUTINE 2 : Installation via Docker
# ==============================================================================

function setup_docker() {
    print_step "Installation via Docker (Conteneurs)"
    print_pedagogy "Docker permet d'enfermer GestiCourrier et toutes ses dépendances (serveur,"
    print_pedagogy "base de données, PHP) dans des 'boîtes' isolées appelées conteneurs."
    print_pedagogy "C'est la méthode la plus propre car elle ne modifie pas votre système principal !"
    
    if ! command -v docker >/dev/null 2>&1; then
        print_warning "L'outil Docker n'est pas encore installé sur votre machine."
        if ask_yes_no "Voulez-vous que je l'installe pour vous ?" "O"; then
            apt_install "docker.io docker-compose"
            systemctl enable docker >/dev/null 2>&1
            systemctl start docker >/dev/null 2>&1
            if [[ -n "$SUDO_USER" ]]; then
                usermod -aG docker "$SUDO_USER"
                print_info "J'ai autorisé votre utilisateur ($SUDO_USER) à utiliser Docker."
            fi
            print_success "Moteur Docker prêt à l'emploi !"
        else
            print_error "Sans Docker, ce mode d'installation est impossible."
            exit 1
        fi
    fi
    sleep 1
    
    local app_dir
    app_dir=$(dirname "$(readlink -f "$0")")
    local env_file="$app_dir/.env"
    
    print_step "Configuration Docker Automatique"
    if [[ ! -f "$env_file" ]]; then
        print_pedagogy "Je prépare le fichier '.env' pour que le conteneur Web puisse parler"
        print_pedagogy "au conteneur de Base de Données de façon 100% sécurisée."
        cat > "$env_file" <<EOF
DB_HOST=db
DB_USER=utilisateur
DB_PASS=mdp1234
DB_NAME=courriers_db
EOF
        print_success "Fichier d'environnement (.env) créé."
    fi
    
    print_info "Démarrage des machines virtuelles Docker en arrière-plan..."
    print_pedagogy "Docker va télécharger les images nécessaires (MariaDB, PHP/Apache) et"
    print_pedagogy "importer automatiquement la structure de la base de données. Patientez..."
    
    if command -v docker-compose >/dev/null 2>&1; then
        docker-compose up -d --build
    else
        docker compose up -d --build
    fi
    
    if [[ $? -eq 0 ]]; then
        print_success "Merveilleux ! Les conteneurs tournent parfaitement."
    else
        print_error "Une erreur est survenue lors du démarrage des conteneurs."
    fi
}

# ==============================================================================
# MAIN SCRIPT
# ==============================================================================

draw_header
check_root

echo -e "${C_CYAN}${C_BOLD}Veuillez choisir votre méthode d'installation préférée :${C_RESET}"
echo -e "  ${C_BOLD}1)${C_RESET} Classique sur la Machine Hôte (Installe Apache, PHP et MySQL sur ce système)"
echo -e "  ${C_BOLD}2)${C_RESET} Isolé via Docker (Recommandé, propre et sécurisé via des conteneurs)"
echo ""

while true; do
    read -p "  Entrez le numéro de votre choix (1 ou 2) : " mode
    case $mode in
        1)
            setup_host
            print_step "🎉 C'est terminé !"
            print_pedagogy "L'installation est achevée. GestiCourrier est désormais opérationnel."
            echo -e "  ${C_GREEN}Rendez-vous sur votre navigateur à l'adresse : ${C_BOLD}http://<IP_DE_VOTRE_SERVEUR>/${C_RESET}\n"
            break
            ;;
        2)
            setup_docker
            print_step "🎉 C'est terminé !"
            print_pedagogy "L'installation est achevée. L'application tourne via le port 8000."
            echo -e "  ${C_GREEN}Rendez-vous sur votre navigateur à l'adresse : ${C_BOLD}http://<IP_DE_VOTRE_SERVEUR>:8000/${C_RESET}\n"
            break
            ;;
        *)
            print_error "Choix invalide. Tapez 1 ou 2."
            ;;
    esac
done
