<img alt="Drupal Logo" src="http://www.drupal.org/files/Wordmark_blue_RGB.png" height="60px">

# Voting System

## Getting start

- Start lando

  ```bash
  lando start
  ```

- Install dependencies

  ```bash
  lando composer install
  ```

- Run Site-Install

  ```bash
  lando drush si minimal --db-url "pgsql://postgres:@database:5432/drupal11?module=pgsql" --config-dir "../config/sync/"
  ```

- Restore database
  ```bash
  lando db-import data/dump.sql.gz
  ```

## Features

- Admin login

  ```bash
  lando drush uli --uri="http://simple-voting-system.lndo.site/" --browser
  ```

- Manage questions
  [http://simple-voting-system.lndo.site/admin/content/question](http://simple-voting-system.lndo.site/admin/content/question)

- Enable voting system and configs
  [http://simple-voting-system.lndo.site/vote-system/configuration](http://simple-voting-system.lndo.site/vote-system/configuration)

- Voting
  [http://simple-voting-system.lndo.site](http://simple-voting-system.lndo.site)

## Rest API POSTMAN collection

[data/POSTMAN-collection.json](data/POSTMAN-collection.json)
