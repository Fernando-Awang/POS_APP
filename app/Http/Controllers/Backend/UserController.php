<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $mainModel;
    private $detailModel;
    private $fillableMainModel;
    private $fillableDetailModel;
    public function __construct()
    {
        $this->mainModel = new \App\Models\User();
        $this->detailModel = new \App\Models\DetailUser();
        $this->fillableMainModel = [
            'username',
            'password',
            'role',
        ];
        $this->fillableDetailModel = [
            'id_user',
            'nama',
            'alamat',
            'telp',
        ];
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
            $validate['username'] = 'required|unique:users';
            $validate['password'] = 'required';
            $validate['role'] = 'required';
            $validate['nama'] = 'required';
            $validate['alamat'] = 'required';
            $validate['telp'] = 'required';
        }
        if ($type == 'update') {
            $validate['username'] = 'unique:users,username,' . $request->id;
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
        // user > main
        $dataRequestMain = $request->all($this->fillableMainModel);
        $dataMain = [];
        foreach ($dataRequestMain as $key => $value) {
            if ($value != null && $value != '') {
                $dataMain[$key] = $value;
            }
        }
        $dataMain['password'] = Hash::make($dataMain['password']);
        // detail_user > detail
        $dataRequestDetail = $request->all($this->fillableDetailModel);
        $dataDetail = [];
        foreach ($dataRequestDetail as $key => $value) {
            if ($value != null && $value != '') {
                $dataDetail[$key] = $value;
            }
        }
        DB::beginTransaction();
        try {
            $dataUser = $this->mainModel->create($dataMain);
            $dataDetail['id_user'] = $dataUser->id;
            $this->detailModel->create($dataDetail);
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
        // exception request
        $request->id = $id;
        $validasi = $this->validasiInput($request, 'update');
        if (!$validasi['status']) {
            return responseJson(false, 'validasi error', $validasi['message'], 500);
        }
        // user > main
        $dataRequestMain = $request->all($this->fillableMainModel);
        $dataMain = [];
        foreach ($dataRequestMain as $key => $value) {
            if ($value != null && $value != '') {
                $dataMain[$key] = $value;
            }
        }
        if (isset($dataMain['password'])) {
            $dataMain['password'] = Hash::make($dataMain['password']);
        }
        // detail_user > detail
        $dataRequestDetail = $request->all($this->fillableDetailModel);
        $dataDetail = [];
        foreach ($dataRequestDetail as $key => $value) {
            if ($value != null && $value != '') {
                $dataDetail[$key] = $value;
            }
        }
        DB::beginTransaction();
        try {
            if (count($dataMain) > 0) {
                $findData->update($dataMain);
            }
            if (count($dataDetail) > 0) {
                $this->getAllDetailModel(['id_user' => $id])->update($dataDetail);
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
            $detail = $this->getAllDetailModel(['id_user' => $id]);
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
