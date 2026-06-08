<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaneType extends Model
{
    public $timestamps = false;
    protected $table = 'lane_types';
    protected $primaryKey = 'id_lane_type';

    protected $fillable = ['name'];

    public function lanes()
    {
        return $this->hasMany(Lane::class, 'id_lane_type');
    }
}
