<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    public $timestamps = false;
    protected $table = 'tariffs';
    protected $primaryKey = 'id_tariff';

    protected $fillable = ['id_auto_type', 'amount', 'time_start', 'time_end', 'day_of_week'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function autoType()
    {
        return $this->belongsTo(AutoType::class, 'id_auto_type');
    }
}
