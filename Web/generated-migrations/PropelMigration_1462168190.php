<?php


/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1462168190.
 * Generated on 2016-05-02 01:49:50 by drew
 */
class PropelMigration_1462168190
{
    public $comment = '';

    public function preUp($manager)
    {
      $sql = "INSERT INTO listensto (user_id,playlist_id,lastplayedsonguri)
              SELECT owner_id, id, lastplayedsonguri
              FROM playlist";
      $pdo = $manager->getAdapterConnection('soapy');
      $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
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
      $sql = "UPDATE playlist
              INNER JOIN listensto
              ON listensto.playlist_id=playlist.id
              SET playlist.owner_id=listensto.user_id,
                  playlist.lastplayedsonguri=listensto.lastplayedsonguri";
      $pdo = $manager->getAdapterConnection('soapy');
      $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
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

#set @dropfk=if((SELECT true FROM information_schema.TABLE_CONSTRAINTS WHERE
#            CONSTRAINT_SCHEMA = DATABASE() AND
#            TABLE_NAME        = \'playlist\' AND
#            CONSTRAINT_NAME   = \'playlist_fk_ac5b84\') = true,
#            \'ALTER TABLE `playlist` DROP FOREIGN KEY `playlist_fk_ac5b84`\',\'select 1\');
#prepare stmt from @dropfk;
#execute stmt;
##deallocate prepare stmt;
#
#set @dropfi=if((SELECT true FROM information_schema.STATISTICS WHERE
#            TABLE_SCHEMA = DATABASE() AND
#            TABLE_NAME   = \'playlist\' AND
#            INDEX_NAME   = \'playlist_fi_ac5b84\') = true,
#            \'ALTER TABLE playlist drop index playlist_fi_ac5b84\',\'select 1\');
#prepare stmt2 from @dropfi;
#execute stmt2;
##deallocate prepare stmt2;


ALTER TABLE `playlist`

  DROP FOREIGN KEY `playlist_fk_ac5b84`,

  DROP INDEX `playlist_fi_ac5b84`,

  DROP `lastplayedsonguri`,

  DROP `owner_id`;


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

ALTER TABLE `playlist`

  ADD `lastplayedsonguri` TEXT AFTER `id`,

  ADD `owner_id` INTEGER NOT NULL AFTER `lastplayedsonguri`,

  ADD INDEX `playlist_fi_ac5b84` (`owner_id`),

  ADD CONSTRAINT `playlist_fk_ac5b84`
    FOREIGN KEY (`owner_id`)
    REFERENCES `user` (`id`);

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}
