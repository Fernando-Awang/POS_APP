<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPenjualan extends Model
{
    use HasFactory;
    protected $table = 'retur_penjualan';
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
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
