<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DetailPenjualanController extends Controller
{
    private $mainModel;
    private $fillableMainModel;
    private $modelkategoriBarang;
    private $modelBarang;
    public function __construct()
    {
        $this->mainModel = new \App\Models\DetailPenjualan();
        $this->modelkategoriBarang = new \App\Models\KetegoriBarang();
        $this->modelBarang = new \App\Models\Barang();
        $this->fillableMainModel = [
            'id_penjualan',
            'id_barang',
            'jumlah',
            'harga_satuan',
            'subtotal',
        ];
    }
    private function getAllMainModel($condition = null)
    {
        $data =  $this->mainModel->with('barang');
        if ($condition != null) {
            $data = $data->where($condition);
        }
        return $data;
    }
    private function getOneMainModel($condition)
    {
        return $this->mainModel->where($condition);
    }
    private function updateStock($id_barang, $diff, $status = 'add')
    {
        $currentData = $this->modelBarang->where('id', $id_barang);
        $getCurrentData = $currentData->first();
        $newStock = $getCurrentData->stok - $diff;
        if ($status != 'add') {
            $newStock = $getCurrentData->stok + $diff;
        }
        $currentData->update([
            'stok' => $newStock,
        ]);
    }
    private function validasiInput($request, $type = 'store')
    {
        $validate = [];
        $result['status'] = false;
        if ($type == 'store') {
            $validate['id_barang'] = 'required';
            $validate['jumlah'] = 'required';
        }
        if ($type == 'update') {
        }
        if (count($validate) == 0) {
            $result['status'] = true;
            return $result;
        }
        $validator = Validator::make($request->all(), $validate);
        if ($validator->fails()) {
            $result['message'] = $validator->errors()->all();
            return $result;
        }
        $result['status'] = true;
        return $result;
    }
    private function mapShowAllData($data)
    {
        return collect($data)->map(function ($var) {
            $var->nama_barang = $var->barang->nama;
            $var->kategori_barang = $this->findKategoriBarang($var->barang->id_kategori_barang)->nama;
            $var->subtotal_format = formatRupiah($var->subtotal);
            unset($var->barang);
            return $var;
        });
    }
    private function findKategoriBarang($id_kategori_barang)
    {
        return $this->modelkategoriBarang->where('id', $id_kategori_barang)->first();
    }
    private function findBarang($id_barang)
    {
        return $this->modelBarang->where('id', $id_barang)->first();
    }
    // ==================== crud function ======================================================
    public function index($id_penjualan)
    {
        $data = $this->getAllMainModel(['id_penjualan' => $id_penjualan])->get();
        $data = $this->mapShowAllData($data);
        return responseJson(true, 'data list', $data);
    }
    public function store($id_penjualan, Request $request)
    {
        $validasi = $this->validasiInput($request);
        if (!$validasi['status']) {
            return responseJson(false, 'validasi error', $validasi['message'], 500);
        }
        $dataRequestMain = $request->all($this->fillableMainModel);
        $dataMain = [];
        foreach ($dataRequestMain as $key => $value) {
            if ($value != null && $value != '') {
                $dataMain[$key] = $value;
            }
        }
        $dataMain['harga_satuan'] = $this->findBarang($dataMain['id_barang'])->harga_jual;
        $dataMain['subtotal'] = $dataMain['jumlah'] * $dataMain['harga_satuan'];
        $dataMain['id_penjualan'] = $id_penjualan;
        DB::beginTransaction();
        try {
            $detailPenjualan = $this->mainModel->create($dataMain);
            $this->updateStock($detailPenjualan->id_barang, $detailPenjualan->jumlah, 'add');
            DB::commit();
            return responseJson(true, 'Data berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollback();
            return responseJson(false, 'Data gagal ditambahkan!', $e->getMessage(), 500);
        }
    }
    public function show($id_penjualan, $id)
    {
        $condition = ['id' => $id];
        $findData = $this->getOneMainModel($condition);
        $result = $findData->first();
        if (!isset($result->id)) {
            return responseJson(false, 'Data tidak ditemukan', null, 404);
        }
        $result  = $this->mapShowAllData($findData->get())->first();
        return responseJson(true, 'data', $result);
    }
    public function update($id_penjualan, Request $request, $id)
    {
        $condition = [
            'id_penjualan' => $id_penjualan,
            'id' => $id
        ];
        $findData = $this->getOneMainModel($condition);
        $result = $findData->first();
        if (!isset($result->id)) {
            return responseJson(false, 'Data tidak ditemukan', null, 404);
        }
        // user > main
        $dataRequestMain = $request->all($this->fillableMainModel);
        $dataMain = [];
        foreach ($dataRequestMain as $key => $value) {
            if ($value != null && $value != '') {
                $dataMain[$key] = $value;
            }
        }
        $dataMain['harga_satuan'] = $this->findBarang($dataMain['id_barang'])->harga_jual;
        $jumlah = $result->jumlah;
        $status = 'add';
        $diff = 0;
        if (isset($dataMain['jumlah'])) {
            $jumlah = $dataMain['jumlah'];
            if ($jumlah < $result->jumlah) {
                $diff = $result->jumlah - $jumlah;
                $status = 'add';
            }
            if ($jumlah > $result->jumlah) {
                $diff = $jumlah - $result->jumlah;
                $status = 'remove';
            }
        }
        $dataMain['subtotal'] = $dataMain['harga_satuan'] * $jumlah;
        DB::beginTransaction();
        try {
            if (count($dataMain) > 0) {
                $detailPenjualan = $findData->update($dataMain);
                if ($diff != 0) {
                    $this->updateStock($result->id_barang, $diff, $status);
                }
            }
            DB::commit();
            return responseJson(true, 'Data berhasil diubah!');
        } catch (\Exception $e) {
            DB::rollback();
            return responseJson(false, 'Data gagal diubah!', $e->getMessage(), 500);
        }
    }
    public function destroy($id_penjualan, $id)
    {
        $condition = [
            'id_penjualan' => $id_penjualan,
            'id' => $id
        ];
        $findData = $this->getOneMainModel($condition);
        $result = $findData->first();
        if (!isset($result->id)) {
            return responseJson(false, 'Data tidak ditemukan', null, 404);
        }
        DB::beginTransaction();
        try {
            $findData->delete();
            $this->updateStock($result->id_barang, $result->jumlah, 'remove');
            DB::commit();
            return responseJson(true, 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollback();
            return responseJson(false, 'Data gagal dihapus!', $e->getMessage(), 500);
        }
    }
}
