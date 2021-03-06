<?PHP namespace Spotify;

require '../vendor/autoload.php';
require '../config.php';

$sp_session = new \SpotifyWebAPI\Session(
  $cfg['spotify']['client_id'],
  $cfg['spotify']['client_secret'],
  $cfg['url'] . '/' . $cfg['spotify']['callback_route'] . '/');

$scopes = array(
  'playlist-read-private',
  'streaming',
);

$sp_auth_url = $sp_session->getAuthorizeUrl(array(
  'scope' => $scopes,
));

function get_refresh_token($auth_code) {
  global $sp_session;
  $sp_session->requestAccessToken($auth_code);
  return $sp_session->getRefreshToken();
}

function refresh_access_token($refresh_token) {
  global $sp_session;

  $sp_session->setRefreshToken($refresh_token);
  $sp_session->refreshAccessToken();

  return [$sp_session->getAccessToken(), $sp_session->getExpires()];
}

function refresh_account($spotifyacct, $save=true) {
  global $sp_session;

  // TODO: Error checking? What if refreshAccessToken() or me() fail?

  $access = refresh_access_token($spotifyacct->getRefreshToken());
  $spotifyacct->setAccessToken($access[0]);
  $spotifyacct->setExpiration(time() + $access[1] - 10); // Expire 10 seconds early.

  $api = get_api($access[0]);
  $me = $api->me();

  $spotifyacct->setUsername($me->id);
  if ($me->images) {
    $spotifyacct->setAvatar($me->images[0]->url);
  } else {
    $spotifyacct->setAvatar(null);
  }

  if ($save) {
    $spotifyacct->save();
  }
}

function get_api($access_token) {
  $api = new \SpotifyWebAPI\SpotifyWebAPI();
  $api->setAccessToken($access_token);

  return $api;
}

function wrap_spotify_playlist_data($user, $spotifyPlaylist) {
  $playlist = \SpotifyPlaylistQuery::GetOrCreateSpotifyPlaylist(
    $spotifyPlaylist['uri']);
  $listening = \ListensToQuery::GetOrCreateListensTo($user, $playlist);
  $data = $listening->getDataForJson();
  $data['spotifyPlaylist'] = $spotifyPlaylist;
  return $data;
}

function get_playlists($api, $user) {
  $wrapWithModel = function($spotifyPlaylist) use (&$user) {
    return wrap_spotify_playlist_data($user, $spotifyPlaylist);
  };

  $spotifyaccount = $user->getSpotifyAccount();
  if (!$spotifyaccount) return null;
  $username = $spotifyaccount->getUsername();
  $playlists = $api->getUserPlaylists($username, array(
    'limit' => 50,
  ))['items'];
  return array_map($wrapWithModel, $playlists);
}

function get_tracks_for_playlist($api, $playlist) {
  $username = $playlist->getSpotifyPlaylist()->getOwnerUsername();
  $spotifyId = $playlist->getSpotifyPlaylist()->getSpotifyId();
  $songs = $api->getUserPlaylistTracks($username, $spotifyId);
  return $songs['items'];
}

function get_formatted_tracks_for_playlist($api, $playlist) {
  $songs = get_tracks_for_playlist($api, $playlist);
  $newSongs = array();

  for ($i = 0; $i < sizeof($songs); $i++) {
    $song = $songs[$i];

    if (is_song_valid($song)) {
      $song['track']['is_local'] = $song['is_local'];
      $song['track']['is_valid'] = true;
    } else {
      $song['track']['is_local'] = true;
      $song['track']['is_valid'] = false;
    }
    
    $newSongs[] = $song['track'];
  }

  return $newSongs;
}

function is_song_valid($songData) {
  if (!$songData) return false;
  if (!isset($songData['track'])) return false;
  if (!isset($songData['track']['uri'])) return false;
  if (!isset($songData['track']['name'])) return false;

  return true;
}

