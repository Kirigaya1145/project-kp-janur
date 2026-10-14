<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TarifContainer extends Model {
    protected $table='tarif_container';
    protected $primaryKey='tarif_id';
    public $timestamps=false;
    protected $fillable=['rute_id','ukuran_feet','harga'];
}
