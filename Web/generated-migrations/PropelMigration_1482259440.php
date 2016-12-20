<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1482259440.
 * Generated on 2016-12-20 13:44:00 by drew
 */
class PropelMigration_1482259440
{
    public $comment = '';

    public function preUp($manager)
    {
        // add the pre-migration code here
    }

    public function postUp($manager)
    {
      require getcwd() . '/config.php';

      if (!isset($cfg) || !isset($cfg['ibutton']) ||
          !isset($cfg['ibutton']['overrides'])) {
          echo 'No existing RFID mappings found in $cfg[\'ibutton\'][\'overrides\'].\n';
          return;
      }

      echo 'Found RFID mappings in config.php. Adding to database...\n';
      $mappings = $cfg['ibutton']['overrides'];

      foreach ($mappings as $rfid => $ldap) {
        echo "Adding RFID to database: $rfid -> $ldap\n";

        $sql = "INSERT INTO rfid (rfid, ldap)
                VALUES ('$rfid', '$ldap');";
        $pdo = $manager->getAdapterConnection('soapy');
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
      }
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

CREATE TABLE IF NOT EXISTS `rfid`
(
    `rfid` VARCHAR(64) NOT NULL,
    `ldap` VARCHAR(128) NOT NULL,
    PRIMARY KEY (`rfid`)
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

DROP TABLE IF EXISTS `rfid`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}
