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

