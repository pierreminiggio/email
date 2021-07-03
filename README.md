```sql
CREATE TABLE `email` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `token` TEXT NULL,
    `verified_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;
ALTER TABLE `email` ADD `token_sent_at` DATETIME NULL AFTER `token`;
ALTER TABLE `email` ADD `ip` VARCHAR(255) NOT NULL AFTER `email`;
ALTER TABLE `email` CHANGE `created_at` `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
```
