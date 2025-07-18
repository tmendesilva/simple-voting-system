<img alt="Drupal Logo" src="https://www.drupal.org/files/Wordmark_blue_RGB.png" height="60px">

# Voting System

## Getting start

- Start lando
  `lando start`

- Run Site-Install
  `lando drush si minimal --db-url "pgsql://postgres:@database:5432/drupal11?module=pgsql"`

- Restore database
  `lando db-import data/dump.sql.gz`

## Features

- Admin login `lando drush uli --uri="https://simple-voting-system.lndo.site/" --browser`

- Manage questions
  [http://simple-voting-system.lndo.site/admin/content/question](http://simple-voting-system.lndo.site/admin/content/question)

- Enable voting system and configs
  [http://simple-voting-system.lndo.site/vote-system/configuration](http://simple-voting-system.lndo.site/vote-system/configuration)

- Voting
  [http://simple-voting-system.lndo.site](http://simple-voting-system.lndo.site)

## Rest API POSTMAN collection

[data/POSTMAN-collection.json](data/POSTMAN-collection.json)
