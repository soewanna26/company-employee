<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view permission', only: ['index']),
            new Middleware('permission:create permission', only: ['create']),
            new Middleware('permission:edit permission', only: ['edit']),
            new Middleware('permission:delete permission', only: ['destroy']),
        ];
    }
    public function index(Request $request)
    {
        $permissions = Permission::latest();
        if (!empty($request->get('keyword'))) {
            $permissions = $permissions->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $permissions = $permissions->paginate(10);
        return view('permission.list', compact('permissions'));
    }

    public function create()
    {
        return view('permission.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:permissions|min:3',
        ]);

        if ($validator->fails()) {
            return redirect()->route('permissionCreate')->withInput()->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            Permission::create(['name' => $request->name]);
            DB::commit();
            return redirect()->route('permission')->with('success', 'Permission added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save permission: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to create permission');
        }
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return view('permission.edit', compact('permission'));
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:permissions,name,' . $id . ',id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('permissionCreate')->withInput()->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            $permission->name = $request->name;
            $permission->save();
            DB::commit();
            return redirect()->route('permission')->with('success', 'Permission updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save permission: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to updated permission');
        }
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        DB::beginTransaction();
        try {

            $permission->delete();
            DB::commit();
            return redirect()->route('permission')->with('success', 'Permission deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete permission: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete permission');
        }
    }
}
