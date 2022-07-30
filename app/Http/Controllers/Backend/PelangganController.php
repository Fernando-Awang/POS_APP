<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PelangganController extends Controller
{
    private $mainModel;
    private $fillableMainModel;
    public function __construct()
    {
        $this->mainModel = new \App\Models\Pelanggan();
        $this->fillableMainModel = [
            'nama',
            'telp',
            'alamat',
        ];
    }
    private function getData($condition = null)
    {
        $data = $this->mainModel;
        // $data = $data->with('');
        if ($condition != null) {
            $data = $data->where($condition);
        }
        return $data;
    }
    private function mapData($data)
    {
        return collect($data)->map(function ($item) {
            return $item;
        });
    }
    private function validasiInput($request, $type = 'store')
    {
        $validate = [];
        $messages = [];
        $result['status'] = false;
        if ($type == 'store') {
            $validate['nama'] = 'required';
            $validate['telp'] = 'required';
            $validate['alamat'] = 'required';

            $messages['nama.required'] = 'Nama tidak boleh kosong';
            $messages['telp.required'] = 'Telepon tidak boleh kosong';
            $messages['alamat.required'] = 'Alamat tidak boleh kosong';
        }
        if ($type == 'update') {
        }
        if (count($validate) == 0) {
            $result['status'] = true;
            return $result;
        }
        $validator = Validator::make($request->all(), $validate, $messages);
        if ($validator->fails()) {
            $result['message'] = $validator->errors()->all();
            return $result;
        }
        $result['status'] = true;
        return $result;
    }
    // ==================== crud function ======================================================
    public function index()
    {
        $data = $this->getData()->get();
        $data = $this->mapData($data);
        return responseJson(true, 'data list', $data);
    }
    public function store(Request $request)
    {
        $validasi = $this->validasiInput($request);
        if (!$validasi['status']) {
            return responseJson(false, 'validasi error', $validasi['message'], 500);
        }
        // $dataRequest = $request->except('_token');
        $dataRequest = $request->all($this->fillableMainModel);
        $data = [];
        foreach ($dataRequest as $key => $value) {
            if ($value != null && $value != '') {
                $data[$key] = $value;
            }
        }
        DB::beginTransaction();
        try {
            $this->mainModel->create($data);
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
        $findData = $this->getData($condition);
        $getData = $findData->get();
        $result = count($getData);
        if ($result == 0) {
            return responseJson(false, 'Data tidak ditemukan', null, 404);
        }
        return responseJson(true, 'data', $this->mapData($getData)->first());
    }
    public function update(Request $request, $id)
    {
        $condition = ['id' => $id];
        $findData = $this->getData($condition);
        $result = count($findData->get());
        if ($result == 0) {
            return responseJson(false, 'Data tidak ditemukan', null, 404);
        }
        $validasi = $this->validasiInput($request, 'update');
        if (!$validasi['status']) {
            return responseJson(false, 'validasi error', $validasi['message'], 500);
        }
        // $dataRequest = $request->except('_token');
        $dataRequest = $request->all($this->fillableMainModel);
        $data = [];
        foreach ($dataRequest as $key => $value) {
            if ($value != null && $value != '') {
                $data[$key] = $value;
            }
        }
        DB::beginTransaction();
        try {
            $findData->update($data);
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
        $findData = $this->getData($condition);
        $result = count($findData->get());
        if ($result == 0) {
            return responseJson(false, 'Data tidak ditemukan', null, 404);
        }
        DB::beginTransaction();
        try {
            $findData->delete();
            DB::commit();
            return responseJson(true, 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollback();
            return responseJson(false, 'Data gagal dihapus!', $e->getMessage(), 500);
        }
    }
}
