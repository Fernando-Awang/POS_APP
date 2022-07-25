<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenjualan extends Model
{
    use HasFactory;
    protected $table = 'detail_penjualan';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan');
    }
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }
}
