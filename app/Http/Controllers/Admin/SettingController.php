<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display the system settings edit form.
     */
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->all();
        
        // Ensure default values if any setting is missing
        $defaults = [
            'sla_days_low' => '5',
            'sla_days_high' => '3',
            'sla_days_critical' => '1',
            'sla_days_assign_low' => '2',
            'sla_days_assign_high' => '1',
            'sla_days_assign_critical' => '0',
        ];

        foreach ($defaults as $key => $val) {
            if (!isset($settings[$key])) {
                $settings[$key] = $val;
            }
        }

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update the system settings in storage.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'sla_days_low' => 'required|integer|min:0',
            'sla_days_high' => 'required|integer|min:0',
            'sla_days_critical' => 'required|integer|min:0',
            'sla_days_assign_low' => 'required|integer|min:0',
            'sla_days_assign_high' => 'required|integer|min:0',
            'sla_days_assign_critical' => 'required|integer|min:0',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'System SLA and Assignment configurations updated successfully.');
    }
}
