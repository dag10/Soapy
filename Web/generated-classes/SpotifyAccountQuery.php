<?php

use Base\SpotifyAccountQuery as BaseSpotifyAccountQuery;

/**
 * Skeleton subclass for performing query and update operations on the 'spotifyaccount' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class SpotifyAccountQuery extends BaseSpotifyAccountQuery
{
  public static function findByUser($user) {
    if (!$user || !$user->getId()) return null;

    return self::create()->filterByUserId($user->getId())->findOne();
  }
}
