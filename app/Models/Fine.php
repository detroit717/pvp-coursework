<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    public $timestamps = false;
    protected $table = 'fines';
    protected $primaryKey = 'id_fine';

    protected $fillable = [
        'id_driver', 'id_vehicle', 'id_transaction', 'id_point',
        'id_fine_type', 'amount', 'datetime', 'payment_status', 'comment'
    ];

    protected $casts = [
        'datetime' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'id_driver');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'id_vehicle');
    }

    public function fineType()
    {
        return $this->belongsTo(FineType::class, 'id_fine_type');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'id_transaction');
    }

    public function paymentPoint()
    {
        return $this->belongsTo(PaymentPoint::class, 'id_point');
    }
}
