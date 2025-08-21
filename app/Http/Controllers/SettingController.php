<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $keys = [
            'company.name',
            'company.logo_url',
            'notifications.email_enabled',
            'notifications.realtime_enabled',
        ];

        $settings = collect($keys)->mapWithKeys(fn($k) => [$k => Setting::get($k)]);
        return view('settings.index', ['settings' => $settings]);
    }

    public function update(Request $r)
    {
        $data = $r->validate([
            'company.name' => 'nullable|string|max:255',
            'company.logo_url' => 'nullable|string|max:255',
            'notifications.email_enabled' => 'nullable|boolean',
            'notifications.realtime_enabled' => 'nullable|boolean',
        ]);

        foreach ($data as $k => $v) {
            if (in_array($k, ['notifications.email_enabled','notifications.realtime_enabled'])) {
                $v = (bool)$v;
            }
            Setting::put($k, is_bool($v) ? ($v ? '1' : '0') : $v);
        }

        return back()->with('success','Paramètres enregistrés.');
    }
}
