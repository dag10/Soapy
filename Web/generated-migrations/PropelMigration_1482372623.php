<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1482372623.
 * Generated on 2016-12-21 21:10:23 by drew
 */
class PropelMigration_1482372623
{
    public $comment = '';

    public function preUp($manager)
    {
        // add the pre-migration code here
    }

    public function postUp($manager)
    {
      $sql = "INSERT INTO `rfidtap`
      SELECT
      `rfid`.`rfid`,
      `grouped`.`time`
      FROM
      (SELECT
      `initial`.`time`,
      SUBSTRING_INDEX(`initial`.`fullName`, ' ', 1) as `firstName`,
      SUBSTRING_INDEX(`initial`.`fullName`, ' ', -1) as `lastName`
      FROM
      (SELECT
      `time`,
      DATE_FORMAT(`time`, '%Y-%m-%d') as `day`,
      SUBSTRING_INDEX(`message`, ' ', 2) as `fullName`
      FROM `log`
      WHERE `message` LIKE '%started playing%'
      ORDER BY `time` ASC
      ) AS `initial`
      GROUP BY `initial`.`day`, `initial`.`fullName`
      ORDER BY `time` DESC) AS `grouped`,
      (SELECT `ldap`, `firstName`, `lastName` FROM `user`) AS `user`,
      (SELECT `ldap`, `rfid` FROM `rfid`) AS `rfid`
      WHERE `user`.`firstName` = `grouped`.`firstName` AND `user`.`lastName` = `grouped`.`lastName` AND `rfid`.`ldap` = `user`.`ldap`";
      $pdo = $manager->getAdapterConnection('soapy');
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
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
  'soapy' => '',
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
  'soapy' => '',
);
    }

}
