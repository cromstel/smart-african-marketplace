<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoreDomainController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $vendor = $request->user()->vendor;

        if (! $vendor || ! $this->hasEnterpriseAccess($request)) {
            return redirect()->route('vendor.subscription.upgrade')
                ->with('error', 'Custom domain mapping is available on the Enterprise plan only.');
        }

        return view('vendor.store-domain.index', [
            'vendor' => $vendor,
            'subdomain_url' => $this->subdomainUrl($vendor->slug),
            'domain_target' => (string) config('vendor-store.domain_target'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $vendor = $request->user()->vendor;

        if (! $vendor || ! $this->hasEnterpriseAccess($request)) {
            return redirect()->route('vendor.subscription.upgrade')
                ->with('error', 'Custom domain mapping is available on the Enterprise plan only.');
        }

        $validated = $request->validate([
            'custom_domain' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^(?=.{1,253}$)(?!-)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i',
                Rule::unique('vendors', 'custom_domain')->ignore($vendor->id),
            ],
        ]);

        $domain = $this->normalizeDomain($validated['custom_domain'] ?? null);

        if ($domain === null) {
            $vendor->update([
                'custom_domain' => null,
                'custom_domain_verified_at' => null,
            ]);

            return redirect()->route('vendor.store-domain.index')
                ->with('success', 'Custom domain removed.');
        }

        $vendor->update([
            'custom_domain' => $domain,
            'custom_domain_verified_at' => null,
        ]);

        return redirect()->route('vendor.store-domain.index')
            ->with('success', 'Custom domain saved. Please verify DNS before it becomes active.');
    }

    public function verify(Request $request): RedirectResponse
    {
        $vendor = $request->user()->vendor;

        if (! $vendor || ! $this->hasEnterpriseAccess($request)) {
            return redirect()->route('vendor.subscription.upgrade')
                ->with('error', 'Custom domain mapping is available on the Enterprise plan only.');
        }

        if (! $vendor->custom_domain) {
            return redirect()->route('vendor.store-domain.index')
                ->with('error', 'Set a custom domain before verifying.');
        }

        $verified = app()->environment('local', 'testing')
            || $this->domainPointsToConfiguredTarget($vendor->custom_domain);

        if (! $verified) {
            return redirect()->route('vendor.store-domain.index')
                ->with('error', 'DNS not verified yet. Point your domain to the configured target and try again.');
        }

        $vendor->update([
            'custom_domain_verified_at' => now(),
        ]);

        return redirect()->route('vendor.store-domain.index')
            ->with('success', 'Custom domain verified and active.');
    }

    private function hasEnterpriseAccess(Request $request): bool
    {
        $subscription = $request->user()->effectiveSubscription();

        return $subscription
            && $subscription->isActive()
            && $subscription->plan
            && $subscription->plan->slug === 'enterprise';
    }

    private function normalizeDomain(?string $domain): ?string
    {
        if (! is_string($domain) || trim($domain) === '') {
            return null;
        }

        $value = strtolower(trim($domain));
        $value = preg_replace('#^https?://#', '', $value);
        $value = preg_replace('#/.*$#', '', $value);

        return $value !== '' ? $value : null;
    }

    private function subdomainUrl(string $slug): string
    {
        $scheme = (string) config('vendor-store.scheme', 'https');
        $baseDomain = trim((string) config('vendor-store.base_domain', ''), '.');

        return $baseDomain === ''
            ? url('/vendors/'.$slug)
            : $scheme.'://'.$slug.'.'.$baseDomain;
    }

    private function domainPointsToConfiguredTarget(string $domain): bool
    {
        $target = strtolower(trim((string) config('vendor-store.domain_target', ''), '.'));
        if ($target === '' || ! function_exists('dns_get_record')) {
            return false;
        }

        $records = dns_get_record($domain, DNS_CNAME + DNS_A + DNS_AAAA);
        if (! is_array($records) || $records === []) {
            return false;
        }

        foreach ($records as $record) {
            $cname = strtolower(trim((string) ($record['target'] ?? ''), '.'));
            if ($cname !== '' && $cname === $target) {
                return true;
            }
        }

        return false;
    }
}
