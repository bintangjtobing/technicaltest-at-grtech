<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\EmployeeResource;
use App\Models\Company;
use App\Models\Employee;
use App\Notifications\NewEmployeeNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('company');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sortField = $request->get('sortField', 'company_id');
        $sortOrder = $request->get('sortOrder', 'ascend');
        $query->orderBy($sortField, $sortOrder === 'ascend' ? 'asc' : 'desc');

        $view = $request->get('view', 'flat');
        if ($view === 'grouped') {
            $employees = $query->get();
            $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
                $employees,
                $employees->count(),
                $employees->count() ?: 1,
                1
            );
            $employeesResource = EmployeeResource::collection($paginatedData);
        } else {
            $perPage = $request->get('pageSize', 10);
            $employees = $query->paginate($perPage);
            $employeesResource = EmployeeResource::collection($employees);
        }

        return Inertia::render('Employees/Index', [
            'employees' => $employeesResource,
            'companies' => CompanyResource::collection(Company::all()),
            'filters' => array_merge($request->only(['search', 'sortField', 'sortOrder']), ['view' => $view]),
        ]);
    }

    public function create()
    {
        $companies = Company::all();
        return Inertia::render('Employees/Create', [
            'companies' => CompanyResource::collection($companies),
        ]);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();
        $employee = Employee::create($data);

        $company = Company::find($data['company_id']);
        if ($company && $company->email) {
            $company->notify(new NewEmployeeNotification($employee));
        }

        return redirect()->route('employees.index')->with('success', 'Employee created.');
    }

    public function show(Employee $employee)
    {
        $employee->load('company');
        return new EmployeeResource($employee);
    }

    public function edit(Employee $employee)
    {
        $companies = Company::all();
        return Inertia::render('Employees/Edit', [
            'employee' => new EmployeeResource($employee->load('company')),
            'companies' => CompanyResource::collection($companies),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();
        $employee->update($data);

        return redirect()->route('employees.index')->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted.');
    }
}
