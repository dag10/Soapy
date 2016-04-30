<?php

use Base\User as BaseUser;

/**
 * Skeleton subclass for representing a row from the 'user' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class User extends BaseUser
{
  public function getDataForJson() {
    $data = [
      'ldap' => $this->getLdap(),
      'firstName' => $this->getFirstName(),
      'lastName' => $this->getLastName(),
      'playback' => [
        'playbackMode' => $this->getPlaybackMode(),
        ],
      ];

    $spotifyAccount = $this->getSpotifyAccount();
    if ($spotifyAccount) {
      $data['spotifyAccount'] = $spotifyAccount->getDataForJson();
    }

    return $data;
  }

  public function clearSelectedPlaylist() {
    $this->setPlaylistId(null);
    $this->save();
    return;
  }

  public function setSelectedPlaylistById($id) {
    // For clearing a playlist selection
    if (!$id) {
      $this->clearSelectedPlaylist();
      return;
    }

    // Make sure the playlist exists
    $playlist = PlaylistQuery::create()->findPk($id);
    if (!$playlist) {
      throw new Exception('Playlist not found: ' . $id);
    } else if ($playlist->getOwner() != $this) {
      throw new Exception('You don\'t own playlist ' . $id . '.');
    }

    $this->setPlaylistId($id);
    $this->save();
  }

  public function setPlaylistUri($uri) {
    if (!$uri) {
      $this->setPlaylistId(null);
      $this->save();
      return;
    }

    $playlist = PlaylistQuery::create()->filterByUri($uri)->filterByOwnerId(
      $this->getId())->findOne();

    if (!$playlist) {
      $playlist = new Playlist();
      $playlist->setOwnerId($this->getId());
      $playlist->setUri($uri);
      $playlist->save();
    }

    $this->setPlaylistId($playlist->getId());
    $this->save();
  }

  public function getPlaylistUri() {
    $playlist = $this->getPlaylist();

    if (!$playlist) {
      return null;
    }

    return $playlist->getUri();
  }

  public function getSpotifyAccount() {
    $accounts = $this->getSpotifyAccounts();
    if ($accounts->isEmpty()) return null;
    return $accounts->getFirst();
  }
}
