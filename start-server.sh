
DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$DIR"
echo "Serving UIU Clearance at http://localhost:8000"
echo "Press Ctrl+C to stop."
exec php -S localhost:8000
