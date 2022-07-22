<?php

namespace App\Http\Controllers\Example;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BaseExampleController extends Controller
{
    private $mainModel;
    private $detailModel;
    private $fillableMainModel;
    private $fillableDetailModel;
    public function __construct()
    {
        $this->mainModel = new \App\Models\User();
        $this->detailModel = new \App\Models\DetailUser();
        $this->fillableMainModel = [];
        $this->fillableDetailModel = [];
    }
    private function getAllMainModel()
    {
        return $this->mainModel->with('detail_user');
    }
    private function getOneMainModel($condition)
    {
        return $this->mainModel->where($condition)->with('detail_user');
    }
    private function getAllDetailModel($condition)
    {
        return $this->detailModel->where($condition);
    }
    private function validasiInput($request, $type = 'store')
    {
        $validate = [];
        $result['status'] = false;
        if ($type == 'store') {
            $validate['input'] = 'required';
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
        $data = $request->except('_token');
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
        $validasi = $this->validasiInput($request, 'update');
        if (!$validasi['status']) {
            return responseJson(false, 'validasi error', $validasi['message'], 500);
        }
        $data = $request->except('_token');
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
        $findData = $this->getOneMainModel($condition);
        $result = $findData->first();
        if (!isset($result->id)) {
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
