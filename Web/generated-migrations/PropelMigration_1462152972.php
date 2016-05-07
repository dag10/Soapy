<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1462152972.
 * Generated on 2016-05-01 21:36:12 by drew
 */
class PropelMigration_1462152972
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
      $sql = "UPDATE playlist
              INNER JOIN spotifyplaylist ON playlist.id=spotifyplaylist.id
              SET playlist.uri=spotifyplaylist.uri";
      $pdo = $manager->getAdapterConnection('soapy');
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

ALTER TABLE `playlist`

  DROP `uri`;

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

  ADD `uri` TEXT NOT NULL AFTER `id`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}
