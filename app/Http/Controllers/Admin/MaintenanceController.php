<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MaintenanceService;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    /**
     * The maintenance service instance.
     */
    protected MaintenanceService $maintenanceService;

    /**
     * Create a new controller instance.
     */
    public function __construct(MaintenanceService $maintenanceService)
    {
        $this->maintenanceService = $maintenanceService;

        // Ensure user is authenticated and is an admin
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Show the maintenance mode settings page.
     */
    public function index()
    {
        $isActive = $this->maintenanceService->isActive();
        // Regression: this read config('maintenance') -- the static file
        // only -- so nothing an admin had previously saved ever showed up
        // again after the request that saved it ended. getConfig() layers
        // persisted Setting rows over those same file defaults.
        $config = $this->maintenanceService->getConfig();

        return view('admin.maintenance.index', compact('isActive', 'config'));
    }

    /**
     * Update maintenance mode configuration.
     */
    public function update(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:200',
            'message' => 'nullable|string|max:500',
            'retry' => 'nullable|integer|min:1|max:3600',
            'allowed_ips' => 'nullable|string',
            'allowed_emails' => 'nullable|string',
        ]);

        $config = [];

        if ($request->filled('title')) {
            $config['title'] = $request->title;
        }

        if ($request->filled('message')) {
            $config['message'] = $request->message;
        }

        if ($request->filled('retry')) {
            $config['retry'] = (int) $request->retry;
        }

        $config['allowed_ips'] = $request->filled('allowed_ips')
            ? array_values(array_filter(array_map('trim', explode("\n", $request->allowed_ips))))
            : [];

        $config['allowed_emails'] = $request->filled('allowed_emails')
            ? array_values(array_filter(array_map('trim', explode("\n", $request->allowed_emails))))
            : [];

        // Regression: this passed config('maintenance') (the still-static
        // file, since the config() mutations above never persisted) to
        // updateConfig(), which itself only wrote to Cache::put() /
        // Config::set() -- neither survives past this request. Both layers
        // of the bug are fixed: $config here is built directly from the
        // submitted form, and updateConfig() now writes through Setting.
        $this->maintenanceService->updateConfig($config);

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Maintenance mode configuration has been updated.');
    }

    /**
     * Enable maintenance mode.
     */
    public function enable(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:500',
            'allowed_ips' => 'nullable|string',
            'allowed_emails' => 'nullable|string',
        ]);

        $config = [];

        if ($request->filled('message')) {
            $config['message'] = $request->message;
        }

        if ($request->filled('allowed_ips')) {
            $config['allowed_ips'] = array_values(array_filter(array_map('trim', explode(',', $request->allowed_ips))));
        }

        if ($request->filled('allowed_emails')) {
            $config['allowed_emails'] = array_values(array_filter(array_map('trim', explode(',', $request->allowed_emails))));
        }

        if ($config) {
            $this->maintenanceService->updateConfig($config);
        }

        $this->maintenanceService->enable();

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Maintenance mode has been enabled. Regular visitors will see the maintenance page.');
    }

    /**
     * Disable maintenance mode.
     */
    public function disable()
    {
        $this->maintenanceService->disable();

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Maintenance mode has been disabled. The site is now live.');
    }

    /**
     * Toggle maintenance mode.
     */
    public function toggle()
    {
        $newState = $this->maintenanceService->toggle();

        $message = $newState
            ? 'Maintenance mode has been enabled.'
            : 'Maintenance mode has been disabled.';

        return redirect()->route('admin.maintenance.index')
            ->with('success', $message);
    }

    /**
     * Check the current maintenance status (API endpoint).
     */
    public function status()
    {
        $status = $this->maintenanceService->getStatus();

        return response()->json($status);
    }

    /**
     * Preview the maintenance page (for admins only).
     */
    public function preview()
    {
        // Regression: this called enable(), which durably (see the
        // persistence fix above) turned maintenance mode ON for every real
        // visitor -- and never turned it back off, despite capturing
        // $previousState as though it meant to. A "preview" action must not
        // have a side effect at all; render the exact view a visitor would
        // see instead, admin-only, with no state change.
        $config = $this->maintenanceService->getConfig();

        return response()->view('errors.maintenance', [
            'title' => $config['title'],
            'message' => $config['message'],
            'expected' => $config['expected'],
        ]);
    }
}
