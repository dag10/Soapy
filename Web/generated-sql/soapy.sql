
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
    `playlist_id` INTEGER,
    PRIMARY KEY (`id`),
    INDEX `user_fi_10fa06` (`playlist_id`),
    CONSTRAINT `user_fk_10fa06`
        FOREIGN KEY (`playlist_id`)
        REFERENCES `playlist` (`id`)
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

-- ---------------------------------------------------------------------
-- log
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `log`;

CREATE TABLE `log`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `bathroom` VARCHAR(64) NOT NULL,
    `level` TINYINT,
    `time` DATETIME NOT NULL,
    `tag` VARCHAR(64),
    `message` TEXT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- playlist
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `playlist`;

CREATE TABLE `playlist`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `uri` TEXT NOT NULL,
    `lastplayedsong` TEXT,
    `owner_id` INTEGER NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `playlist_fi_ac5b84` (`owner_id`),
    CONSTRAINT `playlist_fk_ac5b84`
        FOREIGN KEY (`owner_id`)
        REFERENCES `user` (`id`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
