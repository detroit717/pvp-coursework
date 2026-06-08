<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FineType extends Model
{
    public $timestamps = false;
    protected $table = 'fine_types';
    protected $primaryKey = 'id_fine_type';

    protected $fillable = ['name', 'description'];

    public function fines()
    {
        return $this->hasMany(Fine::class, 'id_fine_type');
    }
}
