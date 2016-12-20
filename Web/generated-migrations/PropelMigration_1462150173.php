<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1462150173.
 * Generated on 2016-12-20 13:11:47 by drew
 *
 * You'll notice that this migration has been created retroactively, named
 * after the timestamp that's 1 second earlier than the actual first migration.
 * This is so that new installations only use migrations to create the initial
 * database structure instead of using propel mysql:insert.
 */
class PropelMigration_1462150173
{
    public $comment = '';

    public function preUp($manager)
    {
        // add the pre-migration code here
    }

    public function postUp($manager)
    {
        // add the post-migration code here
    }

    public function preDown($manager)
    {
        // add the pre-migration code here
    }

    public function postDown($manager)
    {
        // add the post-migration code here
    }

    /**
     * Get the SQL statements for the Up migration
     *
     * @return array list of the SQL strings to execute for the Up migration
     *               the keys being the datasources
     */
    public function getUpSQL()
    {
        return array (
  'soapy' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

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
',
);
    }

    /**
     * Get the SQL statements for the Down migration
     *
     * @return array list of the SQL strings to execute for the Down migration
     *               the keys being the datasources
     */
    public function getDownSQL()
    {
        return array (
  'soapy' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `user`;

DROP TABLE IF EXISTS `spotifyaccount`;

DROP TABLE IF EXISTS `log`;

DROP TABLE IF EXISTS `playlist`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}
