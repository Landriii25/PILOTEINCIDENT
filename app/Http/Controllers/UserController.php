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

        $roles    = Role::orderBy('name')->get();
        $services = Service::orderBy('nom')->get();

        return view('users.create', compact('roles','services'));
    }

    public function store(Request $request)
    {
        // 1. Valider les données
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'title'      => ['nullable', 'string', 'max:255'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'role_id'    => ['required', Rule::exists('roles', 'id')],
            'service_id' => ['nullable', 'exists:services,id'],
        ]);

        // 2. Hasher le mot de passe
        $data['password'] = Hash::make($data['password']);

        // 3. Créer l'utilisateur
        $user = User::create($data);

        // 4. CORRECTION : Attribuer le rôle
        // On trouve l'objet Role à partir de son ID...
        $role = Role::findById($data['role_id']);
        // ...puis on le synchronise avec l'utilisateur.
        $user->syncRoles($role);

        // 5. Rediriger
        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        // On utilise get() ici aussi
        $roles    = Role::orderBy('name')->get();
        $services = Service::orderBy('nom')->get();

        return view('users.edit', compact('user','roles','services'));
    }

    public function show(User $user)
    {
        // Laravel injecte automatiquement l'utilisateur correspondant à l'ID de l'URL.
        // On charge les relations pour pouvoir les afficher dans la vue.
        $user->load('service', 'roles');

        // On retourne la vue en lui passant l'objet utilisateur.
        return view('users.show', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // La validation ne change pas
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'title'      => ['nullable', 'string', 'max:255'],
            'password'   => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_id'    => ['required', Rule::exists('roles', 'id')],
            'service_id' => ['nullable', 'exists:services,id'],
        ]);

        // AMÉLIORATION 1 : On passe tout le tableau $data directement.
        // Plus besoin de lister les champs un par un. C'est moins d'erreurs si vous en ajoutez.
        $user->fill($data);

        // On gère le mot de passe séparément
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        // AMÉLIORATION 2 (mineure) : On passe l'objet $role directement, c'est plus propre.
        $role = Role::findById($data['role_id']);
        $user->syncRoles($role);

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour.');
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
