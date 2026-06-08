<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lane extends Model
{
    public $timestamps = false;
    protected $table = 'lanes';
    protected $primaryKey = 'id_lane';

    protected $fillable = ['id_point', 'lane_number', 'id_lane_type'];

    public function paymentPoint()
    {
        return $this->belongsTo(PaymentPoint::class, 'id_point');
    }

    public function laneType()
    {
        return $this->belongsTo(LaneType::class, 'id_lane_type');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'id_lane');
    }
}
