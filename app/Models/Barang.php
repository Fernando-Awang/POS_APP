<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;
    protected $table = 'barang';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
    public function kategori_barang()
    {
        return $this->belongsTo(KetegoriBarang::class, 'id_kategori_barang', 'id');
    }
}
