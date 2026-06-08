<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentPoint extends Model
{
    public $timestamps = false;
    protected $table = 'payment_points';
    protected $primaryKey = 'id_point';

    protected $fillable = ['name', 'location', 'lanes_count'];

    public function lanes()
    {
        return $this->hasMany(Lane::class, 'id_point');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'id_point');
    }

    public function activeLanesCount()
    {
        return $this->lanes()->count();
    }

    public function totalRevenue()
    {
        return $this->transactions()->where('status', 'успешно')->sum('amount');
    }
}
