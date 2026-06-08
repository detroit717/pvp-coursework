<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Driver extends Model
{
    public $timestamps = false;
    protected $table = 'drivers';
    protected $primaryKey = 'id_driver';

    protected $fillable = ['full_name', 'phone_number', 'birth_date', 'personal_balance', 'password'];

    protected $hidden = ['password'];

    protected $casts = [
        'birth_date' => 'date',
        'personal_balance' => 'decimal:2',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'id_driver');
    }

    public function fines()
    {
        return $this->hasMany(Fine::class, 'id_driver');
    }

    public function getDebtAttribute()
    {
        return DB::selectOne("SELECT COALESCE(SUM(amount), 0) as debt FROM fines WHERE id_driver = ? AND payment_status = 'неоплачен'", [$this->id_driver])->debt;
    }
}
