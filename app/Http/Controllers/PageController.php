<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;

class PageController extends Controller
{
    public function home()
    {
        $companyProfile = CompanyProfile::first();

        return view('pages.home', compact('companyProfile'));
    }

    public function tentang()
    {
        $companyProfile = CompanyProfile::first();

        return view('pages.tentang', compact('companyProfile'));
    }
}
