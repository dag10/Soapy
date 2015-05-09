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

function render($app, $ctx=null, $template='index.html') {
  global $sp_auth_url;
  $token = isset($_SESSION['token']) ? $_SESSION['token'] : null;

  $base_ctx = array(
    'auth_url' => $sp_auth_url,
    'authorized' => !!$token,
    'token' => $token,
  );

  $ctx = $ctx ? array_merge($base_ctx, $ctx) : $base_ctx;

  $app->render($template, $ctx);
}

function ensure_auth($app) {
  if (!isset($_SESSION['token'])) {
    $app->flash('error', "You must connect a Spotify account.");
    $app->redirect('/');
    return false;
  }

  return true;
}

/* Routes */

$app->get('/', function() use ($app) {
  render($app);
});

// Forget the current api token in the session.
$app->get('/forgettoken/?', function() use ($app) {
  if (!ensure_auth($app)) return;

  unset($_SESSION['token']);
  $app->redirect('/');
});

// Spotify redirects here after user authenticates.
$app->get('/' . $cfg['spotify']['callback_route'] . '/?', function() use ($app) {
  $ctx = array();

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
    $retarr = \Spotify\get_api($code);
  } catch(\SpotifyWebAPI\SpotifyWebAPIException $e) {
    $app->flash('error', "Spotify Error: " . $e->getMessage());
    $app->redirect('/');
    return;
  }

  $access_token = $retarr[0];
  $api = $retarr[1];

  $_SESSION['token'] = $access_token;
  $app->redirect('/');
});

// View basic spotify account data.
$app->get('/data/basic/?', function() use ($app) {
  if (!ensure_auth($app)) return;
  $token = $_SESSION['token'];

  $ctx = array(
    'name' => "Bob Smith",
  );

  render($app, $ctx, 'data_basic.html');
});

// View spotify playlists.
$app->get('/data/playlists/?', function() use ($app) {
  if (!ensure_auth($app)) return;
  $token = $_SESSION['token'];

  $ctx = array(
    'name' => "Bob Smith",
  );

  render($app, $ctx, 'data_playlists.html');
});

$app->run();

