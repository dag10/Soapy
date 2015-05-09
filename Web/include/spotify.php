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

function get_api($auth_code) {
  global $sp_session;

  $sp_session->requestAccessToken($auth_code);
  $access_token = $sp_session->getAccessToken();

  $api = new \SpotifyWebAPI\SpotifyWebAPI();
  $api->setAccessToken($access_token);

  return array($access_token, $api);
}

