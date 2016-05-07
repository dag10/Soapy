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

  // Clears the user's playist selection.
  public function clearSelectedPlaylist() {
    $this->setSelectedPlaylist(null);
    $this->save();
    return;
  }

  // Selects a given playlist by Playlist ID, or clears it if null.
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
    }

    // Make sure this playlist is an option for this user
    if ($playlist->getListeningForUser($this) == null) {
      throw new Exception('You don\'t have access to playlist ' . $id . '.');
    }

    $this->setPlaylistId($playlist->getId());
    $this->save();
  }

  // Gets the user's SpotifyAccount entity, or null.
  public function getSpotifyAccount() {
    $accounts = $this->getSpotifyAccounts();
    if ($accounts->isEmpty()) return null;
    return $accounts->getFirst();
  }

  // Returns a query for all ListensTo relationships preloaded with Playlist.
  public function getListeningsQuery() {
    return ListensToQuery::create()->filterByUser($this)->with('Playlist');
  }
}
