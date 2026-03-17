<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('domains')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return inertia('central/tenants/Index', compact('tenants'));
    }

    public function create()
    {
        return inertia('central/tenants/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['required', 'string', 'max:255', 'unique:tenants,id', 'regex:/^[a-z0-9\-_]+$/'],
            'domain' => ['required', 'string', 'max:255', 'unique:domains,domain'],
        ]);

        $tenant = Tenant::create(['id' => $data['id']]);

        $tenant->domains()->create(['domain' => $data['domain']]);

        return redirect()
            ->route('central.tenants.index')
            ->with('success', 'Tenant created successfully.');
    }

    public function show(Tenant $tenant)
    {
        $tenant->load('domains');

        return inertia('central/tenants/Show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        $tenant->load('domains');

        return inertia('central/tenants/Edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $tenant->load('domains');

        $currentDomain = $tenant->domains->first()?->domain;

        $data = $request->validate([
            'domain' => [
                'required',
                'string',
                'max:255',
                Rule::unique('domains', 'domain')->ignore($currentDomain, 'domain'),
            ],
        ]);

        if ($data['domain'] !== $currentDomain) {
            $tenant->domains()->delete();
            $tenant->domains()->create(['domain' => $data['domain']]);
        }

        return redirect()
            ->route('central.tenants.index')
            ->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()
            ->route('central.tenants.index')
            ->with('success', 'Tenant deleted successfully.');
    }
}
