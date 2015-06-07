
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- user
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `ldap` VARCHAR(128) NOT NULL,
    `firstname` TEXT NOT NULL,
    `lastname` TEXT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- spotifyaccount
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `spotifyaccount`;

CREATE TABLE `spotifyaccount`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `user_id` INTEGER NOT NULL,
    `username` VARCHAR(128) NOT NULL,
    `accesstoken` TEXT NOT NULL,
    `refreshtoken` TEXT NOT NULL,
    `expiration` DATETIME NOT NULL,
    `avatar` TEXT,
    PRIMARY KEY (`id`),
    INDEX `spotifyaccount_fi_29554a` (`user_id`),
    CONSTRAINT `spotifyaccount_fk_29554a`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`id`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
