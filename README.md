<img alt="Drupal Logo" src="http://www.drupal.org/files/Wordmark_blue_RGB.png" height="60px">

# Simple Voting System

This project is built on top of Drupal and features a custom module designed to simulate a voting system. The module introduces two custom entities: **Question** and **Answer**. The system allows users to cast votes on answers, and the votes are computed to determine the outcome.

## Custom Entities

- **Question**: Represents a question that can be voted on.
- **Answer**: Represents a possible answer to a question.

## Voting System

The voting system is designed to allow users to cast votes on answers. The votes are then computed to determine the outcome. The system can be used to simulate various types of voting scenarios.

## REST API

A REST API has been developed using Drupal's RESTful Web Services module to interact with the data about entities and voting results. This allows external applications to retrieve and manipulate data related to questions, answers, and voting results.

## Key Features

- Custom entities for questions and answers
- Voting system to compute votes
- REST API using Drupal's BaseResource API for data interaction

## Technical Details

- Built on top of Drupal
- Custom module developed to introduce new entities and functionality
- REST API built using Drupal's RESTful Web Services module and ResourceBase plugin

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

## Shortcuts

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
