<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenjualanController extends Controller
{
    private $mainModel;
    private $detailModel;
    private $fillableMainModel;
    private $fillableDetailModel;
    private $modelBarang;
    private $modelUser;
    private $modelkategoriBarang;
    public function __construct()
    {
        $this->mainModel = new \App\Models\Penjualan();
        $this->detailModel = new \App\Models\DetailPenjualan();
        $this->modelBarang = new \App\Models\Barang();
        $this->modelUser = new \App\Models\User();
        $this->modelDetailUser = new \App\Models\DetailUser();
        $this->modelkategoriBarang = new \App\Models\KetegoriBarang();
        $this->fillableMainModel = [
            'no_nota',
            'id_user',
            'id_pelanggan',
            'tanggal',
            'keterangan',
        ];
        $this->fillableDetailModel = [
            'id_penjualan',
            'id_barang',
            'jumlah',
            'harga_satuan',
            'subtotal',
            'keuntungan',
        ];
    }
    private function getAllMainModel()
    {
        return $this->mainModel->with('detail_penjualan', 'pelanggan');
    }
    private function getOneMainModel($condition)
    {
        return $this->mainModel->where($condition)->with('detail_penjualan', 'pelanggan');
    }
    private function getAllDetailModel($condition)
    {
        return $this->detailModel->where($condition);
    }
    private function updateStock($request, $status = 'add')
    {
        foreach ($request as $value) {
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
    private function findUser($id_user)
    {
        return $this->modelDetailUser->where('id_user', $id_user)->first();
    }
    private function findBarang($id_barang)
    {
        return $this->modelBarang->where('id', $id_barang)->first();
    }
    private function findKategoriBarang($id_kategori_barang)
    {
        return $this->modelkategoriBarang->where('id', $id_kategori_barang)->first();
    }
    private function validasiInput($request, $type = 'store')
    {
        $validate = [];
        $result['status'] = false;
        if ($type == 'store') {
            $validate['id_pelanggan'] = 'required';
        }
        if ($type == 'update') {
        }
        if (count($validate) == 0) {
            $result['status'] = true;
            return $result;
        }
        $validator = Validator::make($request->all(), $validate);
        if ($validator->fails()) {
            $result['message'] = $validator->errors();
            return $result;
        }
        $result['status'] = true;
        return $result;
    }
    private function mapShowAllData($data)
    {
        return collect($data)->map(function ($var) {
            $var->total = $var->detail_penjualan->sum('subtotal');
            $var->total_format = formatRupiah($var->total);
            $var->tanggal_format = formatDateDMYHI($var->tanggal);
            $var->nama_pelanggan = $var->pelanggan->nama;
            $var->nama_user = $this->findUser($var->id_user)->nama;
            unset($var->detail_penjualan);
            unset($var->pelanggan);
            return $var;
        });
    }
    private function mapShowDetailData($data)
    {
        return collect($data)->map(function ($var) {
            $var->total = $var->detail_penjualan->sum('subtotal');
            $var->total_format = formatRupiah($var->total);
            $var->tanggal_format = formatDateDMYHI($var->tanggal);
            $var->nama_pelanggan = $var->pelanggan->nama;
            $var->nama_user = $this->findUser($var->id_user)->nama;
            $var->detail_penjualan->map(function ($item) {
                $dataBarang = $this->findBarang($item->id_barang);
                $item->nama_barang = $dataBarang->nama;
                $item->kategori_barang = $this->findKategoriBarang($dataBarang->id_kategori_barang)->nama;
                $item->harga_satuan_format = formatRupiah($item->harga_satuan);
                $item->subtotal_format = formatRupiah($item->subtotal);
                unset($item->id_penjualan);
                return $item;
            });
            unset($var->pelanggan);
            return $var;
        });
    }
    // ==================== crud function ======================================================
    public function index()
    {
        $data = $this->getAllMainModel()->get();
        return responseJson(true, 'data list', $this->mapShowAllData($data));
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
        $dataMain['tanggal'] = date('Y-m-d H:i:s');
        $dataMain['id_user'] = auth()->user()->id;
        DB::beginTransaction();
        try {
            $dataBarangMasuk = $this->mainModel->create($dataMain);
            // detail > detail
            if (isset($request->detail_penjualan)) {
                $dataDetail = collect($request->detail_penjualan)->map(function ($var) use ($dataBarangMasuk) {
                    $var['id_penjualan'] = $dataBarangMasuk->id;
                    $dataBarang = $this->findBarang($var['id_barang']);
                    $var['harga_satuan'] = $dataBarang->harga_jual;
                    $var['subtotal'] = $var['jumlah'] * $var['harga_satuan'];
                    $var['keuntungan'] = ($dataBarang->harga_jual - $dataBarang->harga_beli) * $var['jumlah'];
                    return $var;
                })->toArray();
                $this->detailModel->insert($dataDetail);
                $this->updateStock($dataDetail, 'remove');
            }
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
        $result = $findData->get();
        if (count($result) == 0) {
            return responseJson(false, 'Data tidak ditemukan', null, 404);
        }
        return responseJson(true, 'data', $this->mapShowDetailData($result)->first());
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
            $detail = $this->getAllDetailModel(['id_penjualan' => $id]);
            $dataDetail = $detail->get();
            if (count($dataDetail) > 0) {
                $this->updateStock(collect($dataDetail)->toArray(), 'add');
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
