#!/bin/bash

# FTP Deploy Script
# Nahraje změněné soubory na Wedos FTP

FTP_HOST="385825.w25.wedos.net"
FTP_USER="w385825"
FTP_PASS="jFeqL%EF!g*2V%KQy"
FTP_PATH="/www/domains/econvisuals.com"

echo "🚀 Starting FTP deployment..."

# Zjistit změněné soubory od posledního commitu
CHANGED_FILES=$(git diff --name-only HEAD~1 HEAD)

if [ -z "$CHANGED_FILES" ]; then
    echo "ℹ️  No files changed since last commit"
    exit 0
fi

echo "📁 Files to upload:"
echo "$CHANGED_FILES"
echo ""

# Počítadlo
COUNT=0
SUCCESS=0
FAILED=0

# Nahrát každý soubor
while IFS= read -r file; do
    if [ -f "$file" ]; then
        COUNT=$((COUNT + 1))

        # Získat adresář souboru
        DIR=$(dirname "$file")

        echo "⬆️  Uploading: $file"

        # Nahrát přes curl s vytvořením adresářů
        curl -s --ftp-create-dirs -T "$file" \
            "ftp://$FTP_HOST$FTP_PATH/$file" \
            --user "$FTP_USER:$FTP_PASS"

        if [ $? -eq 0 ]; then
            echo "   ✅ Success"
            SUCCESS=$((SUCCESS + 1))
        else
            echo "   ❌ Failed"
            FAILED=$((FAILED + 1))
        fi
    fi
done <<< "$CHANGED_FILES"

echo ""
echo "📊 Summary:"
echo "   Total files: $COUNT"
echo "   ✅ Uploaded: $SUCCESS"
echo "   ❌ Failed: $FAILED"
echo ""
echo "🎉 Deployment complete!"
