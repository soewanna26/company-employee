<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Models\Company;
use App\Models\Employee;
use Exception;
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
    public function index()
    {
        $employees = Employee::with('company')->orderBy('created_at', 'DESC')->paginate(10);
        return ResponseHelper::success(EmployeeResource::collection($employees), "Employee retrieved successfully");;
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
            return ResponseHelper::validationError('Validation error', $validator->errors());
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
            return ResponseHelper::success(new EmployeeResource($employee), "Employee created successfully");
        } catch (Exception $e) {
            DB::rollback();
            return ResponseHelper::internalServerError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $employee = Company::with('company')->findOrFail($id);
            return ResponseHelper::success(new EmployeeResource($employee), "Employee retrieved successfully");
        } catch (Exception $e) {
            return ResponseHelper::notFound("Employee not found");
        }
    }

    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function employeeUpdate(Request $request, $id)
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
            return ResponseHelper::success(new EmployeeResource($employee), "Employee created successfully");
        } catch (Exception $e) {
            DB::rollback();
            return ResponseHelper::internalServerError($e->getMessage());
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
            return ResponseHelper::success(null, 'Employee deleted successfully');
        } catch (Exception $e) {
            DB::rollback();
            return ResponseHelper::notFound("Employee not found");
        }
    }
}
