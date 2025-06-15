#!/bin/bash
# Setup script for TTS audio cleanup cron job

# Get the current directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Create the cron job entry
CRON_JOB="*/10 * * * * /usr/bin/php $SCRIPT_DIR/cleanup_audio.php >/dev/null 2>&1"

# Add to crontab
(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -

echo "Cron job added successfully!"
echo "Audio files will be automatically cleaned up every 10 minutes."
echo "Files older than 10 minutes will be deleted."
echo ""
echo "To verify the cron job was added, run: crontab -l"
echo "To remove the cron job later, run: crontab -e"