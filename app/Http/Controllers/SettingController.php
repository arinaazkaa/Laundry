<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = BusinessSetting::pluck('value', 'key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token', '_method');

        foreach ($data as $key => $value) {
            BusinessSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}