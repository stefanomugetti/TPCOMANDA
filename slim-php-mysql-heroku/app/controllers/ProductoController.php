<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\Producto as Producto;

class ProductoController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $body = $request->getParsedBody();

        $nombre = $body['nombre'];
        $precio = $body['precio'];
        $tiempo = $body['tiempoEspera'];
        $area = $body['area'];
        $tipo = $body['tipo'];
        $stock = $body['stock'];

        $producto = new Producto();
        $producto->Nombre = $nombre;
        $producto->PrecioUnidad = $precio;
        $producto->TiempoEspera = $tiempo;
        $producto->Area = $area;
        $producto->TipoProducto = $tipo;
        $producto->Stock = $stock;

        $producto->save();

        $payload = json_encode(array("mensaje" => "Producto creado"));
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $idRecibida = $args['IdProducto'];
        $productoEncontrado = App\Models\Producto::find($idRecibida);
            
        if ($productoEncontrado != null)
        {
            $productoEncontrado->delete();
            $payload = json_encode(array("mensaje" => "Producto borrado"));
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Error al eliminar")); 
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $id = $args['IdProducto'];

        $producto = App\Models\Producto::where('IdProducto', '=', $id)->first();
        
        $body = json_decode(file_get_contents("php://input"), true);

        if ($producto != null)
        {
            $nombre = $body['nombre'];
            $precio = $body['precio'];
            $tiempo = $body['tiempo'];
            $area = $body['area'];
            $tipo = $body['tipo'];
            $stock = $body['stock'];

            $producto->Nombre = $nombre;
            $producto->PrecioUnidad = $precio;
            $producto->TiempoEspera = $tiempo;
            $producto->Area = $area;
            $producto->TipoProducto = $tipo;
            $producto->Stock = $stock;
        
            $producto->save();
            $payload = json_encode(array("mensaje" => "Producto modificado"));
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Producto no modificado")); 
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');    
    }

    public function TraerTodos($request, $response, $args)
    {
        $listaProductos = App\Models\Producto::all();
    
        $payload = json_encode(array("listaProductos" => $listaProductos));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $idRecibido = $args['IdProducto'];

        $listaProductos = Producto::all();
        $producto = $listaProductos->find($idRecibido);

        $payload = json_encode($producto);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}