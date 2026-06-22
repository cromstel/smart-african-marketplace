<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiSettingsController extends Controller
{
    public function index(): View
    {
        $settings = AiSetting::all()->keyBy('key');

        return view('admin.ai-settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ai_enabled' => 'nullable|boolean',
            'default_title_style' => 'required|in:premium,standard,sale',
            'default_description_style' => 'required|in:detailed,concise,technical,marketing',
            'auto_generate_enabled' => 'nullable|boolean',
        ]);

        AiSetting::set('ai_enabled', $validated['ai_enabled'] ?? 0);
        AiSetting::set('default_title_style', $validated['default_title_style']);
        AiSetting::set('default_description_style', $validated['default_description_style']);
        AiSetting::set('auto_generate_enabled', $validated['auto_generate_enabled'] ?? 0);

        return redirect()->route('admin.ai-settings.index')
            ->with('success', 'AI settings updated successfully!');
    }
}
