<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
class AccountController extends Controller {
    public function index(Request $request) {
        $search=trim((string)$request->query('q',''));
        $users=User::when($search,fn($q)=>$q->where(function($q) use ($search) {
            $q->where('nama','like','%'.$search.'%')->orWhere('email','like','%'.$search.'%');
        }))->orderBy('nama')->paginate(15)->withQueryString();
        return view('admin.accounts',compact('users','search'));
    }
    private function validated(Request $request, ?User $user=null): array {
        return $request->validate([
            'nama'=>['required','string','max:100'],
            'email'=>['required','email','max:100',Rule::unique('users','email')->ignore($user?->user_id,'user_id')],
            'no_hp'=>['nullable','string','max:20'], 'alamat'=>['nullable','string','max:2000'],
            'role'=>['required','in:admin,staff,customer'],
            'password'=>[$user?'nullable':'required','string','min:8','confirmed'],
        ]);
    }
    public function store(Request $request) {
        $data=$this->validated($request);
        $data['password']=Hash::make($data['password']);
        $user=User::create($data);
        $user->sendEmailVerificationNotification();
        return back()->with('success','Akun dibuat. Tautan verifikasi dikirim ke email pemilik akun.');
    }
    public function update(Request $request, User $user) {
        $data=$this->validated($request,$user);
        // Keep the current administrator from accidentally removing their own access.
        if ((int)$user->user_id===(int)auth()->id() && $data['role']!=='admin') {
            return back()->with('error','Anda tidak dapat mengubah peran admin akun sendiri.');
        }
        $emailChanged=$user->email!==$data['email'];
        if (!empty($data['password'])) $data['password']=Hash::make($data['password']);
        else unset($data['password']);
        $user->fill($data);
        if ($emailChanged) $user->email_verified_at=null;
        $user->save();
        if ($emailChanged) $user->sendEmailVerificationNotification();
        app(\App\Services\CustomerUpdateMailer::class)->account($user,['Data profil atau pengaturan akun Anda telah diubah. Jika perubahan ini tidak sesuai, hubungi perusahaan.']);
        return back()->with('success','Akun berhasil diperbarui.');
    }
}
