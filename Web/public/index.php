<?php

require '../vendor/autoload.php';
require '../generated-conf/config.php';
require '../config.php';
require '../include/spotify.php';

$app = new \Slim\Slim(array(
  'templates.path' => '../templates',
));

$base_url = $cfg['url'] . '/';

$app->config($cfg);
$app->add(new \Slim\Middleware\SessionCookie());

$app->container->singleton('log', function() {
  $log = new \Monolog\Logger('slim-skeleton');
  $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
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

function get_webauth($app) {
  global $cfg;

  // This is just to fake webauth when developing on systems without it.
  if ($cfg['webauth']) {
    return [
      'ldap' => $_SERVER['WEBAUTH_USER'],
      'firstname' => $_SERVER['WEBAUTH_LDAP_GIVENNAME'],
      'lastname' => $_SERVER['WEBAUTH_LDAP_SN'],
    ];
  } else {
    return [
      'ldap' => 'csher',
      'firstname' => 'John',
      'lastname' => 'Smith',
    ];
  }
}

function start_view($app, $require_spotify=false) {
  global $cfg, $base_url, $sp_auth_url;

  $webauth = get_webauth($app);
  $user = UserQuery::GetOrCreateUser($webauth);
  $spotifyacct = SpotifyAccountQuery::findByUser($user);

  if ($require_spotify and !$spotifyacct) {
    $app->flash('error', "You must connect a Spotify account.");
    $app->redirect($base_url);
    return;
  }

  $me = null;
  $api = null;

  if ($spotifyacct) {
    if (time() > $spotifyacct->getExpiration()->getTimestamp()) {
      \Spotify\refresh_account($spotifyacct);
    }

    $api = \Spotify\get_api($spotifyacct->getAccessToken());
  }

  return array(
    'base_url' => $cfg['url'],
    'auth_url' => $sp_auth_url,
    'spotifyacct' => $spotifyacct,
    'authorized' => !!$spotifyacct,
    'sp_api' => $api,
    'user' => $user,
  );
}

/* Routes */

$app->get('/', function() use ($app) {
  global $base_url;

  $ctx = start_view($app);

  if ($ctx['spotifyacct']) {
    $app->redirect($base_url . 'data/playlists');
    return;
  }

  $app->render('index.html', $ctx);
});

// Spotify redirects here after user authenticates.
$app->get('/' . $cfg['spotify']['callback_route'] . '/?', function() use ($app) {
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

  $ctx = start_view($app, $require_spotify=true);
  if (!$ctx) return;

  $ctx['spotifyacct']->delete();

  $app->redirect($base_url);
});

// View spotify playlists.
$app->get('/data/playlists/?', function() use ($app) {
  $ctx = start_view($app, $require_spotify=true);
  if (!$ctx) return;

  $api = $ctx['sp_api'];
  $api->setReturnAssoc(true);

  try {
    $playlists = $api->getUserPlaylists($ctx['spotifyacct']->getUsername());
    $ctx['playlists'] = $playlists['items'];
  } catch (Exception $e) {
    $app->flash('error', 'Spotify error: ' . $e->getMessage());
    $ctx['playlists'] = array();
  }

  $app->render('data_playlists.html', $ctx);
});

$app->run();

