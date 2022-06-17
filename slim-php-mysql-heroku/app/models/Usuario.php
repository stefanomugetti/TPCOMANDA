<?php

namespace app\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'IdUsuario';
    protected $table = 'usuarios';
    public $incremeting = true;
    public $timestamps = true;

    const CREATED_AT = 'FechaAlta';
    const DELETED_AT = 'FechaBaja';
    const UPDATED_AT = 'FechaModificacion';

    public $fillable = [
        'Usuario','Clave','Nombre',
        'Apellido','Estado',
        'Puesto','FechaAlta','FechaBaja','FechaModificacion'
    ]; 
}

?>