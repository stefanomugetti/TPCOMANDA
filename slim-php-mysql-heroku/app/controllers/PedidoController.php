<?php
date_default_timezone_set("America/Buenos_Aires");
require_once './models/Pedido.php';
require_once './models/PedidoDetalle.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\Pedido as Pedido;
use \App\Models\PedidoDetalle as PedidoDetalle;

class PedidoController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $body = json_decode(file_get_contents("php://input"), true);
        $idMesa = $body['IdMesa'];
        $idUsuario = $body['IdUsuario'];
        $nombreCliente = $body['nombreCliente'];
        $pathFoto = $body['pathFoto'];
        $productosPedidos = $body['productosPedidos'];

        $mesaEncontrada = App\Models\Mesa::find($idMesa);
        if ($mesaEncontrada != null) {
            $usuario = App\Models\Usuario::find($idUsuario);
            if ($usuario != null  || $usuario["Estado"] != 'Ocupado') {
                
                $importeParcial = 0;
                $flag = false;

                if (count($productosPedidos) > 0) {
                    foreach ($productosPedidos as $productoPostman) {
                        $producto = App\Models\Producto::find($productoPostman["idProducto"]);
                        if($producto != null){
                            $cantidad = $productoPostman["cantidad"];
                            if($producto->Stock >= $productoPostman["cantidad"]){
                                $importeParcial += $producto->PrecioUnidad * $cantidad;
                               //Saco el tiempo estimado del pedido
                                if (!$flag)
                                $tiempoEstimado = $producto->TiempoEspera;
                                if ($producto->TiempoEspera > $tiempoEstimado)
                                $tiempoEstimado = $producto->TiempoEspera;
                                
                            }
                            else{//NO HAY STOCK DEL PRODUCTO
                                $response->getBody()->write('No hay stock del producto pedido.');
                                return $response->withHeader('Content-Type', 'application/json');
                            }
                        }else{//PRODUCTO NO EXISTE
                            $response->getBody()->write('El producto no esta disponible. <br>Revise el menu.');
                            return $response->withHeader('Content-Type', 'application/json');
                            break; 
                        }
                    }
                    //-------------------------------- CREACION DEL PEDIDO ---------------------------------------------//

                    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                    $codigoAlfanumericoCreado = substr(str_shuffle($permitted_chars), 0, 10);

                    $pedidoCreado = new Pedido();

                    $pedidoCreado->CodigoPedido = $codigoAlfanumericoCreado;
                    $pedidoCreado->Estado = "Pendiente";
                    $pedidoCreado->ImporteTotal = $importeParcial;
                    $pedidoCreado->TiempoPreparacion = $tiempoEstimado;
                    $pedidoCreado->NombreCliente = $nombreCliente;
                    $pedidoCreado->IdMesa = $mesaEncontrada->IdMesa;
                    $pedidoCreado->IdUsuario = $usuario->IdUsuario;
                    $pedidoCreado->PathFoto = $pathFoto;

                    $pedidoCreado->save();
                    
                    foreach ($productosPedidos as $producto2) {
                        $pedidoDetalle = new PedidoDetalle();
                        $pedidoDetalle->Cantidad = $producto2["cantidad"];
                        $pedidoDetalle->Estado = "preparandose";
                        $pedidoDetalle->IdProducto = $producto2["idProducto"];
                        $pedidoDetalle->IdPedido = $pedidoCreado->IdPedido;
                        $pedidoDetalle->save();
                        //RESTO STOCK A LOS PRODUCTOS
                        $producto = App\Models\Producto::find($producto2["idProducto"]);
                        $producto->Stock = intval($producto->Stock) - intval($pedidoDetalle->Cantidad); 
                        $producto->save();
                    }
                    
                    $payload = json_encode(array("mensaje" => "Pedido creado"));
                } else {
                    $payload = json_encode(array("mensaje" => "No hay productos en el pedido."));
                }
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write('No se encontro el usuario o esta ocupado.');
                return $response->withHeader('Content-Type', 'application/json');
            }
        } else {
            $response->getBody()->write('No se encontro la mesa.');
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['IdPedido'];

        $pedido = App\Models\Pedido::find($id);
        $listaDetalles = App\Models\PedidoDetalle::all();
        if ($pedido != null && count($listaDetalles) > 0) {
            $pedido->delete();
            foreach ($listaDetalles as $detalle) {
                if($detalle->IdPedido == $pedido->IdPedido){
                    $detalle->delete();
                }
            }
            $payload = json_encode(array("mensaje" => "Pedido borrado"));
        } else {
            $payload = json_encode(array("mensaje" => "Error al eliminar"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $id = $args['IdPedido'];
        $body = json_decode(file_get_contents("php://input"), true);
        $estado = $body['Estado'];
        $pedido = App\Models\Pedido::find($id);
        $listaDetalles = App\Models\PedidoDetalle::all();
        if ($pedido != null && count($listaDetalles) > 0) {
            $pedido->Estado = $estado;
            if($estado == 'Cancelado'){
                $pedido->delete();
                foreach ($listaDetalles as $detalle) {
                    if($detalle->IdPedido == $pedido->IdPedido){
                        $detalle->delete();
                    }
                }
            }else if($estado == 'Entregado'){
                $pedido->HoraFin = date('h:i:s');
                foreach ($listaDetalles as $detalle) {
                    if($detalle->IdPedido == $pedido->IdPedido){
                        $detalle->Estado = 'Entregado';
                        $detalle->save();
                    }
                }
            }
            $pedido->save();
            $payload = json_encode(array("mensaje" => "Nuevo estado: " . $estado));
        } else {
            $payload = json_encode(array("mensaje" => "Error al eliminar"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $listaPedidos = App\Models\PedidoDetalle::all();


        if (count($listaPedidos) <= 0) {
            $payload = json_encode(array("mensaje" => "No existe ningun pedido."));
        } else {
            $payload = json_encode(array("listaPedidos" => $listaPedidos));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args['IdPedido'];

        $pedido = App\Models\Pedido::find($id);

        if ($pedido != null) {
            $payload = json_encode($pedido);
        } else {
            $payload = json_encode(array("mensaje" => "Pedido no encontrado."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
