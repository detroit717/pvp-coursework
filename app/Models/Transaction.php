<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public $timestamps = false;
    protected $table = 'transactions';
    protected $primaryKey = 'id_transaction';

    protected $fillable = [
        'id_point', 'id_lane', 'id_vehicle', 'id_tariff',
        'amount', 'id_payment_method', 'id_transponder', 'status', 'datetime'
    ];

    protected $casts = [
        'datetime' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function paymentPoint()
    {
        return $this->belongsTo(PaymentPoint::class, 'id_point');
    }

    public function lane()
    {
        return $this->belongsTo(Lane::class, 'id_lane');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'id_vehicle');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'id_payment_method');
    }

    public function transponder()
    {
        return $this->belongsTo(Transponder::class, 'id_transponder');
    }

    public function tariff()
    {
        return $this->belongsTo(Tariff::class, 'id_tariff');
    }
}
