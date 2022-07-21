<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\KetegoriBarang;

class Barang extends Model
{
    use HasFactory;
    protected $table = 'barang';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
    public function kategori_barang()
    {
        return $this->belongsTo(KetegoriBarang::class, 'kategori_barang_id', 'id');
    }
}
