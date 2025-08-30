<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:users.view')->only('index');
        $this->middleware('can:users.create')->only(['create','store']);
        $this->middleware('can:users.update')->only(['edit','update']);
        $this->middleware('can:users.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = User::with(['service','roles']);

        if ($request->filled('service_id')) $query->where('service_id',$request->integer('service_id'));
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(fn($qq)=>$qq->where('name','like',"%{$q}%")->orWhere('email','like',"%{$q}%"));
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();
        $services = Service::orderBy('nom')->get();

        return view('users.index', compact('users','services'));
    }

    public function create()
    {
        $roles    = Role::orderBy('name')->pluck('name','id');
        $services = Service::orderBy('nom')->pluck('nom','id');
        return view('users.create', compact('roles','services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required','string','max:255'],
            'email'      => ['required','email','max:255','unique:users,email'],
            'title'      => ['nullable','string','max:255'],
            'password'   => ['required','string','min:8','confirmed'],
            'role_id'    => ['required', Rule::exists('roles','id')],
            'service_id' => ['nullable','exists:services,id'],
        ]);

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'title'      => $data['title'] ?? null,
            'service_id' => $data['service_id'] ?? null,
            'password'   => Hash::make($data['password']),
        ]);

        $role = Role::findById($data['role_id']);
        $user->syncRoles([$role->name]);

        return redirect()->route('users.index')->with('success','Utilisateur créé.');
    }

    public function edit(User $user)
    {
        $roles    = Role::orderBy('name')->pluck('name','id');
        $services = Service::orderBy('nom')->pluck('nom','id');
        return view('users.edit', compact('user','roles','services'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'       => ['required','string','max:255'],
            'email'      => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'title'      => ['nullable','string','max:255'],
            'password'   => ['nullable','string','min:8','confirmed'],
            'role_id'    => ['required', Rule::exists('roles','id')],
            'service_id' => ['nullable','exists:services,id'],
        ]);

        $user->fill([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'title'      => $data['title'] ?? null,
            'service_id' => $data['service_id'] ?? null,
        ]);
        if (!empty($data['password'])) $user->password = Hash::make($data['password']);
        $user->save();

        $role = Role::findById($data['role_id']);
        $user->syncRoles([$role->name]);

        return redirect()->route('users.index')->with('success','Utilisateur mis à jour.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error','Impossible de supprimer votre propre compte.');
        }
        $user->delete();
        return back()->with('success','Utilisateur supprimé.');
    }
}
