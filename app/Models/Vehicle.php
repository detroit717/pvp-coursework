<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    public $timestamps = false;
    protected $table = 'vehicles';
    protected $primaryKey = 'id_vehicle';

    protected $fillable = ['id_auto_type', 'id_driver', 'plate_number', 'name'];

    public function autoType()
    {
        return $this->belongsTo(AutoType::class, 'id_auto_type');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'id_driver');
    }

    public function transponders()
    {
        return $this->hasMany(Transponder::class, 'id_vehicle');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'id_vehicle');
    }
}
