<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DetailBarangMasukController extends Controller
{
    private $mainModel;
    private $fillableMainModel;
    public function __construct()
    {
        $this->mainModel = new \App\Models\BarangMasuk();
        $this->detailModel = new \App\Models\DetailBarangMasuk();
        $this->fillableMainModel = [
            'id_barang_masuk',
            'id_barang',
            'jumlah',
            'harga_satuan',
            'subtotal',
        ];
    }
    private function getAllMainModel()
    {
        return $this->mainModel;
    }
    private function getOneMainModel($condition)
    {
        return $this->mainModel->where($condition);
    }
    private function updateStock($request, $status='add')
    {
        foreach($request as $value){
            $currentData = $this->modelBarang->where('id', $value['id_barang']);
            $getCurrentData = $currentData->first();
            $newStock = $getCurrentData->stok + $value['jumlah'];
            if ($status != 'add') {
                $newStock = $getCurrentData->stok - $value['jumlah'];
            }
            $currentData->update([
                'stok' => $newStock,
            ]);
        }
    }
    private function validasiInput($request, $type = 'store')
    {
        $validate = [];
        $result['status'] = false;
        if ($type == 'store') {
            $validate['id_barang_masuk'] = 'required';
            $validate['id_barang'] = 'required';
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
        return collect($data)->map(function($var){
            $var->total = $var->detail_barang_masuk->sum('subtotal');
            $var->total_format = formatRupiah($var->total);
            $var->tanggal_format = formatDateDMYHI($var->tanggal);
            $var->nama_supplier = $var->supplier->nama;
            $var->nama_user = $this->findUser($var->id_user)->nama;
            unset($var->detail_barang_masuk);
            unset($var->supplier);
            return $var;
        });
    }
    private function mapShowDetailData($data)
    {
        return collect($data)->map(function($var){
            $var->total = $var->detail_barang_masuk->sum('subtotal');
            $var->total_format = formatRupiah($var->total);
            $var->tanggal_format = formatDateDMYHI($var->tanggal);
            $var->nama_supplier = $var->supplier->nama;
            $var->nama_user = $this->findUser($var->id_user)->nama;
            $var->detail_barang_masuk->map(function($item){
                $item->nama_barang = $this->findBarang($item->id_barang)->nama;
                $item->harga_satuan_format = formatRupiah($item->harga_satuan);
                $item->subtotal_format = formatRupiah($item->subtotal);
                unset($item->id_barang_masuk);
                return $item;
            });
            unset($var->supplier);
            return $var;
        });
    }
    // ==================== crud function ======================================================
    public function index()
    {
        $data = $this->getAllMainModel()->get();
        return responseJson(true, 'data list', $data);
    }
    public function store(Request $request)
    {
        $validasi = $this->validasiInput($request);
        if (!$validasi['status']) {
            return responseJson(false, 'validasi error', $validasi['message'], 500);
        }
        // brng_masuk > main
        $dataRequestMain = $request->all($this->fillableMainModel);
        $dataMain = [];
        foreach ($dataRequestMain as $key => $value) {
            if ($value != null && $value != '') {
                $dataMain[$key] = $value;
            }
        }
        $dataMain['subtotal'] = $dataMain['jumlah'] * $dataMain['harga_satuan'];
        DB::beginTransaction();
        try {
            $detailBarangMasuk = $this->mainModel->create($dataMain);
            DB::commit();
            return responseJson(true, 'Data berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollback();
            return responseJson(false, 'Data gagal ditambahkan!', $e->getMessage(), 500);
        }
    }
    public function show($id)
    {
        $condition = ['id' => $id];
        $findData = $this->getOneMainModel($condition);
        $result = $findData->first();
        if (!isset($result->id)) {
            return responseJson(false, 'Data tidak ditemukan', null, 404);
        }
        return responseJson(true, 'data', $result);
    }
    public function update(Request $request, $id)
    {
        $condition = ['id' => $id];
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
        DB::beginTransaction();
        try {
            if (count($dataMain) > 0) {
                $findData->update($dataMain);
            }
            DB::commit();
            return responseJson(true, 'Data berhasil diubah!');
        } catch (\Exception $e) {
            DB::rollback();
            return responseJson(false, 'Data gagal diubah!', $e->getMessage(), 500);
        }
    }
    public function destroy($id)
    {
        $condition = ['id' => $id];
        $findData = $this->getOneMainModel($condition);
        $result = $findData->first();
        if (!isset($result->id)) {
            return responseJson(false, 'Data tidak ditemukan', null, 404);
        }
        DB::beginTransaction();
        try {
            $detail = $this->getAllDetailModel(['id_barang_masuk' => $id]);
            $dataDetail = $detail->get();
            if(count($dataDetail) > 0){
                $detail->delete();
            }
            $findData->delete();
            DB::commit();
            return responseJson(true, 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollback();
            return responseJson(false, 'Data gagal dihapus!', $e->getMessage(), 500);
        }
    }
}
