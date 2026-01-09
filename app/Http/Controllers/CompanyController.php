<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sortField = $request->get('sortField', 'id');
        $sortOrder = $request->get('sortOrder', 'ascend');
        $query->orderBy($sortField, $sortOrder === 'ascend' ? 'asc' : 'desc');

        $perPage = $request->get('pageSize', 10);
        $companies = $query->paginate($perPage);

        return Inertia::render('Companies/Index', [
            'companies' => CompanyResource::collection($companies),
            'filters' => $request->only(['search', 'sortField', 'sortOrder']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Companies/Create');
    }

    public function store(StoreCompanyRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        Company::create($data);

        return redirect()->route('companies.index')->with('success', 'Company created.');
    }

    public function show(Company $company)
    {
        return new CompanyResource($company);
    }

    public function edit(Company $company)
    {
        return Inertia::render('Companies/Edit', [
            'company' => new CompanyResource($company),
        ]);
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $company->update($data);

        return redirect()->route('companies.index')->with('success', 'Company updated.');
    }

    public function destroy(Company $company)
    {
        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Company deleted.');
    }
}
