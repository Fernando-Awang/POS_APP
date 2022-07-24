<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    use HasFactory;
    protected $table = 'barang_masuk';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
    public function detail_barang_masuk()
    {
        return $this->hasMany(DetailBarangMasuk::class, 'id_barang_masuk');
    }
}
