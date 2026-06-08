<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoType extends Model
{
    public $timestamps = false;
    protected $table = 'auto_types';
    protected $primaryKey = 'id_auto_type';

    protected $fillable = ['name'];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'id_auto_type');
    }

    public function tariffs()
    {
        return $this->hasMany(Tariff::class, 'id_auto_type');
    }
}
