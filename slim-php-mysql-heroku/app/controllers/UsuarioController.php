<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\Usuario as Usuario;

class UsuarioController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $nombre = $parametros['nombre'];
        $apellido = $parametros['apellido'];
        $estado = $parametros['estado'];
        $puesto = $parametros['puesto'];

        $usuarioCreado = new Usuario();

        $claveHasheada = password_hash($clave, PASSWORD_DEFAULT);

        $usuarioCreado->Usuario = $usuario;
        $usuarioCreado->Clave = $claveHasheada;
        $usuarioCreado->Nombre = $nombre;
        $usuarioCreado->Apellido = $apellido;
        $usuarioCreado->Estado = $estado;
        $usuarioCreado->Puesto = $puesto;
        
        $usuarioCreado->save();
        //logs
        
        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['IdUsuario'];

        $usuario = App\Models\Usuario::find($id);
            
        if ($usuario != null)
        {
            $usuario->delete();
            $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Usuario no eliminado")); 
        }
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $id = $args['IdUsuario'];

        $usuario = App\Models\Usuario::where('IdUsuario', '=', $id)->first();
        
        $body = json_decode(file_get_contents("php://input"), true);

        if ($usuario != null)
        {
            $user = $body['usuario'];
            $clave = $body['clave'];
            $nombre = $body['nombre'];
            $apellido = $body['apellido'];
            $estado = $body['estado'];
            $puesto = $body['puesto'];

            $usuario->Usuario = $user;
            $usuario->Clave = $clave;
            $usuario->Nombre = $nombre;
            $usuario->Apellido = $apellido;
            $usuario->Estado = $estado;
            $usuario->Puesto = $puesto;
        
            $usuario->save();
            $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Usuario no modificado")); 
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');    
    }

    public function TraerTodos($request, $response, $args)
    {
        $listaUsuarios = App\Models\Usuario::all();
        
        $payload = json_encode(array("listaUsuarios" => $listaUsuarios));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args['IdUsuario'];

        $listaUsuarios = Usuario::all();
        $usuario = $listaUsuarios->find($id);

        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>