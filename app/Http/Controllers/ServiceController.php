<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    /**
     * Liste paginée des services + recherche.
     */
    public function index(Request $request)
    {
        // Permission lecture
        if ($request->user()->cannot('services.view')) {
            abort(403);
        }

        $q = trim((string) $request->get('q', ''));

        $services = Service::query()
            ->with(['chef:id,name,email'])                  // Chef de service
            ->withCount(['applications', 'techniciens'])    // Besoin d'avoir défini ces relations sur Service
            ->when($q !== '', function ($query) use ($q) {
                $query->where(fn ($qq) =>
                    $qq->where('nom', 'like', "%{$q}%")
                       ->orWhere('description', 'like', "%{$q}%")
                );
            })
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('services.index', compact('services', 'q'));
    }

    /**
     * Formulaire de création.
     */
    public function create(Request $request)
    {
        if ($request->user()->cannot('services.create')) {
            abort(403);
        }

        // Liste des chefs potentiels (par défaut tous les users, tu peux filtrer par rôle 'superviseur')
        $chefs = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('services.create', compact('chefs'));
    }

    /**
     * Enregistrement.
     */
    public function store(Request $request)
    {
        if ($request->user()->cannot('services.create')) {
            abort(403);
        }

        $data = $request->validate([
            'nom'         => ['required', 'string', 'max:255', 'unique:services,nom'],
            'description' => ['nullable', 'string', 'max:1000'],
            'chef_id'     => ['nullable', 'integer', Rule::exists('users', 'id')],
        ]);

        $service = Service::create($data);

        return redirect()
            ->route('services.show', $service)
            ->with('success', 'Service créé avec succès.');
    }

    /**
     * Fiche service.
     */
    public function show(Request $request, Service $service)
    {
        if ($request->user()->cannot('services.view')) {
            abort(403);
        }

        $service->load([
            'chef:id,name,email',
            'applications:id,nom,statut,service_id,logo_path',
        ]);

        // Optionnel : techniciens du service (si tu veux les afficher à droite)
        // Nécessite que User ait un champ service_id + rôles Spatie.
        $techniciens = User::role('technicien')
            ->where('service_id', $service->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('services.show', compact('service', 'techniciens'));
    }

    /**
     * Formulaire d’édition.
     */
    public function edit(Request $request, Service $service)
    {
        if ($request->user()->cannot('services.update')) {
            abort(403);
        }

        $chefs = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('services.edit', compact('service', 'chefs'));
    }

    /**
     * Mise à jour.
     */
    public function update(Request $request, Service $service)
    {
        if ($request->user()->cannot('services.update')) {
            abort(403);
        }

        $data = $request->validate([
            'nom'         => ['required', 'string', 'max:255', Rule::unique('services', 'nom')->ignore($service->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'chef_id'     => ['nullable', 'integer', Rule::exists('users', 'id')],
        ]);

        $service->update($data);

        return redirect()
            ->route('services.show', $service)
            ->with('success', 'Service mis à jour avec succès.');
    }

    /**
     * Suppression.
     */
    public function destroy(Request $request, Service $service)
    {
        if ($request->user()->cannot('services.delete')) {
            abort(403);
        }

        $service->delete();

        return redirect()
            ->route('services.index')
            ->with('success', 'Service supprimé.');
    }

    /**
     * API simple : liste des techniciens d’un service (pour select dépendant).
     * Route: GET services/{service}/techniciens
     * Retourne JSON [{id,name,email}, ...]
     */
    public function technicians(Request $request, Service $service)
    {
        if ($request->user()->cannot('services.view')) {
            abort(403);
        }

        $techs = User::role('technicien')
            ->where('service_id', $service->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json($techs);
    }
}
