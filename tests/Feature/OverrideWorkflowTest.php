<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{User,Booking,RuteHarga,Invoice,BuktiPembayaran};
use App\Notifications\{VerifyAccountEmail,ResetAccountPassword};
use Illuminate\Support\Facades\{Schema,DB,Hash,Notification};
use Illuminate\Database\Schema\Blueprint;

class OverrideWorkflowTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:',
            'app.key'=>'base64:'.base64_encode(str_repeat('x',32)), 'mail.default'=>'array']);
        DB::purge('sqlite');
        Schema::create('users',function(Blueprint $t) {
            $t->id('user_id');$t->string('nama',100);$t->string('email',100)->unique();
            $t->string('password');$t->string('no_hp',20)->nullable();$t->text('alamat')->nullable();
            $t->string('role')->default('customer');
        });
        foreach(['2026_09_04_045000_create_company_profiles_table.php','2026_09_04_050440_add_logo_to_company_profiles_table.php',
            '2026_09_04_060000_create_booking_workflow_tables.php','2014_10_12_100000_create_password_reset_tokens_table.php',
            '2026_10_14_000001_extend_customer_workflow.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        $this->withoutVite();
        Notification::fake();
    }
    private function user(string $role='customer',bool $verified=true): User {
        $user=User::create(['nama'=>'User '.$role,'email'=>uniqid().'@example.com',
            'password'=>Hash::make('password123'),'role'=>$role]);
        if($verified) {$user->email_verified_at=now();$user->save();}
        return $user;
    }
    private function rute(): RuteHarga {
        $r=RuteHarga::create(['pelabuhan_asal'=>'Surabaya','pelabuhan_tujuan'=>'Sorong','harga_dasar'=>5000000]);
        $r->tarifs()->create(['ukuran_feet'=>20,'harga'=>5000000]);
        $r->tarifs()->create(['ukuran_feet'=>40,'harga'=>8000000]);return $r;
    }
    private function payload(RuteHarga $r): array {
        return ['nama_customer'=>'Nama Orang Lain','email_customer'=>'wakil@example.com','no_hp_customer'=>'08123456789',
            'rute_id'=>$r->rute_id,'tanggal_pengiriman'=>today()->addDays(5)->toDateString(),
            'jumlah_container'=>3,'ukuran_container'=>40,
            'barang'=>[['nama_barang'=>'Produk','kategori_barang'=>'Dry','qty'=>1,'berat_kg'=>100,'keterangan'=>'']]];
    }
    private function booking(User $u): Booking {
        $r=$this->rute();$this->actingAs($u)->post('/booking',$this->payload($r))->assertRedirect('/booking/sukses');
        return Booking::latest('booking_id')->firstOrFail();
    }
    private function offer(User $admin,Booking $b): void {
        $this->actingAs($admin)->post(route('admin.booking.offer',$b),[
            'harga_final'=>21000000,'estimasi_waktu'=>7,
            'tanggal_pengiriman_usulan'=>today()->addDays(8)->toDateString(),
        ])->assertRedirect();$b->refresh();
    }
    public function test_container_price_uses_route_size_quantity_and_other_customer_name(): void {
        $u=$this->user();$b=$this->booking($u);
        $this->assertEquals(24000000,$b->harga_estimasi);
        $this->assertEquals(8000000,$b->harga_satuan_container);
        $this->assertEquals($u->user_id,$b->user_id);
        $this->assertSame('Nama Orang Lain',$b->nama_customer);
    }
    public function test_category_does_not_change_price(): void {
        $u=$this->user();$r=$this->rute();
        foreach(['Dry','Reefer','Liquid'] as $category) {
            $p=$this->payload($r);$p['barang'][0]['kategori_barang']=$category;
            $this->actingAs($u)->post('/booking',$p)->assertRedirect('/booking/sukses');
            $this->assertEquals(24000000,Booking::latest('booking_id')->first()->harga_estimasi);
        }
    }
    public function test_past_date_invalid_category_and_missing_tariff_are_rejected(): void {
        $u=$this->user();$r=$this->rute();$p=$this->payload($r);
        $p['tanggal_pengiriman']=today()->subDay()->toDateString();
        $this->actingAs($u)->post('/booking',$p)->assertSessionHasErrors('tanggal_pengiriman');
        $p=$this->payload($r);$p['barang'][0]['kategori_barang']='Other';
        $this->post('/booking',$p)->assertSessionHasErrors('barang.0.kategori_barang');
        $r->tarifs()->where('ukuran_feet',40)->delete();
        $this->post('/booking',$this->payload($r))->assertSessionHasErrors('ukuran_container');
        $this->assertSame(0,Booking::count());
    }
    public function test_registration_requires_email_verification(): void {
        $this->post('/register',['nama'=>'Customer Baru','email'=>'new@example.com',
            'password'=>'password123','password_confirmation'=>'password123'])->assertRedirect('/email/verify');
        $u=User::where('email','new@example.com')->firstOrFail();
        $this->assertNull($u->email_verified_at);
        Notification::assertSentTo($u,VerifyAccountEmail::class);
        $this->get('/booking')->assertRedirect('/email/verify');
        $url=\Illuminate\Support\Facades\URL::temporarySignedRoute('verification.verify',now()->addMinutes(30),[
            'id'=>$u->user_id,'hash'=>sha1($u->email)]);
        $this->get($url)->assertRedirect('/home');
        $this->assertNotNull($u->fresh()->email_verified_at);
    }
    public function test_total_offer_and_proposed_date_are_confirmed_together(): void {
        $u=$this->user();$b=$this->booking($u);$admin=$this->user('admin');$requested=$b->tanggal_pengiriman->toDateString();
        $this->offer($admin,$b);
        $this->assertSame($requested,$b->tanggal_pengiriman->toDateString());
        $this->actingAs($u)->post(route('booking.confirm-offer',$b),['keputusan'=>'setuju','versi_penawaran'=>1])->assertRedirect();
        $b->refresh();$this->assertSame(today()->addDays(8)->toDateString(),$b->tanggal_pengiriman->toDateString());
        $this->assertEquals(21000000,$b->latestInvoice->subtotal);
        $this->post(route('booking.confirm-offer',$b),['keputusan'=>'setuju','versi_penawaran'=>1])->assertStatus(409);
        $this->assertSame(1,Invoice::count());
    }
    public function test_rejection_note_and_stale_offer_protection(): void {
        $u=$this->user();$b=$this->booking($u);$a=$this->user('admin');$this->offer($a,$b);$this->offer($a,$b);
        $this->actingAs($u)->post(route('booking.confirm-offer',$b),['keputusan'=>'setuju','versi_penawaran'=>1])->assertStatus(409);
        $this->post(route('booking.confirm-offer',$b),['keputusan'=>'tolak','versi_penawaran'=>2,'catatan_penolakan'=>'Mohon tanggal lain.'])->assertRedirect();
        $this->assertSame('Mohon tanggal lain.',$b->fresh()->catatan_penolakan);
        $this->assertSame(0,Invoice::count());
    }
    public function test_admin_requires_numeric_days_and_future_schedule(): void {
        $b=$this->booking($this->user());$this->actingAs($this->user('admin'))->post(route('admin.booking.offer',$b),[
            'harga_final'=>21000000,'estimasi_waktu'=>'7-10 hari','tanggal_pengiriman_usulan'=>today()->subDay()->toDateString()
        ])->assertSessionHasErrors(['estimasi_waktu','tanggal_pengiriman_usulan']);
    }
    public function test_payment_status_only_becomes_paid_when_verified_total_is_sufficient(): void {
        $u=$this->user();$b=$this->booking($u);$a=$this->user('admin');$this->offer($a,$b);
        $this->actingAs($u)->post(route('booking.confirm-offer',$b),['keputusan'=>'setuju','versi_penawaran'=>1]);
        $invoice=$b->fresh()->latestInvoice;
        $p1=BuktiPembayaran::create(['invoice_id'=>$invoice->invoice_id,'jumlah_bayar'=>1000000,'file_bukti'=>'dummy.png','tanggal_upload'=>now(),'status_konfirmasi'=>'menunggu']);
        $p2=BuktiPembayaran::create(['invoice_id'=>$invoice->invoice_id,'jumlah_bayar'=>$invoice->total_bayar-1000000,'file_bukti'=>'dummy2.png','tanggal_upload'=>now(),'status_konfirmasi'=>'menunggu']);
        $this->actingAs($a)->post(route('admin.payment.verify',$p1),['status_konfirmasi'=>'dikonfirmasi'])->assertRedirect();
        $this->assertSame('belum_lunas',$invoice->fresh()->status_bayar);
        $this->assertNull($invoice->fresh()->tanggal_lunas);
        $this->post(route('admin.payment.verify',$p2),['status_konfirmasi'=>'dikonfirmasi'])->assertRedirect();
        $this->assertSame('lunas',$invoice->fresh()->status_bayar);
        $this->assertNotNull($invoice->fresh()->tanggal_lunas);
        $this->post(route('admin.payment.verify',$p2),['status_konfirmasi'=>'dikonfirmasi'])->assertStatus(409);
    }
    public function test_customer_and_staff_cannot_manage_accounts(): void {
        $this->actingAs($this->user())->get(route('admin.accounts.index'))->assertForbidden();
        $this->actingAs($this->user('staff'))->get(route('admin.accounts.index'))->assertForbidden();
        $this->actingAs($this->user('admin'))->get(route('admin.accounts.index'))->assertOk();
    }
    public function test_profile_changes_require_current_password_and_new_email_verification(): void {
        $u=$this->user();$this->actingAs($u)->put(route('profile.update'),['nama'=>'Baru','email'=>'changed@example.com','current_password'=>'wrong'])->assertSessionHasErrors('current_password');
        $this->put(route('profile.update'),['nama'=>'Baru','email'=>'changed@example.com','current_password'=>'password123'])->assertRedirect('/email/verify');
        $this->assertNull($u->fresh()->email_verified_at);
        Notification::assertSentTo($u,VerifyAccountEmail::class);
    }
    public function test_forgot_password_reset_link_works_once(): void {
        $u=$this->user();$this->post(route('password.email'),['email'=>$u->email])->assertSessionHas('status');
        Notification::assertSentTo($u,ResetAccountPassword::class);
        $notification=Notification::sent($u,ResetAccountPassword::class)->first();
        $payload=['email'=>$u->email,'token'=>$notification->token,'password'=>'newpassword123','password_confirmation'=>'newpassword123'];
        $this->post(route('password.update'),$payload)->assertRedirect('/home');
        $this->assertTrue(Hash::check('newpassword123',$u->fresh()->password));
        auth()->logout();
        $this->post(route('password.update'),$payload)->assertSessionHasErrors('email');
    }
    public function test_public_tracking_does_not_expose_customer_identity(): void {
        $b=$this->booking($this->user());auth()->logout();
        $this->get(route('booking.show',$b->kode_booking))->assertOk()->assertDontSee('Nama Orang Lain')->assertDontSee('wakil@example.com');
    }
    public function test_invoice_signature_is_only_present_when_paid(): void {
        $u=$this->user();$b=$this->booking($u);$a=$this->user('admin');$this->offer($a,$b);
        $this->actingAs($u)->post(route('booking.confirm-offer',$b),['keputusan'=>'setuju','versi_penawaran'=>1]);
        $invoice=$b->fresh()->latestInvoice;$b->refresh();
        $html=view('pdf.invoice',compact('invoice','b'))->with('booking',$b)->render();
        $this->assertStringNotContainsString('signature-dummy.jpg',$html);
        $invoice->update(['status_bayar'=>'lunas','tanggal_lunas'=>today()]);
        $html=view('pdf.invoice',['invoice'=>$invoice,'booking'=>$b])->render();
        $this->assertStringContainsString('signature-dummy.jpg',$html);
        $this->assertStringContainsString('Tanggal Pelunasan',$html);
    }
    public function test_updated_pages_render_and_invoice_pdf_can_be_generated(): void {
        config(['contact.whatsapp'=>'6281234567890']);
        $u=$this->user();$b=$this->booking($u);$a=$this->user('admin');
        $this->get('/')->assertOk()->assertSee('Hubungi via WhatsApp')->assertSee('Testimoni Pelanggan');
        $form=$this->get('/booking')->assertOk()->assertSee('Reefer')->assertSee('40 feet');
        if($path=getenv('OVERRIDE_QA_HTML')) file_put_contents($path,$form->getContent());
        $this->get('/profil')->assertOk();
        $this->actingAs($a)->get(route('admin.rute.index'))->assertOk();
        $this->get(route('admin.booking.show',$b))->assertOk();
        $this->offer($a,$b);
        $this->actingAs($u)->get(route('booking.show',$b->kode_booking))->assertOk()->assertSee('Catatan jika menolak');
        $this->post(route('booking.confirm-offer',$b),['keputusan'=>'setuju','versi_penawaran'=>1]);
        $invoice=$b->fresh()->latestInvoice;
        $invoice->update(['status_bayar'=>'lunas','tanggal_lunas'=>today()]);
        $response=$this->get(route('invoice.pdf',$invoice))->assertOk()->assertHeader('Content-Type','application/pdf');
        $this->assertStringStartsWith('%PDF-',$response->getContent());
        if ($path=getenv('OVERRIDE_QA_PDF')) file_put_contents($path,$response->getContent());
    }
    public function test_admin_tariff_updates_preserve_existing_booking_price(): void {
        $u=$this->user();$b=$this->booking($u);$a=$this->user('admin');
        $this->actingAs($a)->post(route('admin.rute.store'),[
            'pelabuhan_asal'=>'Surabaya','pelabuhan_tujuan'=>'Sorong','harga_20'=>6000000,'harga_40'=>9000000
        ])->assertRedirect();
        $this->assertEquals(24000000,$b->fresh()->harga_estimasi);
        $r=RuteHarga::first();$this->actingAs($u)->post('/booking',$this->payload($r))->assertRedirect('/booking/sukses');
        $this->assertEquals(27000000,Booking::latest('booking_id')->first()->harga_estimasi);
    }
    public function test_admin_can_create_edit_accounts_and_cannot_demote_self(): void {
        $a=$this->user('admin');$this->actingAs($a)->post(route('admin.accounts.store'),[
            'nama'=>'Staff Baru','email'=>'staffnew@example.com','role'=>'staff','password'=>'password123','password_confirmation'=>'password123'
        ])->assertRedirect();
        $u=User::where('email','staffnew@example.com')->firstOrFail();
        $this->assertSame('staff',$u->role);Notification::assertSentTo($u,VerifyAccountEmail::class);
        $this->put(route('admin.accounts.update',$u),['nama'=>'Customer Baru','email'=>$u->email,'role'=>'customer'])->assertRedirect();
        $this->assertSame('customer',$u->fresh()->role);
        $this->put(route('admin.accounts.update',$a),['nama'=>$a->nama,'email'=>$a->email,'role'=>'customer'])->assertSessionHas('error');
        $this->assertSame('admin',$a->fresh()->role);
    }
    public function test_customer_is_notified_for_each_staff_booking_update(): void {
        $u=$this->user();$b=$this->booking($u);$staff=$this->user('staff');
        $this->offer($staff,$b);
        Notification::assertSentOnDemand(\App\Notifications\CustomerUpdate::class,function($n,$channels,$notifiable) use($u,$b) {
            return isset($notifiable->routes['mail'][$u->email]) && $n->snapshot['code']===$b->kode_booking
                && in_array('Usulan tanggal pengiriman: '.today()->addDays(8)->toDateString(),$n->details,true);
        });
        $this->actingAs($staff)->post(route('admin.booking.operational',$b),['no_container'=>'CONT-123','etd'=>today()->addDays(8)->toDateString()])->assertRedirect();
        $this->post(route('admin.booking.progress',$b),['status_booking'=>'dalam_pengiriman','keterangan'=>'Kapal telah berangkat.'])->assertRedirect();
        Notification::assertSentOnDemandTimes(\App\Notifications\CustomerUpdate::class,3);
    }
    public function test_payment_and_invoice_updates_send_customer_notifications(): void {
        $u=$this->user();$b=$this->booking($u);$a=$this->user('admin');
        $b->update(['harga_final'=>1000000,'status_harga'=>'dikonfirmasi_customer']);
        $this->actingAs($a)->post(route('admin.booking.invoice',$b),['tanggal_invoice'=>today()->toDateString(),'ppn_persen'=>0,'terms'=>'CASH'])->assertRedirect();
        $invoice=$b->fresh()->latestInvoice;
        $proof=BuktiPembayaran::create(['invoice_id'=>$invoice->invoice_id,'jumlah_bayar'=>1000000,'file_bukti'=>'dummy.jpg','tanggal_upload'=>now(),'status_konfirmasi'=>'menunggu']);
        $this->post(route('admin.payment.verify',$proof),['status_konfirmasi'=>'dikonfirmasi','catatan_admin'=>'Dana diterima.'])->assertRedirect();
        Notification::assertSentOnDemand(\App\Notifications\CustomerUpdate::class,fn($n)=>$n->title==='Hasil verifikasi pembayaran' && in_array('Status invoice: Lunas',$n->details,true));
        Notification::assertSentOnDemandTimes(\App\Notifications\CustomerUpdate::class,2);
    }
    public function test_smtp_failure_preserves_update_and_warns_admin(): void {
        $u=$this->user();$b=$this->booking($u);$a=$this->user('admin');
        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('SMTP unavailable'));
        $this->actingAs($a)->post(route('admin.booking.offer',$b),['harga_final'=>20000000,'estimasi_waktu'=>7,
            'tanggal_pengiriman_usulan'=>today()->addDays(8)->toDateString()])->assertRedirect()->assertSessionHas('email_warning');
        $this->assertEquals(20000000,$b->fresh()->harga_final);
        $this->assertSame('sudah_ditawarkan',$b->fresh()->status_harga);
    }
    public function test_failed_admin_update_sends_no_notification(): void {
        $b=$this->booking($this->user());$this->actingAs($this->user('admin'))->post(route('admin.booking.offer',$b),[
            'harga_final'=>0,'estimasi_waktu'=>'abc','tanggal_pengiriman_usulan'=>'invalid'
        ])->assertSessionHasErrors();
        Notification::assertNothingSent();
    }
    public function test_whatsapp_is_hidden_from_admin_and_staff_and_visible_to_customer(): void {
        config(['contact.whatsapp'=>'6281234567890']);
        $u=$this->user();$b=$this->booking($u);
        $this->get('/')->assertSee('wa.me/6281234567890');
        foreach(['admin','staff'] as $role) {
            $this->actingAs($this->user($role))->get('/')->assertDontSee('wa.me/');
            $this->get(route('admin.dashboard'))->assertDontSee('wa.me/');
            $this->get(route('admin.booking.show',$b))->assertDontSee('wa.me/');
            $this->get('/profil')->assertDontSee('wa.me/');
        }
    }
    public function test_account_update_notifies_customer_without_exposing_password(): void {
        $a=$this->user('admin');$u=$this->user();
        $this->actingAs($a)->put(route('admin.accounts.update',$u),['nama'=>'Nama Baru','email'=>$u->email,'role'=>'customer',
            'password'=>'newsecret123','password_confirmation'=>'newsecret123'])->assertRedirect();
        Notification::assertSentOnDemand(\App\Notifications\CustomerUpdate::class,function($n) {
            return $n->title==='Data akun Anda diperbarui oleh admin' && !str_contains(json_encode($n->details),'newsecret123');
        });
    }
    public function test_responsive_pages_and_notification_email_render(): void {
        config(['contact.whatsapp'=>'6281234567890']);
        $u=$this->user();$b=$this->booking($u);$a=$this->user('admin');
        $directory=getenv('OVERRIDE_QA_PAGES');
        $save=function($name,$response) use($directory) {
            $response->assertOk();
            if($directory) { if(!is_dir($directory)) mkdir($directory,0777,true);file_put_contents($directory.'/'.$name.'.html',$response->getContent()); }
        };
        foreach(['customer-home'=>'/','tentang'=>'/tentang','booking'=>'/booking','history'=>'/booking/riwayat','profile'=>'/profil'] as $name=>$url) $save($name,$this->get($url));
        $this->offer($a,$b);$this->actingAs($u);$save('booking-offer',$this->get(route('booking.show',$b->kode_booking)));
        $this->post(route('booking.confirm-offer',$b),['keputusan'=>'setuju','versi_penawaran'=>1]);
        $invoice=$b->fresh()->latestInvoice;$invoice->update(['status_bayar'=>'lunas','tanggal_lunas'=>today()]);
        $save('booking-paid',$this->get(route('booking.show',$b->kode_booking)));
        $this->actingAs($a);
        foreach(['admin-dashboard'=>route('admin.dashboard'),'admin-booking'=>route('admin.booking.show',$b),'admin-routes'=>route('admin.rute.index'),'admin-accounts'=>route('admin.accounts.index')] as $name=>$url) $save($name,$this->get($url));
        auth()->logout();
        foreach(['guest-home'=>'/','login'=>'/login','register'=>'/register','forgot-password'=>'/password/reset'] as $name=>$url) $save($name,$this->get($url));
        $notification=Notification::sent(new \Illuminate\Notifications\AnonymousNotifiable,\App\Notifications\CustomerUpdate::class)->first();
        $html=$notification->toMail(new \Illuminate\Notifications\AnonymousNotifiable)->render();
        $this->assertStringContainsString($b->kode_booking,(string)$html);
        if($directory) file_put_contents($directory.'/email-update.html',(string)$html);
    }
}
