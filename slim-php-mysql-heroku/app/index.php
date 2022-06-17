<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
// require_once './middlewares/Logger.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/ProductoController.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$cointainer = $app->getContainer();

$capsule = new Capsule();

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['MYSQL_HOST'],
    'database'  => $_ENV['MYSQL_DB'],
    'username'  => $_ENV['MYSQL_USER'],
    'password'  => $_ENV['MYSQL_PASS'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();;

// ------------------------// U S U A R I O S // ------------------------
$app->group('/usuarios', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');

    $group->get('/{IdUsuario}', \UsuarioController::class . ':TraerUno');
    
    $group->post('[/]', \UsuarioController::class . ':CargarUno');

    $group->put('/{IdUsuario}', \UsuarioController::class. ':ModificarUno');

    $group->delete('/{IdUsuario}', \UsuarioController::class . ':BorrarUno');
});
// ------------------------// MESAS// ------------------------------
$app->group('/mesas', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \MesaController::class . ':TraerTodos');

    $group->get('/{IdMesa}', \MesaController::class . ':TraerUno');

    $group->post('[/]', \MesaController::class . ':CargarUno');

    $group->put('/{IdMesa}', \MesaController::class. ':ModificarUno');

    $group->delete('/{IdMesa}', \MesaController::class . ':BorrarUno');
});

// ------------------------// P R O D U C T O S // ------------------
$app->group('/productos', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \ProductoController::class . ':TraerTodos');

    $group->get('/{IdProducto}', \ProductoController::class . ':TraerUno');

    $group->post('[/]', \ProductoController::class . ':CargarUno');

    $group->put('/{IdProducto}', \ProductoController::class. ':ModificarUno');

    $group->delete('/{IdProducto}', \ProductoController::class . ':BorrarUno');
});

// ------------------------// P E D I D O S // ------------------
$app->group('/pedidos', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \PedidoController::class . ':TraerTodos');  
    
    $group->get('/{IdPedido}', \PedidoController::class . ':TraerUno');

    $group->post('[/]', \PedidoController::class . ':CargarUno');
    
    $group->put('/{IdPedido}', \PedidoController::class. ':ModificarUno');

    $group->delete('/{IdPedido}', \PedidoController::class . ':BorrarUno');
});

//----------------------------------------------------------------
$app->get('[/]', function (Request $request, Response $response) 
{    
    $response->getBody()->write("Slim Framework 4 PHP Stefano :D");
    return $response;
});

$app->run();
