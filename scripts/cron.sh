#!/bin/bash

cd "$(dirname "$0")/.."

# Pull latest changes
git pull

# Execute fetch script
php scripts/01_fetch.php

# Add all changes
git add -A

# Commit with timestamp
git commit -m "autoupdate $(date '+%Y-%m-%d %H:%M:%S')"

# Push to remote
git push
