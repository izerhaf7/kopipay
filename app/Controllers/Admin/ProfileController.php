<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Services\AccountService;

class ProfileController extends BaseController
{
    public function index()
    {
        return view('admin/profile', ['title'=>'Profil Saya','subtitle'=>'Kelola informasi akun pribadi','user'=>user()]);
    }

    public function update()
    {
        $id = (int) user_id();
        $u = model(UserModel::class)->find($id);
        if ($u === null) return redirect()->to(site_url('admin/dashboard'))->with('error','Akun tidak ditemukan.');
        $rules = ['email'=>'required|valid_email|is_unique[users.email,id,'.$id.']','username'=>'required|alpha_numeric_punct|min_length[3]|max_length[30]|is_unique[users.username,id,'.$id.']'];
        if (trim((string)$this->request->getPost('password')) !== '') $rules['password'] = 'min_length[8]|max_length[72]';
        if (! $this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        $u->email = $this->request->getPost('email'); $u->username = $this->request->getPost('username');
        if ($this->request->getPost('password')) $u->setPassword((string)$this->request->getPost('password'));
        $file = $this->request->getFile('photo');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            if (! in_array($file->getMimeType(), ['image/jpeg','image/png','image/webp'], true) || $file->getSize() > 2*1024*1024) return redirect()->back()->withInput()->with('error','Foto harus JPG, PNG, atau WEBP dan maksimal 2 MB.');
            $dir = FCPATH.'uploads/users'; if (! is_dir($dir)) mkdir($dir,0755,true); $name=$file->getRandomName(); $file->move($dir,$name); $u->photo=$name;
        }
        model(UserModel::class)->save($u);
        return redirect()->to(site_url('admin/profile'))->with('message','Profil berhasil diperbarui.');
    }
}
