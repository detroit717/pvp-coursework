<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transponder extends Model
{
    public $timestamps = false;
    protected $table = 'transponders';
    protected $primaryKey = 'id_transponder';

    protected $fillable = ['id_vehicle', 'serial_number', 'status', 'id_driver'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'id_vehicle');
    }

    public static function generateSerialNumber(): string
    {
        $prefix = 'TRP';
        $date = now()->format('ymd');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        return $prefix . $date . $random;
    }
}
