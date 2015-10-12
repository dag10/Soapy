<?php

require '../vendor/autoload.php';
require '../generated-conf/config.php';
require '../config.php';
require '../include/spotify.php';
require '../include/csh.php';

$app = new \Slim\Slim(array(
  'templates.path' => '../templates',
));

$base_url = $cfg['url'] . '/';

$app->config($cfg);
$app->add(new \Slim\Middleware\SessionCookie());

$app->container->singleton('log', function() {
  $log = new \Monolog\Logger('slim-skeleton');
  $log->pushHandler(new \Monolog\Handler\StreamHandler(
    '../logs/app.log', \Monolog\Logger::DEBUG));
  return $log;
});

$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
  'charset' => 'utf-8',
  'cache' => realpath('../templates/cache'),
  'auto_reload' => true,
  'strict_variables' => false,
  'autoescape' => true
);
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());

/* View Utilities */

function start_view($app, $opts=[]) {
  global $cfg, $base_url, $sp_auth_url;

  $rfid = isset($opts['rfid']) ? $opts['rfid'] : null;
  $require_spotify = isset($opts['require_spotify'])
    ? $opts['require_spotify']
    : false;
  $require_secret = isset($opts['require_secret'])
    ? $opts['require_secret']
    : false;

  if ($require_secret) {
    $request_secret = $app->request->headers->get('X-Soapy-Secret', '');
    if ($request_secret != $cfg['soapy_secret']) {
      header('Content-Type: application/json');
      echo json_encode(['error' => 'Invalid Soapy secret.']);
      exit;
    }
  }

  if ($rfid) {
    header('Content-Type: application/json');
    $user = \CSH\user_for_rfid($rfid);
    if (!$user) {
      echo json_encode(['error' => 'User not found.']);
      exit;
    }
  } else {
    $webauth = \CSH\get_webauth($app);
    $user = UserQuery::GetOrCreateUser($webauth);
  }
  $spotifyacct = SpotifyAccountQuery::findByUser($user);

  if ($require_spotify and !$spotifyacct) {
    if ($rfid) {
      echo json_encode(['error' => 'No Spotify account has been linked.']);
      exit;
    } else {
      $app->flash('error', "You must connect a Spotify account.");
      $app->redirect($base_url);
      return;
    }
  }

  $me = null;
  $api = null;

  if ($spotifyacct) {
    $user_json = [
      'ldap' => $user->getLdap(),
      'username' => $spotifyacct->getUsername(),
      'first_name' => $user->getFirstName(),
      'last_name' => $user->getLastName(),
      'access_token' => $spotifyacct->getAccessToken(),
      'avatar' => $spotifyacct->getAvatar(),
    ];
  } else {
    $user_json = [];
  }

  if ($spotifyacct) {
    if (time() > $spotifyacct->getExpiration()->getTimestamp()) {
      \Spotify\refresh_account($spotifyacct);
    }

    $api = \Spotify\get_api($spotifyacct->getAccessToken());
    $api->setReturnAssoc(true);
  }

  return array(
    'base_url' => $cfg['url'],
    'auth_url' => $sp_auth_url,
    'spotifyacct' => $spotifyacct,
    'authorized' => !!$spotifyacct,
    'sp_api' => $api,
    'user' => $user,
    'user_json' => $user_json,
  );
}

/* Routes */

$app->get('/', function() use ($app) {
  global $base_url;

  $ctx = start_view($app);

  if ($ctx['spotifyacct']) {
    $app->redirect($base_url . 'me/playlists');
    return;
  }

  $app->render('index.html', $ctx);
});

// Spotify redirects here after user authenticates.
$app->get(
    '/' . $cfg['spotify']['callback_route'] . '/?', function() use ($app) {
  global $base_url;

  $ctx = start_view($app);

  if (isset($ctx['spotifyacct'])) {
    $app->flash('error', "You are already authenticated with Spotify.");
    $app->redirect($base_url);
    return;
  }

  $error = $app->request->get('error');
  if (isset($error)) {
    $app->flash('error', "You can't use Soapy until you accept Spotify permissions.");
    $app->redirect($base_url);
    return;
  }

  $code = $app->request->get('code');
  if (!isset($code)) {
    $app->flash('error', "Expected Spotify authorization token.");
    $app->redirect($base_url);
    return;
  }

  try {
    $refresh_token = \Spotify\get_refresh_token($code);
  } catch(\SpotifyWebAPI\SpotifyWebAPIException $e) {
    $app->flash('error', "Spotify Error: " . $e->getMessage());
    $app->redirect($base_url);
    return;
  }

  $spotifyacct = new SpotifyAccount();
  $spotifyacct->setUserId($ctx['user']->getId());
  $spotifyacct->setRefreshToken($refresh_token);
  \Spotify\refresh_account($spotifyacct); // Also saves the object.

  $app->redirect($base_url);
});

$app->post('/unpair/spotify/?', function() use ($app) {
  global $base_url;

  $ctx = start_view($app, ['require_spotify' => true]);
  if (!$ctx) return;

  $ctx['spotifyacct']->delete();

  $app->redirect($base_url);
});

// View spotify playlists.
$app->get('/me/playlists/?', function() use ($app) {
  $ctx = start_view($app, ['require_spotify' => true]);
  if (!$ctx) return;

  $api = $ctx['sp_api'];

  try {
    $ctx['playlists'] = \Spotify\get_playlists(
        $api, $ctx['spotifyacct']->getUsername());
    $ctx['selected_playlist_uri'] = $ctx['spotifyacct']->getPlaylist();
  } catch (Exception $e) {
    $app->flash('error', 'Spotify error: ' . $e->getMessage());
    $ctx['playlists'] = array();
  }

  $app->render('data_playlists.html', $ctx);
});

// AJAX endpoint for setting the selected playlist for a user.
$app->post('/me/playlist/set', function() use ($app) {
  $ctx = start_view($app, ['require_spotify' => true]);

  $new_playlist = $app->request->post('playlist_uri');
  $ctx['spotifyacct']->setPlaylist($new_playlist);
  $ctx['spotifyacct']->save();

  header("Content-Type: application/json");
  echo json_encode(
    ['success' => true],
    JSON_UNESCAPED_SLASHES);
  exit;
});

// API for fetching playlists for a user.
$app->get('/api/rfid/:rfid/playlists/?', function($rfid) use ($app) {
  $ctx = start_view($app, [
    'require_spotify' => true, 'rfid' => $rfid, 'require_secret' => true]);

  $playlists = \Spotify\get_playlists(
    $ctx['sp_api'], $ctx['spotifyacct']->getUsername());

  $playlist_uri = $ctx['spotifyacct']->getPlaylist();
  $playlist_data = [ 'uri' => $playlist_uri ];

  header("Content-Type: application/json");
  echo json_encode(
    [
    'user' => $ctx['user_json'],
    'playlists' => $playlists,
    'playlist' => $playlist_data,
    ],
    JSON_UNESCAPED_SLASHES);
  exit;
});

// API for fetching songs for a user from their selected playlist.
$app->get('/api/rfid/:rfid/tracks/?', function($rfid) use ($app) {
  $ctx = start_view($app, [
    'require_spotify' => true, 'rfid' => $rfid, 'require_secret' => true]);

  $playlist_uri = $ctx['spotifyacct']->getPlaylist();

  if (!$playlist_uri) {
    echo json_encode(
      ['user' => $ctx['user_json'],
       'error' => 'User has not selected a playlist.',
      ],
      JSON_UNESCAPED_SLASHES);
    exit;
  }

  $playlist_uri_expl = explode(':', $playlist_uri);
  $playlist_username = $playlist_uri_expl[2];
  $playlist_id = $playlist_uri_expl[4];

  $playlist_data = [ 'uri' => $playlist_uri ];

  $songs = \Spotify\get_playlist_tracks(
    $ctx['sp_api'], $playlist_username, $playlist_id);

  for ($i = 0; $i < sizeof($songs); $i++) {
    $song = $songs[$i];
    $song['track']['is_local'] = $song['is_local'];
    $songs[$i] = $song['track'];
  }

  header("Content-Type: application/json");
  echo json_encode(
    ['user' => $ctx['user_json'],
     'playlist' => $playlist_data,
     'tracks' => $songs],
    JSON_UNESCAPED_SLASHES);
  exit;
});

// API for setting the selected playlist for a user.
$app->post('/api/rfid/:rfid/playlist/set', function($rfid) use ($app) {
  $ctx = start_view($app, [
    'require_spotify' => true, 'rfid' => $rfid, 'require_secret' => true]);

  $new_playlist = $app->request->post('playlist_uri');
  $ctx['spotifyacct']->setPlaylist($new_playlist);
  $ctx['spotifyacct']->save();

  header("Content-Type: application/json");
  echo json_encode(
    ['success' => true],
    JSON_UNESCAPED_SLASHES);
  exit;
});

$app->run();

