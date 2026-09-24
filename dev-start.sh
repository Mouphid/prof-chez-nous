#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────
#  JoieEnseignante — Démarrage local (après redémarrage du PC)
#  Usage : bash dev-start.sh
#  Site : http://127.0.0.1:8080  |  Admin : /admin/login.php
# ─────────────────────────────────────────────────────────────
set -e

PROJET="/home/dev/Documents/Projet dev/prof-chez-nous"
RUN_DIR="/tmp/opencode/mysql-run"
DATA_DIR="/tmp/opencode/mysql-data"
SOCK="$RUN_DIR/mysqld.sock"
DB_NAME="joieenseignante"
EXPORT="$PROJET/sql/export_production.sql"

mkdir -p "$RUN_DIR"

# ── 1. MySQL ─────────────────────────────────────────────────
if mysqladmin --socket="$SOCK" ping >/dev/null 2>&1; then
    echo "✔ MySQL déjà démarré"
elif [ -d "$DATA_DIR/mysql" ]; then
    echo "✔ Démarrage de MySQL (datadir existant)..."
    /usr/sbin/mysqld --datadir="$DATA_DIR" --socket="$SOCK" --port=3306 \
        --pid-file="$RUN_DIR/mysqld.pid" --log-error="$RUN_DIR/error.log" &
    sleep 3
else
    echo "• Initialisation du datadir MySQL (premier lancement)..."
    mkdir -p "$DATA_DIR"
    /usr/sbin/mysqld --initialize-insecure --datadir="$DATA_DIR" --user="$(whoami)" \
        --log-error="$RUN_DIR/init.log" >/dev/null 2>&1
    echo "✔ Démarrage de MySQL..."
    /usr/sbin/mysqld --datadir="$DATA_DIR" --socket="$SOCK" --port=3306 \
        --pid-file="$RUN_DIR/mysqld.pid" --log-error="$RUN_DIR/error.log" &
    sleep 3
fi

# Attendre que le socket soit prêt
for i in $(seq 1 20); do
    mysqladmin --socket="$SOCK" ping >/dev/null 2>&1 && break
    sleep 1
done

# ── 2. Base de données ───────────────────────────────────────
if ! mysql --socket="$SOCK" -u root -e "USE $DB_NAME" >/dev/null 2>&1; then
    echo "• Création de la base '$DB_NAME'..."
    mysql --socket="$SOCK" -u root -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql --socket="$SOCK" -u root "$DB_NAME" < "$EXPORT"
    echo "✔ Données importées depuis sql/export_production.sql"
else
    echo "✔ Base '$DB_NAME' présente"
fi

# ── 3. Serveur PHP ───────────────────────────────────────────
if pgrep -f "php -S 127.0.0.1:8080" >/dev/null; then
    echo "✔ Serveur PHP déjà démarré"
else
    echo "✔ Démarrage du serveur PHP..."
    nohup php -S 127.0.0.1:8080 -t "$PROJET" >/dev/null 2>&1 &
fi

sleep 1
echo
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Site    : http://127.0.0.1:8080"
echo "  Bannières OK. Base '$DB_NAME' OK."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"