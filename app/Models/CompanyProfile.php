<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $primaryKey = 'profile_id';

    protected $fillable = [
        'nama_perusahaan', 'logo', 'visi', 'misi', 'sejarah', 'alamat', 'email', 'telepon',
    ];
}
