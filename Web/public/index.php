<?php
require '../vendor/autoload.php';
require '../config.php';
require '../include/spotify.php';

$app = new \Slim\Slim(array(
  'templates.path' => '../templates',
));

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
  if (!isset($_SESSION['sp_refresh_token'])) {
    $app->flash('error', "You must connect a Spotify account.");
    $app->redirect('/');
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

function start_view($app, $require_auth=false) {
  if ($require_auth) {
    if (!($refresh_token = ensure_auth($app))) return;
  } else {
    $refresh_token = get_refresh_token();
  }

  if ($refresh_token) {
    $access_token = \Spotify\get_access_token($refresh_token);
  } else {
    $access_token = null;
  }

  return array(
    'sp_refresh_token' => $refresh_token,
    'sp_access_token' => $access_token,
  );
}

/* Routes */

$app->get('/', function() use ($app) {
  $ctx = start_view($app);
  render($app, $ctx);
});

// Forget the current api token in the session.
$app->get('/forgettoken/?', function() use ($app) {
  if (!ensure_auth($app)) return;
  unset($_SESSION['sp_refresh_token']);
  $app->redirect('/');
});

// Spotify redirects here after user authenticates.
$app->get('/' . $cfg['spotify']['callback_route'] . '/?', function() use ($app) {
  if (get_refresh_token()) {
    $app->flash('error', "You are already authenticated with Spotify.");
    $app->redirect('/');
    return;
  }

  $error = $app->request->get('error');
  if (isset($error)) {
    $app->flash('error', "You can't use Soapy until you accept Spotify permissions.");
    $app->redirect('/');
    return;
  }

  $code = $app->request->get('code');
  if (!isset($code)) {
    $app->flash('error', "Expected Spotify authorization token.");
    $app->redirect('/');
    return;
  }

  try {
    $refresh_token = \Spotify\get_refresh_token($code);
  } catch(\SpotifyWebAPI\SpotifyWebAPIException $e) {
    $app->flash('error', "Spotify Error: " . $e->getMessage());
    $app->redirect('/');
    return;
  }

  $_SESSION['sp_refresh_token'] = $refresh_token;
  $app->redirect('/');
});

// View basic spotify account data.
$app->get('/data/basic/?', function() use ($app) {
  $ctx = start_view($app, $require_auth=true);
  if (!$ctx) return;

  $ctx['name'] = "John smith";

  render($app, $ctx, 'data_basic.html');
});

// View spotify playlists.
$app->get('/data/playlists/?', function() use ($app) {
  $ctx = start_view($app, $require_auth=true);
  if (!$ctx) return;

  render($app, $ctx, 'data_playlists.html');
});

$app->run();

