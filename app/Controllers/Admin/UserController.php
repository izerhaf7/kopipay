<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Entities\User;
use App\Models\UserModel;
use App\Services\AccountService;
use App\Models\ActivityLogModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class UserController extends BaseController
{
    public function index()
    {
        $q = trim((string) $this->request->getGet('q'));
        $sort = (string) $this->request->getGet('sort');
        $dir = strtoupper((string) $this->request->getGet('dir')) === 'DESC' ? 'DESC' : 'ASC';
        $map = ['email'=>'users.email','username'=>'users.username','active'=>'users.active','created'=>'users.created_at'];
        $perPage = max(5, min(50, (int) ($this->request->getGet('per_page') ?: 10)));
        $b = db_connect()->table('users')
            ->select('users.*, GROUP_CONCAT(auth_groups.name SEPARATOR ", ") AS role_name, employees.full_name')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'left')
            ->join('employees', 'employees.user_id = users.id', 'left')
            ->where('users.deleted_at', null)->groupBy('users.id');
        if ($q !== '') $b->groupStart()->like('users.email',$q)->orLike('users.username',$q)->orLike('employees.full_name',$q)->groupEnd();
        $rows = $b->orderBy($map[$sort] ?? 'users.username', $dir)->get($perPage, max(0, ((int)($this->request->getGet('page_users') ?: 1)-1)*$perPage))->getResultArray();
        $total = db_connect()->table('users')->where('deleted_at', null)->countAllResults();
        return view('admin/users/index', ['title'=>'Manajemen User','subtitle'=>'Kelola akun login dan hak akses','users'=>$rows,'total'=>$total,'page'=>(int)($this->request->getGet('page_users') ?: 1),'perPage'=>$perPage,'q'=>$q,'sort'=>$sort,'dir'=>$dir]);
    }

    public function new() { return view('admin/users/form', ['title'=>'Tambah User','subtitle'=>'Buat akun login baru','user'=>null,'groups'=>db_connect()->table('auth_groups')->orderBy('name')->get()->getResultArray()]); }

    public function create()
    {
        $this->validate(['email'=>'required|valid_email|is_unique[users.email]','username'=>'required|alpha_numeric_punct|min_length[3]|max_length[30]|is_unique[users.username]','password'=>'required|min_length[8]','group_id'=>'required|is_natural_no_zero']);
        if ($this->validator->hasErrors()) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        $u = new User(['email'=>$this->request->getPost('email'),'username'=>$this->request->getPost('username'),'password'=>$this->request->getPost('password')]); $u->activate();
        $id = (int) model(UserModel::class)->insert($u, true); $this->setGroup($id, (int)$this->request->getPost('group_id'));
        model(ActivityLogModel::class)->write((int)user_id(),'user.create','user',$id,'User '.$u->username.' dibuat');
        return redirect()->to(site_url('admin/users'))->with('message','User berhasil dibuat.');
    }

    public function edit(int $id)
    {
        $u = model(UserModel::class)->find($id); if (!$u) throw PageNotFoundException::forPageNotFound();
        $group = db_connect()->table('auth_groups_users')->where('user_id',$id)->get()->getRowArray();
        return view('admin/users/form',['title'=>'Edit User','subtitle'=>'Perbarui akun dan role','user'=>$u,'groupId'=>$group['group_id'] ?? null,'groups'=>db_connect()->table('auth_groups')->orderBy('name')->get()->getResultArray()]);
    }

    public function update(int $id)
    {
        $u=model(UserModel::class)->find($id); if(!$u) throw PageNotFoundException::forPageNotFound();
        $rules=['email'=>'required|valid_email|is_unique[users.email,id,'.$id.']','username'=>'required|alpha_numeric_punct|min_length[3]|max_length[30]|is_unique[users.username,id,'.$id.']','group_id'=>'required|is_natural_no_zero'];
        if(trim((string)$this->request->getPost('password'))!=='') $rules['password']='min_length[8]';
        if(!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        $u->email=$this->request->getPost('email'); $u->username=$this->request->getPost('username'); if($this->request->getPost('password')) $u->setPassword($this->request->getPost('password')); model(UserModel::class)->save($u); $this->setGroup($id,(int)$this->request->getPost('group_id'));
        return redirect()->to(site_url('admin/users'))->with('message','User berhasil diperbarui.');
    }

    public function reset(int $id)
    {
        if ($id === (int)user_id()) return redirect()->to(site_url('admin/users'))->with('error','Akun yang sedang digunakan tidak dapat direset dari sini.');
        $u=model(UserModel::class)->find($id); if(!$u) throw PageNotFoundException::forPageNotFound(); $pw=(new AccountService())->randomPassword(); $u->setPassword($pw); $u->forcePasswordReset(); model(UserModel::class)->save($u);
        return redirect()->to(site_url('admin/users'))->with('message','Password sementara user '.$u->username.': '.$pw);
    }

    public function delete(int $id) { if($id===(int)user_id()) return redirect()->to(site_url('admin/users'))->with('error','Akun sendiri tidak dapat dihapus.'); model(UserModel::class)->delete($id); return redirect()->to(site_url('admin/users'))->with('message','User dihapus.'); }
    private function setGroup(int $uid,int $gid): void { $db=db_connect(); $db->table('auth_groups_users')->where('user_id',$uid)->delete(); $db->table('auth_groups_users')->insert(['user_id'=>$uid,'group_id'=>$gid,'created_at'=>date('Y-m-d H:i:s')]); }
}
