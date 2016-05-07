<?php

use Base\ListensToQuery as BaseListensToQuery;

/**
 * Skeleton subclass for performing query and update operations on the 'listensto' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class ListensToQuery extends BaseListensToQuery
{
  public static function GetOrCreateListensTo($user, $playlist) {
    $listensTo = self::create()
      ->filterByUser($user)
      ->filterByPlaylistId($playlist->getId())
      ->findOne();

    if (!$listensTo) {
      $listensTo = new ListensTo();
      $listensTo->setUser($user);
      $listensTo->setPlaylistId($playlist->getId());
      $listensTo->save();
    }

    return $listensTo;
  }
}

