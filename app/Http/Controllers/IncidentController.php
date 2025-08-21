<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Application;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// Notifications existantes chez toi
use App\Notifications\IncidentAssignedNotification;
use App\Notifications\IncidentCommentedNotification;

class IncidentController extends Controller
{
    /* ===================== LISTES ===================== */

    // Tous les incidents (admin / lecture étendue via politiques/permissions)
    public function index(Request $request)
    {
        // Bases de la requête
        $q = \App\Models\Incident::with(['application:id,nom', 'technicien:id,name', 'user:id,name']);

        // Recherche plein texte simple (titre/description/code)
        if ($request->filled('q')) {
            $term = trim($request->q);
            $q->where(function ($qq) use ($term) {
                $qq->where('titre', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%");
            });
        }

        // Filtres structurés
        if ($request->filled('priorite')) {
            $q->where('priorite', $request->priorite);
        }

        if ($request->filled('statut')) {
            $q->where('statut', $request->statut);
        }

        if ($request->filled('application_id')) {
            $q->where('application_id', $request->integer('application_id'));
        }

        if ($request->filled('technicien_id')) {
            $q->where('technicien_id', $request->integer('technicien_id'));
        }

        // Cases à cocher rapides
        if ($request->boolean('open')) {
            $q->whereNull('resolved_at');
        }
        if ($request->boolean('mine')) {
            $q->where('user_id', $request->user()->id);
        }

        // Tri par défaut (les plus récents)
        $q->latest();

        // Pagination
        $incidents = $q->paginate(10)->withQueryString();

        // Listes pour les selects
        $applications = \App\Models\Application::orderBy('nom')->get(['id','nom']);
        $techniciens  = \App\Models\User::role('technicien')->orderBy('name')->get(['id','name']);

        // Constantes
        $priorites = \App\Models\Incident::PRIORITES;
        $statuts   = ['Ouvert','En cours','Résolu','Fermé'];

        return view('incidents.index', compact('incidents','applications','techniciens','priorites','statuts'));
}


    // Mes incidents (créateur)
    public function mine(Request $request)
    {
        $u = $request->user();

        $incidents = Incident::with(['application:id,nom','technicien:id,name'])
            ->where('user_id', $u->id)
            ->latest('id')
            ->paginate(12);

        return view('incidents.mine', compact('incidents'));
    }

    // Incidents SLA à risque
    public function slaAtRisk(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Incident::class); // optionnel selon tes policies

        $incidents = \App\Models\Incident::with(['application:id,nom','technicien:id,name'])
            ->slaAtRisk()                      // scope sur le modèle Incident
            ->orderBy('due_at')
            ->paginate(10)
            ->withQueryString();

        return view('incidents.sla', compact('incidents'));
    }

    /* ===================== CREATION ===================== */

   public function create()
    {
        // Applications + service pour l’auto-remplissage
        $applications = \App\Models\Application::with('service:id,nom')->orderBy('nom')->get();

        // Map JS: application_id -> {id, nom} du service
        $mapAppServices = $applications->mapWithKeys(function ($app) {
            return [
                $app->id => [
                    'id'  => $app->service_id,
                    'nom' => optional($app->service)->nom,
                ],
            ];
        });

        // Techniciens (si tu filtres par service côté JS, on part de la liste complète)
        $techniciens = \App\Models\User::role('technicien')->select('id','name','service_id')->orderBy('name')->get();

        // ⚠ Code «prévisionnel» juste pour l’affichage (le modèle génèrera le vrai code)
        $countThisMonth = \App\Models\Incident::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count() + 1;

        $previewCode = 'INC' . now()->format('ym') . '-' . str_pad($countThisMonth, 4, '0', STR_PAD_LEFT);

        return view('incidents.create', [
            'applications'    => $applications,
            'techniciens'     => $techniciens,
            'mapAppServices'  => $mapAppServices,
            'previewCode'     => $previewCode,
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre'          => ['required','string','max:255'],
            'description'    => ['nullable','string'],
            'priorite'       => ['required', Rule::in(Incident::PRIORITES)],
            'application_id' => ['required','exists:applications,id'],
            'technicien_id'  => ['nullable','exists:users,id'],
        ]);

        // Détermination du service depuis l’application
        $application = Application::select('id','service_id','nom')->findOrFail($validated['application_id']);
        $serviceId   = $application->service_id;

        $incident = new Incident($validated);
        $incident->user_id      = Auth::id();
        $incident->service_id   = $serviceId;         // auto-renseignement
        $incident->statut       = $incident->statut ?? 'Ouvert'; // par défaut
        // code + due_at sont générés dans Incident::booted() (creating)

        DB::transaction(function () use ($incident) {
            $incident->save();
        });

        // Notification d’assignation si technicien défini
        if (!empty($incident->technicien_id)) {
            optional($incident->technicien)->notify(
                new IncidentAssignedNotification($incident)
            );
        }

        return redirect()
            ->route('incidents.index')
            ->with('success', "Incident {$incident->code} créé avec succès.");
    }

    /* ===================== LECTURE / EDIT ===================== */

    public function show(Request $request, Incident $incident)
    {
        $incident->load(['application:id,nom','technicien:id,name','user:id,name','commentaires.user:id,name']);

        return view('incidents.show', [
            'incident'     => $incident,
            'commentaires' => $incident->commentaires()->latest()->get(),
        ]);
    }

    public function edit(Request $request, Incident $incident)
    {
        $applications = Application::select('id','nom','service_id')->orderBy('nom')->get();

        // Par défaut : tous les techniciens ; tu peux filtrer côté JS par service
        $techniciens  = User::role('technicien')->select('id','name','service_id')->orderBy('name')->get();

        return view('incidents.edit', compact('incident','applications','techniciens'));
    }

    public function update(Request $request, Incident $incident)
    {
        $validated = $request->validate([
            'titre'          => ['required','string','max:255'],
            'description'    => ['nullable','string'],
            'priorite'       => ['required', Rule::in(Incident::PRIORITES)],
            'statut'         => ['required','string','max:50'],
            'application_id' => ['required','exists:applications,id'],
            'technicien_id'  => ['nullable','exists:users,id'],
        ]);

        // Si application changée → service recalculé
        $app       = Application::select('id','service_id')->findOrFail($validated['application_id']);
        $serviceId = $app->service_id;

        $beforeTech = $incident->technicien_id;

        $incident->fill($validated);
        $incident->service_id = $serviceId;

        DB::transaction(function () use ($incident) {
            $incident->save();
        });

        // Notifier une (ré)assignation
        if (!empty($incident->technicien_id) && $incident->technicien_id != $beforeTech) {
            optional($incident->technicien)->notify(
                new IncidentAssignedNotification($incident)
            );
        }

        return back()->with('success', "Incident {$incident->code} mis à jour.");
    }

    public function destroy(Request $request, Incident $incident)
    {
        $incident->delete();

        return redirect()->route('incidents.index')
            ->with('success', "Incident {$incident->code} supprimé.");
    }

    /* ===================== ACTIONS MÉTIER ===================== */

    // Ajout d’un commentaire
    public function addComment(Request $request, Incident $incident)
    {
        $data = $request->validate([
            'commentaire' => ['required','string','max:5000'],
        ]);

        $comment = $incident->commentaires()->create([
            'user_id'  => Auth::id(),
            'contenu'  => $data['commentaire'],
        ]);

        // Notifier le créateur et (optionnel) le technicien
        if ($incident->user_id && $incident->user_id != Auth::id()) {
            optional($incident->user)->notify(new IncidentCommentedNotification($incident, $comment));
        }
        if ($incident->technicien_id && $incident->technicien_id != Auth::id()) {
            optional($incident->technicien)->notify(new IncidentCommentedNotification($incident, $comment));
        }

        return back()->with('success', 'Commentaire ajouté.');
    }

    // Marquer “Résolu” (utilisé par le technicien/superviseur/admin)
    public function resolve(Request $request, Incident $incident)
    {
        $incident->statut      = 'Résolu';
        $incident->resolved_at = now();
        $incident->save();

        // Notifier le créateur pour qu’il CLOSE ou REOPEN
        optional($incident->user)->notify(
            new IncidentAssignedNotification($incident) // utilise ton gabarit ou crée IncidentResolvedNotification
        );

        return back()->with('success', "Incident {$incident->code} marqué comme résolu.");
    }

    // CLOSE par le créateur (ou admin)
    public function close(Request $request, Incident $incident)
    {
        // Autoriser seulement créateur ou admin
        if (! (Auth::id() === $incident->user_id || $request->user()->hasRole('admin'))) {
            abort(403);
        }

        $incident->statut = 'Fermé';
        $incident->save();

        return back()->with('success', "Incident {$incident->code} clôturé.");
    }

    // REOPEN vers le même technicien par le créateur (ou admin)
    public function reopenToSameTech(Request $request, Incident $incident)
    {
        if (! (Auth::id() === $incident->user_id || $request->user()->hasRole('admin'))) {
            abort(403);
        }

        $incident->statut       = 'Ouvert';
        $incident->resolved_at  = null;
        // conserver technicien_id ; recalculer due_at selon priorité
        if (!empty($incident->priorite) && isset(Incident::SLA_HOURS[$incident->priorite])) {
            $incident->due_at = now()->addHours(Incident::SLA_HOURS[$incident->priorite]);
        }
        $incident->save();

        // notifier le technicien
        optional($incident->technicien)->notify(new IncidentAssignedNotification($incident));

        return back()->with('success', "Incident {$incident->code} ré-ouvert et ré-assigné au même technicien.");
    }
}
