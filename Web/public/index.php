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

function render($app, $ctx, $template='index.html') {
  global $sp_auth_url;
  $sp_access_token = $ctx['sp_access_token'];

  $base_ctx = array(
    'auth_url' => $sp_auth_url,
    'authorized' => !!$sp_access_token,
    'sp_access_token' => $sp_access_token,
  );

  $ctx = $ctx ? array_merge($base_ctx, $ctx) : $base_ctx;

  $app->render($template, $ctx);
}

function ensure_auth($app) {
  global $base_url;

  if (!isset($_SESSION['sp_refresh_token'])) {
    $app->flash('error', "You must connect a Spotify account.");
    $app->redirect($base_url);
    return null;
  }

  return $_SESSION['sp_refresh_token'];
}

function get_refresh_token() {
  if (isset($_SESSION['sp_refresh_token'])) {
    return $_SESSION['sp_refresh_token'];
  }

  return null;
}

function get_webauth($app) {
  global $cfg;

  if ($cfg['webauth']) {
    return [
      'ldap' => $_SERVER['WEBAUTH_USER'],
      'firstname' => $_SERVER['WEBAUTH_LDAP_GIVENNAME'],
      'lastname' => $_SERVER['WEBAUTH_LDAP_SN'],
    ];
  } else {
    return [
      'ldap' => 'dag10',
      'firstname' => 'Drew',
      'lastname' => 'Gottlieb',
    ];
  }
}

function get_me($api) {
  $me_obj = $api->me();

  $me = array(
    'name' => $me_obj->display_name,
    'user_id' => $me_obj->id,
  );

  $images = $me_obj->images;
  if ($images) {
    $me['image_url'] = $images[0]->url;
  }

  return $me;
}

function start_view($app, $require_auth=false) {
  global $cfg;

  if ($require_auth) {
    if (!($refresh_token = ensure_auth($app))) return;
  } else {
    $refresh_token = get_refresh_token();
  }

  // TODO: Cache this in a db and only fetch new access token when expired.
  if ($refresh_token) {
    $access_token = \Spotify\get_access_token($refresh_token);
  } else {
    $access_token = null;
  }

  $api = $refresh_token ? \Spotify\get_api($access_token) : null;

  // TODO: Cache user data in a db instead of fetching it every time.
  $me = $api ? get_me($api) : null;

  $webauth = get_webauth($app);
  $user = UserQuery::GetOrCreateUser($webauth);

  return array(
    'base_url' => $cfg['url'],
    'sp_refresh_token' => $refresh_token,
    'sp_access_token' => $access_token,
    'sp_api' => $api,
    'sp_user_data' => $me,
    'user' => $user,
  );
}

/* Routes */

$app->get('/', function() use ($app) {
  global $base_url;

  $ctx = start_view($app);

  if ($ctx['sp_access_token']) {
    $app->redirect($base_url . 'data/playlists');
    return;
  }

  render($app, $ctx);
});

// Forget the current api token in the session.
$app->get('/forgettoken/?', function() use ($app) {
  global $base_url;

  if (!ensure_auth($app)) return;
  unset($_SESSION['sp_refresh_token']);
  $app->redirect($base_url);
});

// Spotify redirects here after user authenticates.
$app->get('/' . $cfg['spotify']['callback_route'] . '/?', function() use ($app) {
  global $base_url;

  if (get_refresh_token()) {
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

  $_SESSION['sp_refresh_token'] = $refresh_token;
  $app->redirect($base_url);
});

// View spotify playlists.
$app->get('/data/playlists/?', function() use ($app) {
  $ctx = start_view($app, $require_auth=true);
  if (!$ctx) return;

  $api = $ctx['sp_api'];

  $api->setReturnAssoc(true);

  try {
    $playlists = $api->getUserPlaylists($ctx['sp_user_data']['user_id']);
    $ctx['playlists'] = $playlists['items'];
  } catch (Exception $e) {
    $app->flash('error', 'Spotify error: ' . $e->getMessage());
    $ctx['playlists'] = array();
  }

  render($app, $ctx, 'data_playlists.html');
});

$app->run();

