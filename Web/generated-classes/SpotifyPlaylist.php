<?php

use Base\SpotifyPlaylist as BaseSpotifyPlaylist;

/**
 * Skeleton subclass for representing a row from the 'spotifyplaylist' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class SpotifyPlaylist extends BaseSpotifyPlaylist
{
  public function getOwnerUsername() {
    $playlist_uri_expl = explode(':', $this->getUri());
    return $playlist_uri_expl[2];
  }
  
  public function getSpotifyId() {
    $playlist_uri_expl = explode(':', $this->getUri());
    return $playlist_uri_expl[4];
  }
}
