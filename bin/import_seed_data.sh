#!/bin/bash
# Run all the database migrations
mysql < /var/www/db/migrations/base.sql

# Import the translations
php /var/www/bin/translation_importer.php

# Load the seed data
cd /var/www/db/seeds/
mysql < load_seed_data.sql