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

function start_view_context($app, $opts=[]) {
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
      dieWithJsonError("Invalid Soapy secret.");
    }
  }

  if ($rfid) {
    $user = \CSH\user_for_rfid($rfid);
    if (!$user) {
      dieWithJsonError("User not found!");
    }
  } else {
    $webauth = \CSH\get_webauth($app);
    $user = $webauth == null ? null : UserQuery::GetOrCreateUser($webauth);
  }

  $spotifyacct = null;
  $me = null;
  $api = null;

  if ($user) {
    $spotifyacct = $user->getSpotifyAccount();
    if ($spotifyacct) {
      $expiration = $spotifyacct->getExpiration();
      if (!$expiration || time() > $expiration->getTimestamp()) {
        try {
          \Spotify\refresh_account($spotifyacct); // Also saves the object.
        } catch(\SpotifyWebAPI\SpotifyWebAPIException $e) {
          if ($rfid) {
            dieWithJsonError(
              "Failed to refresh spotify access token: " . $e->getMessage());
          } else {
            $spotifyacct->delete();
            $app->flash(
              'error',
              "Failed to refresh spotify acess token. Unpaired. Error: " .
              $e->getMessage());
            $app->redirect($base_url);
          }
        }
        // Re-fetch associated SpotifyAccount on the next access.
        $user->clearSpotifyAccounts();
        $spotifyacct = $user->getSpotifyAccount();
      }
      $api = \Spotify\get_api($spotifyacct->getAccessToken());
      $api->setReturnAssoc(true);
    } else if ($require_spotify) {
      if ($rfid) {
        dieWithJsonError("No spotify account has been linked.");
      } else {
        $app->flash('error', "You must connect a Spotify account.");
        $app->redirect($base_url);
      }
    }
  }

  return array(
    'base_url' => $cfg['url'],
    'auth_url' => $sp_auth_url,
    'authorized' => !!$spotifyacct,
    'sp_api' => $api,
    'user' => $user,
  );
}

function dieWithJson($data) {
  header("Content-Type: application/json");
  echo json_encode($data, JSON_UNESCAPED_SLASHES);
  exit;
}

function dieWithJsonError($message) {
  http_response_code(400);
  dieWithJson(['error' => $message]);
}

function dieWithJsonSuccess() {
  dieWithJson(['success' => true]);
}

/* Routes */

$app->get('/', function() use ($app) {
  global $base_url;

  $ctx = start_view_context($app);

  if ($ctx['user']->getSpotifyAccount() != null) {
    $app->redirect($base_url . 'me/playlists');
  }

  $app->render('index.html', $ctx);
});

// Spotify redirects here after user authenticates.
$app->get(
    '/' . $cfg['spotify']['callback_route'] . '/?', function() use ($app) {
  global $base_url;

  $ctx = start_view_context($app);

  if (isset($ctx['user']) && $ctx['user']->getSpotifyAccount() != null) {
    $app->flash('error', "You are already authenticated with Spotify.");
    $app->redirect($base_url);
    return;
  }

  $error = $app->request->get('error');
  if (isset($error)) {
    $app->flash(
      'error', "You can't use Soapy until you accept Spotify permissions.");
    $app->redirect($base_url);
  }

  $code = $app->request->get('code');
  if (!isset($code)) {
    $app->flash('error', "Expected Spotify authorization token.");
    $app->redirect($base_url);
  }

  try {
    $refresh_token = \Spotify\get_refresh_token($code);
  } catch(\SpotifyWebAPI\SpotifyWebAPIException $e) {
    $app->flash('error', "Spotify Error: " . $e->getMessage());
    $app->redirect($base_url);
  }

  $spotifyacct = new SpotifyAccount();
  $spotifyacct->setUserId($ctx['user']->getId());
  $spotifyacct->setRefreshToken($refresh_token);
  $spotifyacct->setAccessToken("initial");
  $spotifyacct->setExpiration(0);

  $spotifyacct->save();

  $app->redirect($base_url);
});

$app->post('/unpair/spotify/?', function() use ($app) {
  global $base_url;

  $ctx = start_view_context($app, ['require_spotify' => true]);
  if (!$ctx) return;

  $ctx['user']->getSpotifyAccount()->delete();

  $app->redirect($base_url);
});

// View spotify playlists.
$app->get('/me/playlists/?', function() use ($app) {
  $ctx = start_view_context($app, ['require_spotify' => true]);
  if (!$ctx) return;

  $api = $ctx['sp_api'];
  $ctx['selected_playlist_uri'] = $ctx['user']->getPlaylistUri();
  $ctx['playlists'] = array();

  try {
    $ctx['playlists'] = \Spotify\get_playlists($api, $ctx['user']);
  } catch (\SpotifyWebAPI\SpotifyWebAPIException $e) {
    $app->flash('error', 'Spotify error: ' . $e->getMessage());
  }

  $app->render('data_playlists.html', $ctx);
});

// AJAX endpoint for setting the selected playlist for a user.
$app->post('/me/playlist/set', function() use ($app) {
  $ctx = start_view_context($app, ['require_spotify' => true]);

  $new_playlist = $app->request->post('playlist_uri');
  $ctx['user']->setPlaylistUri($new_playlist);

  dieWithJsonSuccess();
});

// API for fetching playlists for a user.
$app->get('/api/rfid/:rfid/playlists/?', function($rfid) use ($app) {
  $ctx = start_view_context($app, [
    'require_spotify' => true, 'rfid' => $rfid, 'require_secret' => true]);

  $json_data = [
    'user' => $ctx['user']->getDataForJson(),
    ];

  try {
    $playlists = \Spotify\get_playlists($ctx['sp_api'], $ctx['user']);
  } catch (\SpotifyWebAPI\SpotifyWebAPIException $e) {
    dieWithJsonError("Error getting playlists: " . $e->getMessage());
  }

  if ($playlists) {
    $json_data['user']['playlists'] = $playlists;
  }

  $playlist = $ctx['user']->getPlaylist();
  if ($playlist) {
    $json_data['user']['selectedPlaylist'] = $playlist->getDataForJson();
  }

  dieWithJson($json_data);
});

// API for fetching songs for a user from their selected playlist.
$app->get(
    '/api/rfid/:rfid/playlist/:playlistId',
    function($rfid, $playlistId) use ($app) {

  $ctx = start_view_context($app, [
    'require_spotify' => true, 'rfid' => $rfid, 'require_secret' => true]);

  if ($playlistId == "selected") {
    $playlist = $ctx['user']->getPlaylist();
    if (!$playlist) {
      dieWithJsonError("User has not selected a playlist.");
    }
  } else {
    $playlist = PlaylistQuery::create()->findPk($playlistId);
    if ($playlist->getOwnerId() != $ctx['user']->getId()) {
      dieWithJsonError("This is not your playlist.");
    }
    if (!$playlist) {
      dieWithJsonError("Playlist not found.");
    }
  }

  $json_data = [
    'user' => $ctx['user']->getDataForJson(),
    ];

  $json_data['user']['selectedPlaylist'] = $ctx['user']->getPlaylist()->
    getDataForJson();

  try {
    //$json_data['user']['selectedPlaylist']['tracklist'] =
    $tracklist =
      \Spotify\get_formatted_tracks_for_playlist($ctx['sp_api'], $playlist);
  } catch (\SpotifyWebAPI\SpotifyWebAPIException $e) {
    dieWithJsonError("Error getting tracks: " . $e->getMessage());
  }

  if ($playlistId == "selected") {
    $json_data['user']['selectedPlaylist']['tracklist'] = $tracklist;
  } else {
    $json_data['playlist'] = $playlist->getDataForJson();
    $json_data['playlist']['tracklist'] = $tracklist;
  }

  dieWithJson($json_data);
});

// API for setting the selected playlist for a user.
$app->post('/api/rfid/:rfid/playlist/set', function($rfid) use ($app) {
  $ctx = start_view_context($app, [
    'require_spotify' => true, 'rfid' => $rfid, 'require_secret' => true]);

  $new_playlist = $app->request->post('playlist_uri');
  $ctx['user']->setPlaylistUri($new_playlist);

  dieWithJsonSuccess();
});

// API for updating the current song being played.
$app->post('/api/rfid/:rfid/song/playing', function($rfid) use ($app) {
  $ctx = start_view_context($app, [
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
  $ctx = start_view_context($app, ['require_secret' => true]);

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

