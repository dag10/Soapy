<?PHP namespace Spotify;

require '../config.php';
require 'util.php';

$scopes = array('playlist-read-private', 'streaming');

function auth_url() {
  global $cfg, $scopes;
  return "https://accounts.spotify.com/authorize" .
         "?client_id=" . $cfg['spotify']['client_id'] .
         "&response_type=code" .
         "&redirect_uri=" . encodeURIComponent($cfg['url'] . '/' . $cfg['spotify']['callback_route'] . '/') .
         "&scope=" . encodeURIComponent(join(' ', $scopes));
}

