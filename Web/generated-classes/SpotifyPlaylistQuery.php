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
  public static function GetOrCreateSpotifyPlaylist($playlistUri) {
    $sp_playlist = self::create()->filterByUri($playlistUri)->findOne();

    if (!$sp_playlist) {
      $playlist = new Playlist();
      $playlist->save();

      $sp_playlist = new SpotifyPlaylist();
      $sp_playlist->setPlaylist($playlist);
      $sp_playlist->setUri($playlistUri);
      $sp_playlist->save();
    }

    return $sp_playlist;
  }
}
