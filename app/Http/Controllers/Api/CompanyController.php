<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Laravel\Facades\Image;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use Exception;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companies = Company::orderBy('created_at', 'DESC')->paginate(10);
        return ResponseHelper::success(CompanyResource::collection($companies), "Company retrieved successfully");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:companies,email',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::validationError('Validation error', $validator->errors());
        }

        DB::beginTransaction();
        try {
            $company = new Company();
            $company->name = $request->name;
            $company->email = $request->email;

            $destinationPath = storage_path('app/public/company/');
            $destinationPathThumbnail = storage_path('app/public/company/thumbnail/');

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
                $company->logo = $imageName;
            }

            $company->website = $request->website;
            $company->save();

            DB::commit();
            return ResponseHelper::success(new CompanyResource($company), "Company created successfully");
        } catch (Exception $e) {
            DB::rollback();
            return ResponseHelper::internalServerError($e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $company = Company::findOrFail($id);
            return ResponseHelper::success(new CompanyResource($company), "Company retrieved successfully");
        } catch (Exception $e) {
            return ResponseHelper::notFound("Company not found");
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function companyUpdate(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:companies,email,' . $id,
        ]);

        if ($validator->fails()) {
            return ResponseHelper::validationError('Validation error', $validator->errors());
        }

        DB::beginTransaction();
        try {
            $company->name = $request->name;
            $company->email = $request->email;

            $destinationPath = storage_path('app/public/company/');
            $destinationPathThumbnail = storage_path('app/public/company/thumbnail/');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            if (!File::exists($destinationPathThumbnail)) {
                File::makeDirectory($destinationPathThumbnail, 0755, true);
            }

            if ($request->hasFile('image')) {
                // Delete old image if it exists
                if ($company->logo) {
                    $oldImagePath = storage_path('app/public/company/' . $company->logo);
                    $oldThumbnailPath = storage_path('app/public/company/thumbnail/' . $company->logo);

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

                $company->logo = $imageName;
            }

            $company->website = $request->website;
            $company->save();

            DB::commit();
            return ResponseHelper::success(new CompanyResource($company), "Company Updated successfully");
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
        $company = Company::findOrFail($id);

        DB::beginTransaction();
        try {
            if ($company->logo) {
                $oldImagePath = storage_path('app/public/company/' . $company->logo);
                $oldThumbnailPath = storage_path('app/public/company/thumbnail/' . $company->logo);

                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }

                if (File::exists($oldThumbnailPath)) {
                    File::delete($oldThumbnailPath);
                }
            }

            $company->delete();

            DB::commit();
            return ResponseHelper::success(null, 'Company deleted successfully');
        } catch (Exception $e) {
            DB::rollback();
            return ResponseHelper::notFound("Company not found");
        }
    }
}
