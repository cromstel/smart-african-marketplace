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
        $config = config('maintenance');

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

        // Update config with values from the form
        if ($request->has('title') && $request->title) {
            config(['maintenance.title' => $request->title]);
        }

        if ($request->has('message') && $request->message) {
            config(['maintenance.message' => $request->message]);
        }

        if ($request->has('retry') && $request->retry) {
            config(['maintenance.retry' => (int) $request->retry]);
        }

        if ($request->has('allowed_ips') && $request->allowed_ips) {
            $ips = array_filter(array_map('trim', explode("\n", $request->allowed_ips)));
            config(['maintenance.allowed_ips' => array_values($ips)]);
        } else {
            config(['maintenance.allowed_ips' => []]);
        }

        if ($request->has('allowed_emails') && $request->allowed_emails) {
            $emails = array_filter(array_map('trim', explode("\n", $request->allowed_emails)));
            config(['maintenance.allowed_emails' => array_values($emails)]);
        } else {
            config(['maintenance.allowed_emails' => []]);
        }

        // Store configuration in cache
        $this->maintenanceService->updateConfig(config('maintenance'));

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

        // Update config with optional custom values
        if ($request->has('message') && $request->message) {
            config(['maintenance.message' => $request->message]);
        }

        if ($request->has('allowed_ips') && $request->allowed_ips) {
            $ips = array_filter(array_map('trim', explode(',', $request->allowed_ips)));
            config(['maintenance.allowed_ips' => $ips]);
        }

        if ($request->has('allowed_emails') && $request->allowed_emails) {
            $emails = array_filter(array_map('trim', explode(',', $request->allowed_emails)));
            config(['maintenance.allowed_emails' => $emails]);
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
        // Temporarily enable maintenance mode for preview
        $previousState = $this->maintenanceService->isActive();

        // Force enable for preview
        $this->maintenanceService->enable();

        // Note: In a real implementation, this would render the maintenance view
        // For now, we'll just redirect back with a message

        return redirect()->route('admin.maintenance.index')
            ->with('info', 'Maintenance preview mode - site is now showing maintenance page to all visitors except admins.');
    }
}
