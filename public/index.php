<?php

use Models\Module;
use Models\OperationLog;

// Impostazioni di configurazione PHP
date_default_timezone_set('Europe/Rome');
// Disabilita i messaggi nativi di PHP
//ini_set('display_startup_errors', 0);
//ini_set('display_errors', 0);
// Ignora gli avvertimenti e le informazioni relative alla deprecazione di componenti
//error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_USER_DEPRECATED & ~E_STRICT);

// Controllo sulla versione PHP
$minimum = '7.1.0';
if (version_compare(phpversion(), $minimum) < 0) {
    echo '
<p>Stai utilizzando la versione PHP '.phpversion().', non compatibile con OpenSTAManager.</p>

<p>Aggiorna PHP alla versione >= '.$minimum.'.</p>';
    exit();
}

// Caricamento delle dipendenze e delle librerie del progetto
$loader = require_once __DIR__.'/../vendor/autoload.php';

$namespaces = require_once __DIR__.'/../config/namespaces.php';
foreach ($namespaces as $path => $namespace) {
    $loader->addPsr4($namespace.'\\', __DIR__.'/../'.$path.'/custom/src');
    $loader->addPsr4($namespace.'\\', __DIR__.'/../'.$path.'/src');
}

// Individuazione dei percorsi di base
App::definePaths(__DIR__.'/..');

$docroot = DOCROOT;
$rootdir = ROOTDIR;
$baseurl = BASEURL;

// Configurazione standard
$config = App::getConfig();

// Redirect al percorso HTTPS se impostato nella configurazione
if (!empty($config['redirectHTTPS']) && !isHTTPS(true)) {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    exit();
}

$debug = App::debug();

// Inizializzazione Dependency Injection
$container = new \Slim\Container([
    'settings' => [
        'displayErrorDetails' => $debug,
        'addContentLengthHeader' => false,
        'determineRouteBeforeAppMiddleware' => true,
    ],
]);
App::setContainer($container);

// Istanziamento della sessione
ini_set('session.use_trans_sid', '0');
ini_set('session.use_only_cookies', '1');

$container['uri'] = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));

session_set_cookie_params(0, $container['uri']->getBasePath());
session_cache_limiter(false);
session_start();

// Impostazione di debug
$container['debug'] = $debug;

// Logger
$logger = new Logger($container);
$container['logger'] = $logger;

// Configurazione
$container['config'] = $config;

// Database
$database = new Database($config['db_host'], $config['db_username'], $config['db_password'], $config['db_name']);
$container['database'] = $database;

// Istanziamento delle dipendenze
require __DIR__.'/../config/dependencies.php';

// Debugbar
if (App::debug()) {
    $debugbar = new \DebugBar\StandardDebugBar();

    $debugbar->addCollector(new \Extensions\EloquentCollector($container['database']->getCapsule()));
    $debugbar->addCollector(new \DebugBar\Bridge\MonologCollector($container['logger']));

    $paths = App::getPaths();
    $debugbarRenderer = $debugbar->getJavascriptRenderer();
    $debugbarRenderer->setIncludeVendors(false);
    $debugbarRenderer->setBaseUrl($paths['assets'].'/php-debugbar');

    $container['debugbar'] = $debugbarRenderer;
}

// Istanziamento dell'applicazione Slim
$app = new \Slim\App($container);

// Aggiunta dei percorsi
require __DIR__.'/../routes/web.php';

// Aggiunta dei middleware
require __DIR__.'/../config/middlewares.php';

// Inizializzazione percorsi per i moduli
$modules = Module::withoutGlobalScope('enabled')
    ->get();
foreach ($modules as $module) {
    $class = $module->class;
    $class->boot($app, $module);
}

// Run application
$response = $app->run(true);
$html = $response->getBody()->__toString();

// Configurazione templating personalizzato
if (!empty($config['HTMLWrapper'])) {
    HTMLBuilder\HTMLBuilder::setWrapper($config['HTMLWrapper']);
}

foreach ((array) $config['HTMLHandlers'] as $key => $value) {
    HTMLBuilder\HTMLBuilder::setHandler($key, $value);
}

foreach ((array) $config['HTMLManagers'] as $key => $value) {
    HTMLBuilder\HTMLBuilder::setManager($key, $value);
}

$id_module = Modules::getCurrent()['id'];
$id_plugin = Plugins::getCurrent()['id'];

$html = str_replace('$id_module$', $id_module, $html);
$html = str_replace('$id_plugin$', $id_plugin, $html);
//$html = str_replace('$id_record$', $id_record, $html);

$html = \HTMLBuilder\HTMLBuilder::replace($html);

$body = new Slim\Http\Body(fopen('php://temp', 'r+'));
$body->write($html);

$response = $response->withBody($body);
$app->respond($response);

// Informazioni estese sulle azioni dell'utente
$op = post('op');
if (!empty($op)) {
    OperationLog::setInfo('id_module', $id_module);
    OperationLog::setInfo('id_plugin', $id_plugin);
    OperationLog::setInfo('id_record', $id_record);

    OperationLog::build($op);
}

// Annullo le notifiche (AJAX)
if (isAjaxRequest()) {
    flash()->clearMessage('info');
}
