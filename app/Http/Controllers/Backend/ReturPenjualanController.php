<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReturPenjualanController extends Controller
{
    private $mainModel;
    private $fillableMainModel;
    private $modelkategoriBarang;
    private $modelBarang;
    private $modelDetailPenjualan;
    private $modelPenjualan;
    public function __construct()
    {
        $this->mainModel = new \App\Models\ReturPenjualan();
        $this->modelkategoriBarang = new \App\Models\KetegoriBarang();
        $this->modelBarang = new \App\Models\Barang();
        $this->modelDetailPenjualan = new \App\Models\DetailPenjualan();
        $this->modelPenjualan = new \App\Models\Penjualan();
        $this->fillableMainModel = [
            'id_penjualan',
            'id_barang',
            'id_user',
            'tanggal',
            'jumlah'
        ];
    }
    private function getAllMainModel($condition = null)
    {
        $data =  $this->mainModel->with('penjualan','barang', 'user');
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
        $newStock = $getCurrentData->stok + $diff;
        if ($status == 'subtract') {
            $newStock = $getCurrentData->stok - $diff;
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
            $validate['jumlah'] = 'required';
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
            $var->nama_barang = $var->barang->nama;
            $var->kategori_barang = $this->findKategoriBarang($var->barang->id_kategori_barang)->nama;
            $var->nama_user = $var->user->nama;
            $var->tanggal_format = formatDateDMYHI($var->tanggal);
            unset($var->barang);
            unset($var->user);
            unset($var->penjualan);
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
    private function findDetailPenjualan($id_barang, $condition = null)
    {
        $data = $this->modelDetailPenjualan->where('id_barang', $id_barang);
        if ($condition != null) {
            $data = $data->where($condition);
        }
        return $data;
    }
    private function findPenjualan($id_penjualan)
    {
        $data = $this->modelPenjualan->where('id', $id_penjualan);
        return $data;
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
        $penjualan = $this->findPenjualan($id_penjualan);
        $detailPenjualan = $this->findDetailPenjualan($request->id_barang, ['id_penjualan' => $id_penjualan]);
        $dataDetailPenjualan = $detailPenjualan->first();
        // if (count($penjualan->get()) == 0) {
        //    return responseJson(false, 'data penjualan tidak ditemukan', [], 500);
        // }
        // if (count($detailPenjualan->get()) == 0) {
        //    return responseJson(false, 'data detail penjualan tidak ditemukan', [], 500);
        // }
        // $dataDetailPenjualan = $detailPenjualan->first();
        // if ($dataDetailPenjualan->jumlah < $request->jumlah) {
        //     return responseJson(false, 'jumlah barang tidak mencukupi', [], 500);
        // }
        $dataRequestMain = $request->all($this->fillableMainModel);
        $dataMain = [];
        foreach ($dataRequestMain as $key => $value) {
            if ($value != null && $value != '') {
                $dataMain[$key] = $value;
            }
        }
        $dataMain['id_penjualan'] = $id_penjualan;
        $dataMain['id_user'] = userId();
        DB::beginTransaction();
        try {
            $query = $this->mainModel->create($dataMain);
            // update transaksi penjualan
            $penjualan->update([
                'retur' => 'true',
            ]);
            // update detail transaksi penjualan
            $newJumlahDetailPenjualan = $dataDetailPenjualan->jumlah - $query->jumlah;
            $detailPenjualan->update([
                'jumlah' => $newJumlahDetailPenjualan,
                'subtotal' => $newJumlahDetailPenjualan * $dataDetailPenjualan->harga_satuan,
                'keuntungan' => ($dataDetailPenjualan->keuntungan / $dataDetailPenjualan->jumlah)  * $newJumlahDetailPenjualan,
            ]);
            // update stock
            $this->updateStock($query->id_barang, $query->jumlah, 'add');
            DB::commit();
            return responseJson(true, 'Data berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollback();
            return responseJson(false, 'Data gagal ditambahkan!', $e->getMessage(), 500);
        }
    }
    public function show($id_penjualan, $id)
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
        $validasi = $this->validasiInput($request, 'update');
        if (!$validasi['status']) {
            return responseJson(false, 'validasi error', $validasi['message'], 500);
        }
        $penjualan = $this->findPenjualan($id_penjualan);
        $detailPenjualan = $this->findDetailPenjualan($result->id_barang, ['id_penjualan' => $id_penjualan]);
        $dataDetailPenjualan = $detailPenjualan->first();
        $dataRequestMain = $request->all($this->fillableMainModel);
        $dataMain = [];
        foreach ($dataRequestMain as $key => $value) {
            if ($value != null && $value != '') {
                $dataMain[$key] = $value;
            }
        }
        $dataMain['id_barang'] = $result->id_barang;
        $status = 'add';
        $diff = 0;
        $newJumlahDetailPenjualan = 0;
        if (isset($dataMain['jumlah'])) {
            if ($dataMain['jumlah'] > $result->jumlah) {
                $diff = $dataMain['jumlah'] - $result->jumlah;
                $status = 'add';
            }
            if ($dataMain['jumlah'] < $result->jumlah) {
                $diff = $result->jumlah - $dataMain['jumlah'];
                $status = 'subtract';
            }
            if ($status == 'add' && $diff > 0) {
                $newJumlahDetailPenjualan = $dataDetailPenjualan->jumlah - $diff;
            }
            if ($status == 'subtract' && $diff > 0) {
                $newJumlahDetailPenjualan = $dataDetailPenjualan->jumlah + $diff;
            }
        }
        // return responseJson(false, 'data', ['diff' => $diff, 'status' => $status, 'newJumlahDetailPenjualan' => $newJumlahDetailPenjualan]);
        DB::beginTransaction();
        try {
            if (isset($dataMain['jumlah'])) {
                if ($diff != 0) {
                    // update detail penjualan
                    $detailPenjualan->update([
                        'jumlah' => $newJumlahDetailPenjualan,
                        'subtotal' => $newJumlahDetailPenjualan * $dataDetailPenjualan->harga_satuan,
                        'keuntungan' => ($dataDetailPenjualan->keuntungan / $dataDetailPenjualan->jumlah)  * $newJumlahDetailPenjualan,
                    ]);
                    // update stock
                    $this->updateStock($result->id_barang, $diff, $status);
                }
            }
            // update retur penjualan
            if (count($dataMain) > 0) {
                $query = $findData->update($dataMain);
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
        $detailPenjualan = $this->findDetailPenjualan($result->id_barang, ['id_penjualan' => $id_penjualan]);
        $dataDetailPenjualan = $detailPenjualan->first();
        DB::beginTransaction();
        try {
            $findData->delete();
            $newJumlahDetailPenjualan = $dataDetailPenjualan->jumlah + $result->jumlah;
            $detailPenjualan->update([
                'jumlah' => $newJumlahDetailPenjualan,
                'subtotal' => $newJumlahDetailPenjualan * $dataDetailPenjualan->harga_satuan,
                'keuntungan' => ($dataDetailPenjualan->keuntungan / $dataDetailPenjualan->jumlah)  * $newJumlahDetailPenjualan,
            ]);
            // find retur penjualan
            $returPenjualan = $this->mainModel->where('id_penjualan',$id_penjualan);
            $dataReturPenjualan = $returPenjualan->get();
            if (count($dataReturPenjualan) > 0) {
                $this->findPenjualan($id_penjualan)->update(['retur' => 'false']);
            }
            $this->updateStock($result->id_barang, $result->jumlah, 'subtract');
            DB::commit();
            return responseJson(true, 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollback();
            return responseJson(false, 'Data gagal dihapus!', $e->getMessage(), 500);
        }
    }
}
