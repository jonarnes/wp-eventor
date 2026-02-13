#!/bin/bash
# Script to create a clean WordPress plugin distribution zip
# Excludes development files and dependencies

PLUGIN_NAME="eventor-integration"
VERSION=$(grep "Version:" eventor-integration.php | sed 's/.*Version: *\([0-9.]*\).*/\1/')
ZIP_NAME="${PLUGIN_NAME}-${VERSION}.zip"
TEMP_DIR=$(mktemp -d)

echo "Building ${PLUGIN_NAME} version ${VERSION}..."

# Copy files to temp directory, excluding unwanted files
rsync -av \
  --exclude='node_modules' \
  --exclude='.git' \
  --exclude='.gitignore' \
  --exclude='.distignore' \
  --exclude='package.json' \
  --exclude='package-lock.json' \
  --exclude='assets/js/blocks/events-block.js' \
  --exclude='*.map' \
  --exclude='.DS_Store' \
  --exclude='build-plugin.sh' \
  --exclude='*.log' \
  ./ "${TEMP_DIR}/${PLUGIN_NAME}/"

# Create zip file
cd "${TEMP_DIR}"
zip -r "${ZIP_NAME}" "${PLUGIN_NAME}" > /dev/null
cd - > /dev/null

# Move zip to current directory
mv "${TEMP_DIR}/${ZIP_NAME}" .

# Cleanup
rm -rf "${TEMP_DIR}"

# Show result
ZIP_SIZE=$(du -sh "${ZIP_NAME}" | cut -f1)
echo "✓ Created ${ZIP_NAME} (${ZIP_SIZE})"
echo "  Location: $(pwd)/${ZIP_NAME}"

