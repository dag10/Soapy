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
    $user = $webauth == null ? null : UserQuery::GetOrCreateUser($webauth);
  }

  $spotifyacct = null;
  $me = null;
  $api = null;
  $user_json = [];

  if ($user) {
    $spotifyacct = $user->getSpotifyAccount();
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
  }

  if ($spotifyacct) {
    if (time() > $spotifyacct->getExpiration()->getTimestamp()) {
      \Spotify\refresh_account($spotifyacct);
    }

    $user_json = [
      'ldap' => $user->getLdap(),
      'username' => $spotifyacct->getUsername(),
      'first_name' => $user->getFirstName(),
      'last_name' => $user->getLastName(),
      'access_token' => $spotifyacct->getAccessToken(),
      'avatar' => $spotifyacct->getAvatar(),
    ];

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

function dieWithJsonError($message) {
  http_response_code(400);
  header("Content-Type: application/json");
  echo json_encode(
    ['error' => $message],
    JSON_UNESCAPED_SLASHES);
  exit;
}

function dieWithJsonSuccess() {
  header("Content-Type: application/json");
  echo json_encode(
    ['success' => true],
    JSON_UNESCAPED_SLASHES);
  exit;
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
    $ctx['playlists'] = \Spotify\get_playlists($api, $ctx['user']);
    $ctx['selected_playlist_uri'] = $ctx['user']->getPlaylistUri();
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
  $ctx['user']->setPlaylistUri($new_playlist);

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

  $playlists = \Spotify\get_playlists($ctx['sp_api'], $ctx['user']);

  $json_data = [
    'user' => $ctx['user_json'],
    'playlists' => $playlists,
    ];

  $playlist = $ctx['user']->getPlaylist();
  if ($playlist) {
    $json_data['playlist'] = $playlist->getDataForJson();
  }

  header("Content-Type: application/json");
  echo json_encode($json_data, JSON_UNESCAPED_SLASHES);
  exit;
});

// API for fetching songs for a user from their selected playlist.
$app->get('/api/rfid/:rfid/tracks/?', function($rfid) use ($app) {
  $ctx = start_view($app, [
    'require_spotify' => true, 'rfid' => $rfid, 'require_secret' => true]);

  $playlist = $ctx['user']->getPlaylist();
  if (!$playlist) {
    echo json_encode(
      ['user' => $ctx['user_json'],
       'error' => 'User has not selected a playlist.',
      ],
      JSON_UNESCAPED_SLASHES);
    exit;
  }

  $songs = \Spotify\get_formatted_tracks_for_playlist(
    $ctx['sp_api'], $playlist);

  header("Content-Type: application/json");
  echo json_encode(
    ['user' => $ctx['user_json'],
     'playlist' => $playlist->getDataForJson(),
     'tracks' => $songs],
    JSON_UNESCAPED_SLASHES);
  exit;
});

// API for setting the selected playlist for a user.
$app->post('/api/rfid/:rfid/playlist/set', function($rfid) use ($app) {
  $ctx = start_view($app, [
    'require_spotify' => true, 'rfid' => $rfid, 'require_secret' => true]);

  $new_playlist = $app->request->post('playlist_uri');
  $ctx['user']->setPlaylistUri($new_playlist);

  dieWithJsonSuccess();
});

// API for updating the current song being played.
$app->post('/api/rfid/:rfid/song/playing', function($rfid) use ($app) {
  $ctx = start_view($app, [
    'require_spotify' => true, 'rfid' => $rfid, 'require_secret' => true]);

  $song_uri = $app->request->post('song_uri');
  if (!$song_uri) {
    dieWithJsonError("No song URI was given.");
  }

  $playlist = $ctx['user']->getPlaylist();

  if (!$playlist) {
    dieWithJsonError("User does not have a selected playlist.");
  }

  $playlist->setLastPlayedSong($song_uri);
  $playlist->save();

  dieWithJsonSuccess();
});

// API for submitting log messages.
$app->post('/api/log/add', function() use ($app) {
  $ctx = start_view($app, ['require_secret' => true]);

  $json = $app->request->getBody();
  if (!$json) dieWithJsonError("No JSON body found.");

  $data = json_decode($json, true);
  if (!$data) dieWithJsonError("Failed to parse JSON.");

  try {
    $events = [];
    $bathroom = $data['bathroom'];
    foreach ($data['events'] as $event) {
      $event['bathroom'] = $bathroom;
      $log = new Log();

      try {
        $events[] = Log::CreateLog($event);
      } catch (Exception $e) {
        dieWithJsonError($e->getMessage());
      }
    }
  } catch (Exception $e) {
    dieWithJsonError($e->getMessage());
  }

  foreach ($events as $event) {
    $event->save();
  }

  dieWithJsonSuccess();
});

$app->run();

