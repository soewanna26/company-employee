<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Laravel\Facades\Image;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companies = Company::latest();
        if (!empty($request->get('keyword'))) {
            $companies = $companies->where('name', 'like', '%' . $request->get('keyword') . '%')->orWhere('email', 'like', '%' . $request->get('keyword') . '%')->orWhere('website', 'like', '%' . $request->get('keyword') . '%');
        }
        $companies = $companies->paginate(10);
        return view('company.list', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('company.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|unique:companies,email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
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
            return redirect()->route('company')->with('success', 'Company added Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save company: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to create company');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $company = Company::findOrFail($id);
        return view('company.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|unique:companies,email,' . $id . ',id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
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
            return redirect()->route('company')->with('success', 'Company Updated Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save company: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to create company');
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
            if (!empty($company->logo)) {
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
            }
            $company->delete();

            DB::commit();
            return redirect()->route('company')->with('success', 'Company deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete Company: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete company');
        }
    }
}
