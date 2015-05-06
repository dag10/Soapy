<?php
require '../vendor/autoload.php';
require '../config.php';

$app = new \Slim\Slim(array(
  'templates.path' => '../templates',
));

$app->config($cfg);

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

/* Routes */

$app->get('/', function() use ($app) {
  $app->render('index.html');
});

$app->get('/callback', function() use ($app) {
  echo 'TODO: Callback handler.';
});

$app->run();

