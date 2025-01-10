<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view role', only: ['index']),
            new Middleware('permission:create role', only: ['create']),
            new Middleware('permission:edit role', only: ['edit']),
            new Middleware('permission:delete role', only: ['destroy']),
        ];
    }
    public function index(Request $request)
    {
        $roles = Role::latest();
        foreach ($roles as $role) {
            $role->user_count = User::role($role->name)->count();
        }
        if (!empty($request->get('keyword'))) {
            $roles = $roles->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $roles = $roles->paginate(10);

        return view('role.list', compact('roles'));
    }


    public function create()
    {
        $permissions = Permission::orderBy('name', 'ASC')->get();
        return view('role.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles|min:3',
        ]);

        if ($validator->fails()) {
            return redirect()->route('roleCreate')->withInput()->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $request->name]);

            if (!empty($request->permission)) {
                foreach ($request->permission as $name) {
                    $role->givePermissionTo($name);
                }
            }
            DB::commit();
            return redirect()->route('role')->with('success', 'Role added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save role: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to create Role');
        }
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $hasPermission = [];
        $permissions = [];

        if ($role->name !== 'SuperAdmin') {
            $hasPermission = $role->permissions->pluck('name');
            $permissions = Permission::orderBy('id', 'DESC')->get();
        }

        return view('role.edit', compact('role', 'hasPermission', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name,' . $id . ',id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            $role->name = $request->name;
            $role->save();

            if (!empty($request->permission)) {
                $role->syncPermissions($request->permission);
            } else {
                $role->syncPermissions([]);
            }
            DB::commit();
            return redirect()->route('role')->with('success', 'Role updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save role: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to Role');
        }
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        DB::beginTransaction();
        try {

            $role->delete();
            DB::commit();
            return redirect()->route('role')->with('success', 'Role deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete role: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete role');
        }
    }
}
