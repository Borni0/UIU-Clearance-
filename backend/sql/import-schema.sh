
set -e
DIR="$(cd "$(dirname "$0")" && pwd)"
mysql -u uiu_app -p uiu_clearance < "$DIR/schema.sql"
echo "Schema imported successfully."
