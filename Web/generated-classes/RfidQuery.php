<?php

use Base\RfidQuery as BaseRfidQuery;

/**
 * Skeleton subclass for performing query and update operations on the 'rfid' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class RfidQuery extends BaseRfidQuery
{
  public static function GetOrCreateRFID($rfid) {
    $mapping = self::create()->findOneByRfid($rfid);

    if (!$mapping) {
      $mapping = new Rfid();
      $mapping->setRfid($rfid);
      $mapping->save();
    }

    return $mapping;
  }
}
