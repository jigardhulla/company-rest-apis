# company-rest-apis
Company Rest APIs

# Setup Manual

You will need to install below packages to run the application

PHP: 7.3

MySQL: 8

Web Server of your choice (Apache/Nginx)

composer: latest

composer require vlucas/phpdotenv (run it)

add .env file on root directory with DB_HOST, DB_USERNAME and DB_PASSWORD.

#### Note: You can use xampp or any other way to do server setup first before going ahead.

# DATABASE QUERIES:
```sql
CREATE DATABASE `company` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `departments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `name` VARCHAR(100) NOT NULL, 
  `description` TEXT,
  `deleted` ENUM('0','1') DEFAULT '0' NOT NULL, 
  PRIMARY KEY (`id`)
) ENGINE = INNODB;

CREATE TABLE `employees` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL, 
  `department_id` INT(11) NOT NULL,
  `deleted` ENUM('0','1') DEFAULT '0' NOT NULL, 
  PRIMARY KEY (`id`),
  UNIQUE INDEX (`email`), 
  KEY (`department_id`),
  KEY (`name`)
) ENGINE = INNODB;

CREATE TABLE `employee_contacts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `employee_id` INT(11), 
  `contact_number` INT(10),
  `deleted` ENUM('0','1') DEFAULT '0' NOT NULL, 
  PRIMARY KEY (`id`), 
  KEY (`employee_id`)
) ENGINE = INNODB;

CREATE TABLE `employee_addresses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT, 
  `employee_id` INT(11) NOT NULL, 
  `address` TEXT,
  `deleted` ENUM('0','1') DEFAULT '0' NOT NULL, 
  PRIMARY KEY (`id`), 
  KEY (`employee_id`)
) ENGINE = INNODB;
```

Once setup is done. You can follow below documentation link to access apis:

### https://documenter.getpostman.com/view/27790542/2s93sW9vTB
