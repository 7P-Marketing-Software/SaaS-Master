#!/bin/bash

set -euo pipefail  # Exit on error, undefined var, or pipe failure

LOG_FILE="./mysql_db_creation.log"
exec > >(tee -a "$LOG_FILE") 2>&1

log_with_timestamp() {
  echo "------ $(date '+%Y-%m-%d %H:%M:%S') ------"
  echo "$1"
}
log_with_timestamp "Starting MySQL database and user creation script..."

# Help message
usage() {
  echo "Usage: $0 --host HOST --port PORT --admin-user ADMIN_USER --admin-pass ADMIN_PASS \\"
  echo "          --db-name DB_NAME --new-user NEW_USER --new-pass NEW_PASS --permissions PERMS"
  echo ""
  echo "Example:"
  echo "  $0 --host localhost --port 3306 --admin-user root --admin-pass root123 \\"
  echo "     --db-name test_db --new-user test_user --new-pass user123 --permissions ALL PRIVILEGES"
  exit 1
}

# Parse arguments
while [[ $# -gt 0 ]]; do
  key="$1"
  case $key in
    --host) HOST="$2"; shift; shift ;;
    --port) PORT="$2"; shift; shift ;;
    --admin-user) ADMIN_USER="$2"; shift; shift ;;
    --admin-pass) ADMIN_PASS="$2"; shift; shift ;;
    --db-name) DB_NAME="$2"; shift; shift ;;
    --new-user) NEW_USER="$2"; shift; shift ;;
    --new-pass) NEW_PASS="$2"; shift; shift ;;
    *) echo "Unknown argument: $1"; usage ;;
  esac
done

# Validate required arguments
: "${HOST:?Missing --host}"
: "${PORT:?Missing --port}"
: "${ADMIN_USER:?Missing --admin-user}"
: "${ADMIN_PASS:?Missing --admin-pass}"
: "${DB_NAME:?Missing --db-name}"
: "${NEW_USER:?Missing --new-user}"
: "${NEW_PASS:?Missing --new-pass}"

# Connect and execute SQL
echo "🔐 Connecting to MySQL and creating database/user..."

MYSQL_CMD="mysql -h$HOST -P$PORT -u$ADMIN_USER -p$ADMIN_PASS -e"

echo "🔍 Checking if database '$DB_NAME' already exists..."
DB_EXISTS=$(mysql --batch --skip-column-names -h"$HOST" -P"$PORT" -u"$ADMIN_USER" -p"$ADMIN_PASS" -e \
"SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$DB_NAME';")

if [[ -n "$DB_EXISTS" ]]; then
  echo "❌ Error: Database '$DB_NAME' already exists. Aborting."
  exit 1
fi

echo "🔍 Checking if user '$NEW_USER' already exists..."
USER_EXISTS=$(mysql --batch --skip-column-names -h"$HOST" -P"$PORT" -u"$ADMIN_USER" -p"$ADMIN_PASS" mysql -e \
"SELECT User FROM user WHERE User = '$NEW_USER';")

if [[ -n "$USER_EXISTS" ]]; then
  echo "❌ Error: User '$NEW_USER' already exists. Aborting."
  exit 1
fi


# Create database if it doesn't exist
$MYSQL_CMD "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`;"

# Create user if it doesn't exist
$MYSQL_CMD "CREATE USER IF NOT EXISTS '$NEW_USER'@'%' IDENTIFIED BY '$NEW_PASS';"

# Grant permissions
$MYSQL_CMD "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$NEW_USER'@'%'; FLUSH PRIVILEGES;"

echo "✅ Database '$DB_NAME' and user '$NEW_USER' created with 'ALL PRIVILEGES' permissions."

sed -i '/^DB_HOST=/d;/^DB_PORT=/d;/^DB_DATABASE=/d;/^DB_USERNAME=/d;/^DB_PASSWORD=/d' .env

echo "🔐 Updating .env file with database credentials..."

cat <<EOF >> .env
DB_HOST=$HOST
DB_PORT=$PORT
DB_DATABASE=$DB_NAME
DB_USERNAME=$NEW_USER
DB_PASSWORD=$NEW_PASS
EOF

echo "✅ .env file updated with database credentials."
echo "🔐 Database setup completed successfully."
