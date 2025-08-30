<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        // Sécurisation par permissions Spatie, pas de Policy
        $this->middleware('can:roles.manage')->only(['index','create','store','edit','update','destroy']);
        // Si tu préfères granulaire :
        // $this->middleware('can:roles.view')->only('index');
        // $this->middleware('can:roles.create')->only(['create','store']);
        // $this->middleware('can:roles.update')->only(['edit','update']);
        // $this->middleware('can:roles.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $guard = 'web';

        $q = trim((string) $request->get('q'));
        $roles = Role::query()
            ->when($q !== '', fn($qq) =>
                $qq->where('name','like',"%{$q}%")
            )
            ->where('guard_name', $guard)
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('roles.index', compact('roles','q'));
    }

    public function create()
    {
        $guard = 'web';
        $permissions = Permission::where('guard_name',$guard)->orderBy('name')->get();

        return view('roles.create', [
            'permissions' => $permissions,
            'guard'       => $guard,
        ]);
    }

    public function store(Request $request)
    {
        $guard = 'web';

        $data = $request->validate([
            'name'        => ['required','string','max:190','unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => [
                Rule::exists('permissions','name')->where(fn($q)=>$q->where('guard_name',$guard))
            ],
        ]);

        // Rôle “admin” réservé
        if (strtolower($data['name']) === 'admin') {
            return back()->withErrors(['name' => 'Le nom "admin" est réservé.'])->withInput();
        }

        $role = Role::create([
            'name'       => $data['name'],
            'guard_name' => $guard,
        ]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return redirect()->route('roles.index')->with('success','Rôle créé avec succès.');
    }

    public function edit(Role $role)
    {
        $guard = 'web';

        if ($role->guard_name !== $guard) {
            abort(403, 'Guard non compatible');
        }

        $permissions = Permission::where('guard_name',$guard)->orderBy('name')->get();

        // Rendre le rôle “admin” non modifiable (optionnel)
        $isAdminRole = strtolower($role->name) === 'admin';

        return view('roles.edit', [
            'role'        => $role,
            'permissions' => $permissions,
            'isAdminRole' => $isAdminRole,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $guard = 'web';

        if ($role->guard_name !== $guard) {
            abort(403, 'Guard non compatible');
        }

        $data = $request->validate([
            'name'        => ['required','string','max:190', Rule::unique('roles','name')->ignore($role->id)],
            'permissions' => ['array'],
            'permissions.*' => [
                Rule::exists('permissions','name')->where(fn($q)=>$q->where('guard_name',$guard))
            ],
        ]);

        // Interdire la modif du nom “admin” (optionnel)
        if (strtolower($role->name) === 'admin' && strtolower($data['name']) !== 'admin') {
            return back()->withErrors(['name' => 'Le rôle "admin" ne peut pas être renommé.'])->withInput();
        }

        $role->name = $data['name'];
        $role->save();

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success','Rôle mis à jour.');
    }

    public function destroy(Role $role)
    {
        // Interdire suppression du rôle admin (optionnel)
        if (strtolower($role->name) === 'admin') {
            return back()->with('error','Le rôle "admin" ne peut pas être supprimé.');
        }

        $role->delete();

        return back()->with('success','Rôle supprimé.');
     }
}
