<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:services.view')->only(['index','show']);
        $this->middleware('can:services.create')->only(['create','store']);
        $this->middleware('can:services.update')->only(['edit','update']);
        $this->middleware('can:services.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $q = Service::query()->with('chef:id,name');

        if ($request->filled('q')) {
            $term = trim($request->q);
            $q->where('nom','like',"%{$term}%");
        }

        $services = $q->orderBy('nom')->paginate(10)->withQueryString();

        return view('services.index', compact('services'));
    }

    public function create()
    {
        $chefs = User::orderBy('name')->get(['id','name']);
        return view('services.create', compact('chefs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'       => ['required','string','max:255'],
            'chef_id'   => ['nullable','exists:users,id'],
            'telephone' => ['nullable','string','max:50'],
            'email'     => ['nullable','email','max:255'],
            'description'=>['nullable','string'],
        ]);

        Service::create($data);

        return redirect()->route('services.index')->with('success','Service créé.');
    }

    public function show(Service $service)
    {
        return view('services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        $chefs = User::orderBy('name')->get(['id','name']);
        return view('services.edit', compact('service','chefs'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'nom'       => ['required','string','max:255'],
            'chef_id'   => ['nullable','exists:users,id'],
            'telephone' => ['nullable','string','max:50'],
            'email'     => ['nullable','email','max:255'],
            'description'=>['nullable','string'],
        ]);

        $service->update($data);

        return redirect()->route('services.index')->with('success','Service mis à jour.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return back()->with('success','Service supprimé.');
    }

    // Ajax : techniciens du service
    public function technicians(Service $service)
    {
        $techs = $service->techniciens()->select('id','name')->orderBy('name')->get();
        return response()->json($techs);
    }
}
