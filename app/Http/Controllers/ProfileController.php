<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
class ProfileController extends Controller {
    public function edit() { return view('profile.edit',['user'=>auth()->user()]); }
    public function update(Request $request) {
        $user=$request->user();
        $data=$request->validate([
            'nama'=>['required','string','max:100'],
            'email'=>['required','email','max:100',Rule::unique('users','email')->ignore($user->user_id,'user_id')],
            'no_hp'=>['nullable','string','max:20'], 'alamat'=>['nullable','string','max:2000'],
            'current_password'=>['required','current_password:web'],
            'password'=>['nullable','string','min:8','confirmed'],
        ]);
        $emailChanged=$user->email!==$data['email'];
        $user->fill(collect($data)->only(['nama','email','no_hp','alamat'])->all());
        if (!empty($data['password'])) $user->password=Hash::make($data['password']);
        if ($emailChanged) $user->email_verified_at=null;
        $user->save();
        if (!empty($data['password'])) {
            auth()->logoutOtherDevices($data['password']);
        }
        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
            return redirect()->route('verification.notice')->with('success','Profil disimpan. Verifikasi alamat email baru Anda.');
        }
        return back()->with('success','Profil berhasil diperbarui.');
    }
}
