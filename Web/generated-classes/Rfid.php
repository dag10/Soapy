<?php

use Base\Rfid as BaseRfid;
use Propel\Runtime\ActiveQuery\Criteria;

/**
 * Skeleton subclass for representing a row from the 'rfid' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class Rfid extends BaseRfid
{
  public function getLastTap() {
    return RfidTapQuery::create()
      ->filterByRfid($this->getRfid())
      ->orderByTime(Criteria::DESC)
      ->findOne();
  }

  public function getDataForJson($include_last_tap = false) {
    $data = [
      'rfid' => $this->getRfid(),
    ];

    if ($include_last_tap) {
      $lastTap = $this->getLastTap();
      if ($lastTap) {
        $data['lastTap'] = $lastTap->getTime()->getTimestamp() * 1000;
      }
    }

    return $data;
  }
}
