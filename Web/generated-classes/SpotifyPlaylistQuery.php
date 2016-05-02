<?php

use Base\SpotifyPlaylistQuery as BaseSpotifyPlaylistQuery;

/**
 * Skeleton subclass for performing query and update operations on the 'spotifyplaylist' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class SpotifyPlaylistQuery extends BaseSpotifyPlaylistQuery
{
  public static function GetOrCreateSpotifyPlaylist($user, $playlistUri) {
    $ownerId = $user->getId();

    $playlist = self::create()->filterByOwnerId($ownerId)->
      filterByUri($playlistUri)->findOne();

    if (!$playlist) {
      $sp_playlist = new SpotifyPlaylist();
      $sp_playlist->setOwnerId($ownerId);
      $sp_playlist->setUri($playlistUri);
      $sp_playlist->save();
    }

    return $playlist;
  }
}
