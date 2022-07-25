<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailBarangMasuk extends Model
{
    use HasFactory;
    protected $table = 'detail_barang_masuk';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
    public function barang_masuk()
    {
        return $this->belongsTo(BarangMasuk::class, 'id_barang_masuk');
    }
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }
}
