<?php

require '../vendor/autoload.php';
require '../generated-conf/config.php';
require '../config.php';
require '../include/spotify.php';
require '../include/csh.php';

session_cache_limiter(false);
session_start();

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
  $admin_only = isset($opts['admin_only'])
    ? $opts['admin_only']
    : false;
  $require_spotify = isset($opts['require_spotify'])
    ? $opts['require_spotify']
    : false;
  $require_secret = isset($opts['require_secret'])
    ? $opts['require_secret']
    : false;
  $json = isset($opts['json'])
    ? $opts['json']
    : ($rfid != null); // If an rfid is provided, a json response is assumed.

  $dieWithError = function($message, $redirect_url=null) use ($json, $app) {
    global $base_url;

    $redirect_url = $redirect_url || $base_url;

    if ($json) {
      dieWithJsonError($message);
    } else {
      $app->flash('error', $message);
      $app->redirect($base_url);
    }
  };

  if ($require_secret) {
    $request_secret = $app->request->headers->get('X-Soapy-Secret', '');
    if ($request_secret != $cfg['soapy_secret']) {
      $dieWithError("Invalid Soapy secret.");
    }
  }

  if ($rfid) {
    $user = \CSH\user_for_rfid($rfid);
    if (!$user) {
      $dieWithError("User not found!");
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
            $dieWithError(
              "Failed to refresh spotify access token: " . $e->getMessage());
          } else {
            $spotifyacct->delete();
            $dieWithError(
              "Failed to refresh spotify acess token. Unpaired. Error: " .
              $e->getMessage());
          }
        }
        // Re-fetch associated SpotifyAccount on the next access.
        $user->clearSpotifyAccounts();
        $spotifyacct = $user->getSpotifyAccount();
      }
      $api = \Spotify\get_api($spotifyacct->getAccessToken());
      $api->setReturnAssoc(true);
    } else if ($require_spotify) {
      $dieWithError("No spotify account has been linked.");
    }
  }

  if ($admin_only) {
    if (!$user || !$user->getIsAdmin()) {
      http_response_code(403);
      $dieWithError('You are not an admin.');
    }
  }

  return array(
    'base_url' => $cfg['url'],
    'auth_url' => $sp_auth_url,
    'authorized' => !!$spotifyacct,
    'sp_api' => $api,
    'user' => $user,
    'is_admin' => $user && $user->getIsAdmin(),
    'current_page' => '',
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

$app->get(
    '/?', function() use ($app) {

  $ctx = start_view_context($app);
  $ctx['main_module'] = 'main.init';
  $ctx['playlist_api_data'] = apiRawUserData($ctx);

  $app->render('app.html', $ctx);
});

$app->get(
    '/logs/?', function() use ($app) {

  $ctx = start_view_context($app, ['admin_only' => true]);

  $ctx['bathrooms'] = LogQuery::bathrooms();

  $ctx['main_module'] = 'logs.init';
  $ctx['playlist_api_data'] = apiRawUserData($ctx);
  $ctx['current_page'] = 'logs';

  $app->render('app.html', $ctx);
});

$app->get(
    '/users/?', function() use ($app) {

  $ctx = start_view_context($app, ['admin_only' => true]);

  $ctx['bathrooms'] = LogQuery::bathrooms();

  $ctx['main_module'] = 'users.init';
  $ctx['playlist_api_data'] = apiRawUserData($ctx);
  $ctx['current_page'] = 'users';

  $app->render('app.html', $ctx);
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

  try {
    \Spotify\refresh_account($spotifyacct);
  } catch (\SpotifyWebAPI\SpotifyWebAPIException $e) {
    $app->flash('error', "Failed to create SpotifyAccount. Error: " .
                         $e->getMessage());
    $app->redirect($base_url);
  }

  $app->redirect($base_url);
});

// API endpoint for unpairing the connected spotify account.
$app->post('/api/me/unpair/?', function() use ($app) {
  global $base_url;

  $ctx = start_view_context($app, ['json' => true, 'require_spotify' => true]);
  if (!$ctx) return;

  $ctx['user']->clearSelectedPlaylist();
  $ctx['user']->getSpotifyAccount()->delete();

  dieWithJsonSuccess();
});

// Raw API endpoint for fetching log data.
function apiRawGetLogs($room, $since) {
  dieWithJson([
    'events' => LogQuery::create()
      ->filterByBathroom($room)
      // +1 because $since is is exclusive but filterById min is inclusive.
      ->filterById(array('min' => $since + 1))
      ->orderByTime('desc')
      ->limit(500)
      ->find()
      ->toArray(),
  ]);
}

// API endpoint to get logs for a room.
$app->get('/api/log/view/:room', function($room) use ($app) {
  $ctx = start_view_context($app, ['admin_only' => true, 'json' => true]);

  apiRawGetLogs($room, 0);
});

// API endpoint to get logs for a room since a certain log entry.
$app->get('/api/log/view/:room/since/:lastLogId', function($room, $lastLogId) use ($app) {
  $ctx = start_view_context($app, ['admin_only' => true, 'json' => true]);

  apiRawGetLogs($room, $lastLogId);
});

// API endpoint for setting the playback settings for a user.
$app->post('/api/me/playback', function() use ($app) {
  $ctx = start_view_context($app, ['require_spotify' => true]);

  $new_playlist = $app->request->post('selectedPlaylistId');
  if ($new_playlist !== null) {
    try {
      $ctx['user']->setSelectedPlaylistById($new_playlist);
    } catch (\Exception $e) {
      dieWithJsonError($e->getMessage());
    }
  }

  $shuffle = $app->request->post('shuffle');
  if ($shuffle !== null) {
    $shuffle = ($shuffle == 'true');
    $mode = $shuffle ? 'SHUFFLE' : 'LINEAR';
    $ctx['user']->setPlaybackMode($mode);
    $ctx['user']->save();
  }

  dieWithJsonSuccess();
});

// Raw API for fetching basic user data. Doesn't call any Spotify APIs.
function apiRawUserData($ctx) {
  $json_data = [
    'user' => $ctx['user']->getDataForJson(),
    ];

  return $json_data;
}

// Raw API for fetching playlists for a user.
function apiRawGetPlaylists($ctx) {
  $json_data = [
    'user' => $ctx['user']->getDataForJson(),
    ];

  if ($ctx['authorized']) {
    try {
      $playlists = \Spotify\get_playlists($ctx['sp_api'], $ctx['user']);
    } catch (\SpotifyWebAPI\SpotifyWebAPIException $e) {
      dieWithJsonError("Error getting playlists: " . $e->getMessage());
    }

    if ($playlists) {
      $json_data['user']['playlists'] = $playlists;
    }

    $playlist = $ctx['user']->getSelectedPlaylist();
    if ($playlist) {
      $json_data['user']['selectedPlaylist'] = $playlist->getDataForJson();
    }
  }

  return $json_data;
}

// Dual API for fetching playlists for a user.
function apiHandlerGetPlaylists($ctx) {
  dieWithJson(apiRawGetPlaylists($ctx));
}

// Web API for fetching playlists for a user.
$app->get('/api/me/playlists/?', function() use ($app) {
  $ctx = start_view_context($app);

  apiHandlerGetPlaylists($ctx);
});

// Device API for fetching playlists for a user.
$app->get('/api/rfid/:rfid/playlists/?', function($rfid) use ($app) {
  $ctx = start_view_context($app, [
    'require_spotify' => true, 'rfid' => $rfid, 'require_secret' => true]);

  apiHandlerGetPlaylists($ctx);
});

// Dual API for fetching songs for a user from a specific playlist, or their
// selected playlist.
function apiHandlerGetPlaylist($ctx, $playlistId) {
  $selected_playlist = $ctx['user']->getSelectedPlaylist();
  if ($selected_playlist == null) {
    $selected_playlist_data = null;
  } else {
    $listening = $selected_playlist->getListeningForUser($ctx['user']);
    if ($listening == null) {
      dieWithJsonError("You don't have access to your selected playlist.");
    }
    $selected_playlist_data = $listening->getDataForJson();
  }

  if ($playlistId == "selected") {
    $playlist = &$selected_playlist;
    $playlist_data = &$selected_playlist_data;
  } else {
    $playlist = PlaylistQuery::create()->findPk($playlistId);
    $listening = $playlist->getListeningForUser($ctx['user']);
    if ($listening == null) {
      dieWithJsonError("You don't have access to this playlist.");
    }
    $playlist_data = $listening->getDataForJson();
  }

  if (!$playlist) {
    if ($playlistId == "selected") {
      dieWithJsonError("User has not selected a playlist.");
    } else {
      dieWithJsonError("Playlist not found.");
    }
  }

  $json_data = [
    'user' => $ctx['user']->getDataForJson(),
    ];

  try {
    $tracklist =
      \Spotify\get_formatted_tracks_for_playlist($ctx['sp_api'], $playlist);
  } catch (\SpotifyWebAPI\SpotifyWebAPIException $e) {
    dieWithJsonError("Error getting tracks: " . $e->getMessage());
  }

  $playlist_data['tracklist'] = $tracklist;

  $json_data['user']['selectedPlaylist'] = $selected_playlist_data;

  if ($playlistId != "selected") {
    $json_data['playlist'] = $playlist_data;
  }

  dieWithJson($json_data);
}

// Device API for fetching songs for a user from a specific playlist, or their
// selected playlist.
$app->get(
    '/api/rfid/:rfid/playlist/:playlistId',
    function($rfid, $playlistId) use ($app) {

  $ctx = start_view_context($app, [
    'require_spotify' => true, 'rfid' => $rfid, 'require_secret' => true]);

  apiHandlerGetPlaylist($ctx, $playlistId);
});

// Web API for fetching songs for a user from a specific playlist, or their
// selected playlist.
$app->get('/api/me/playlist/:playlistId', function($playlistId) use ($app) {

  $ctx = start_view_context($app, ['require_spotify' => true]);

  apiHandlerGetPlaylist($ctx, $playlistId);
});

// Device API for updating the current song being played.
$app->post('/api/rfid/:rfid/song/playing', function($rfid) use ($app) {
  $ctx = start_view_context($app, [
    'require_spotify' => true, 'rfid' => $rfid, 'require_secret' => true]);

  $song_uri = $app->request->post('song_uri');
  if (!$song_uri) {
    dieWithJsonError("No song URI was given.");
  }

  $playlist = $ctx['user']->getSelectedPlaylist();
  if (!$playlist) {
    dieWithJsonError("User does not have a selected playlist.");
  }

  $listening = $playlist->getListeningForUser($ctx['user']);
  if (!$listening) {
    dieWithJsonError("User does not have access to selected playlist.");
  }

  $listening->setLastPlayedSongURI($song_uri);
  $listening->save();

  dieWithJsonSuccess();
});

// Device API for submitting log messages.
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

