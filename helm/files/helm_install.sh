#!/bin/bash

# Usage: ./helm_install.sh <release-name> <domain>
# Example: ./helm_install.sh test3 test.com

RELEASE=$1
DOMAIN=$2

if [[ -z "$RELEASE" || -z "$DOMAIN" ]]; then
  echo "❌ Usage: $0 <release-name> <domain>"
  exit 1
fi

# Current script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Go back to Helm chart root (assumes ../ from script dir)
CHART_DIR="$(dirname "$SCRIPT_DIR")"
VALUES_FILE="$SCRIPT_DIR/../values.yaml"

echo "🚀 Installing Helm release: $RELEASE"
echo "🌐 Domain: $DOMAIN"
echo "📂 Helm Chart Dir: $CHART_DIR"
echo "📄 Using values file: $VALUES_FILE"

# Change to Helm chart directory
cd "$CHART_DIR" || {
  echo "❌ Failed to change to Helm chart directory: $CHART_DIR"
  exit 1
}

# Run Helm install from chart dir
helm install "$RELEASE" . \
  --values "$VALUES_FILE" \
  --set domain="$DOMAIN"

if [[ $? -eq 0 ]]; then
  echo "✅ Helm release '$RELEASE' installed successfully!"
else
  echo "❌ Helm install failed."
fi