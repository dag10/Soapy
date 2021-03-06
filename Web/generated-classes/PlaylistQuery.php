<?php

use Base\PlaylistQuery as BasePlaylistQuery;

/**
 * Skeleton subclass for performing query and update operations on the 'playlist' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class PlaylistQuery extends BasePlaylistQuery
{
  // Always try to preload the corresponding SpotifyPlaylist subclass.
  public static function create($modelAlias = NULL, Propel\Runtime\ActiveQuery\Criteria $criteria = NULL) {
    return BasePlaylistQuery::create($modelAlias, $criteria)->leftJoinWithSpotifyPlaylist();
  }
}
