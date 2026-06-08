<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    public $timestamps = false;
    protected $table = 'payment_methods';
    protected $primaryKey = 'id_payment_method';

    protected $fillable = ['name'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'id_payment_method');
    }
}
