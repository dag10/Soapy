<?php

use Base\Playlist as BasePlaylist;

/**
 * Skeleton subclass for representing a row from the 'playlist' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class Playlist extends BasePlaylist
{
  public function getDataForJson() {
    $ret = [
      'soapyPlaylistId' => $this->getId(),
      ];

    $spPlaylist = $this->getSpotifyPlaylist();
    if ($spPlaylist) {
      $ret['spotifyPlaylistUri'] = $spPlaylist->getUri();
    }

    return $ret;
  }

  public function getConcretePlaylist() {
    $spotifyPlaylist = $this->getSpotifyPlaylist();

    if ($spotifyPlaylist == null) {
      throw new Exception(
        'No child class for Playlist ' + $this->id + ' found.');
    }

    return $spotifyPlaylist;
  }

  // Gets a ListensTo entity for a given User entity.
  public function getListeningForUser($user) {
    return ListensToQuery::create()
      ->filterByUser($user)
      ->filterByPlaylist($this)
      ->findOne();
  }
}
