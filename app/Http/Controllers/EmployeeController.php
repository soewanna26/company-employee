<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Laravel\Facades\Image;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $employees = Employee::with('company:id,name')->latest();
        $companies = Company::all(); // Fetch all companies for the filter dropdown

        $keyword = $request->get('keyword');
        $companyId = $request->get('company_id');

        if (!empty($keyword)) {
            $employees = $employees->where(function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%')
                    ->orWhere('phone', 'like', '%' . $keyword . '%')
                    ->orWhereHas('company', function ($query) use ($keyword) {
                        $query->where('name', 'like', '%' . $keyword . '%');
                    });
            });
        }

        if (!empty($companyId)) {
            $employees = $employees->where('company_id', $companyId);
        }

        $employees = $employees->paginate(10);
        return view('employee.list', compact('employees', 'companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::all();
        return view('employee.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|unique:employees,email',
            'company_id' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $employee = new Employee();
            $employee->name = $request->name;
            $employee->email = $request->email;
            $employee->phone = $request->phone;
            $employee->company_id = $request->company_id;

            $destinationPath = storage_path('app/public/employee/');
            $destinationPathThumbnail = storage_path('app/public/employee/thumbnail/');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            if (!File::exists($destinationPathThumbnail)) {
                File::makeDirectory($destinationPathThumbnail, 0755, true);
            }

            if ($request->hasFile('image')) {
                $image = Image::read($request->file('image'));

                $imageName = time() . '-' . $request->file('image')->getClientOriginalName();
                $image->save($destinationPath . $imageName);

                $image->resize(100, 100);
                $image->save($destinationPathThumbnail . $imageName);
                $employee->profile = $imageName;
            }
            $employee->save();

            DB::commit();
            return redirect()->route('employee')->with('success', 'Employee added Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save employee: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to create Employee');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $companies = Company::all();
        return view('employee.edit', compact('companies', 'employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|unique:employees,email,' . $id . ',id',
            'company_id' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $employee->name = $request->name;
            $employee->email = $request->email;
            $employee->phone = $request->phone;
            $employee->company_id = $request->company_id;

            $destinationPath = storage_path('app/public/employee/');
            $destinationPathThumbnail = storage_path('app/public/employee/thumbnail/');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            if (!File::exists($destinationPathThumbnail)) {
                File::makeDirectory($destinationPathThumbnail, 0755, true);
            }

            if ($request->hasFile('image')) {
                // Delete old image if it exists
                if ($employee->profile) {
                    $oldImagePath = storage_path('app/public/employee/' . $employee->profile);
                    $oldThumbnailPath = storage_path('app/public/employee/thumbnail/' . $employee->profile);

                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }

                    if (File::exists($oldThumbnailPath)) {
                        File::delete($oldThumbnailPath);
                    }
                }

                // Process and save new image
                $image = Image::read($request->file('image'));
                $imageName = time() . '-' . $request->file('image')->getClientOriginalName();

                $image->save($destinationPath . $imageName);
                $image->resize(100, 100);
                $image->save($destinationPathThumbnail . $imageName);

                $employee->profile = $imageName;
            }
            $employee->save();

            DB::commit();
            return redirect()->route('employee')->with('success', 'Employee Updated Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save employee: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to Updated Employee');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        DB::beginTransaction();

        try {
            if (!empty($employee->profile)) {
                if ($employee->profile) {
                    $oldImagePath = storage_path('app/public/employee/' . $employee->profile);
                    $oldThumbnailPath = storage_path('app/public/employee/thumbnail/' . $employee->profile);

                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }

                    if (File::exists($oldThumbnailPath)) {
                        File::delete($oldThumbnailPath);
                    }
                }
            }
            $employee->delete();

            DB::commit();
            return redirect()->route('employee')->with('success', 'Employee deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete employee: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete Employee');
        }
    }
}
