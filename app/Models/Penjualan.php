<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;
    protected $table = 'penjualan';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
    public function detail_penjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'id_penjualan');
    }
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
