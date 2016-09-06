
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
    `playbackmode` TINYINT DEFAULT 0 NOT NULL,
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
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- listensto
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `listensto`;

CREATE TABLE `listensto`
(
    `user_id` INTEGER NOT NULL,
    `playlist_id` INTEGER NOT NULL,
    `lastplayedsonguri` TEXT,
    PRIMARY KEY (`user_id`,`playlist_id`),
    INDEX `listensto_fi_10fa06` (`playlist_id`),
    CONSTRAINT `listensto_fk_29554a`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`id`),
    CONSTRAINT `listensto_fk_10fa06`
        FOREIGN KEY (`playlist_id`)
        REFERENCES `playlist` (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- spotifyplaylist
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `spotifyplaylist`;

CREATE TABLE `spotifyplaylist`
(
    `id` INTEGER NOT NULL,
    `uri` TEXT NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `spotifyplaylist_u_480850` (`uri`(80)),
    CONSTRAINT `spotifyplaylist_fk_e00ee3`
        FOREIGN KEY (`id`)
        REFERENCES `playlist` (`id`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
