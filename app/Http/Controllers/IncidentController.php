<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Application;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NouvelIncidentNotification;
use App\Notifications\IncidentAssignedNotification;
use App\Notifications\IncidentCommentedNotification;
use App\Notifications\IncidentResolvedNotification;
use Illuminate\Validation\Rules\File;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IncidentsExport;

class IncidentController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:incidents.create')->only(['create', 'store']);
    }

    public function index(Request $request)
    {
        $q = Incident::query()->with(['application:id,nom', 'technicien:id,name', 'user:id,name']);
        $user = auth()->user();

        // --- LOGIQUE MÉTIER AJOUTÉE CI-DESSOUS ---
        // Si l'utilisateur est un superviseur OU un technicien, on restreint la vue à son service.
        // L'admin, lui, n'est pas affecté par ce filtre et voit tout.
        if ($user->hasRole('superviseur') || $user->hasRole('technicien')) {
            if ($user->service_id) {
                $q->where('service_id', $user->service_id);
            } else {
                // Si un superviseur/technicien n'a pas de service, il ne voit aucun incident.
                $q->whereRaw('1 = 0'); // Condition qui ne retourne jamais rien
            }
        }
        // --- FIN DE LA LOGIQUE MÉTIER ---

        // Le reste de vos filtres
        if ($request->filled('q')) {
            $term = trim($request->q);
            $q->where(function ($qq) use ($term) {
                $qq->where('code', 'like', "%{$term}%")
                    ->orWhere('titre', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }
        if ($p = $request->get('priorite'))       $q->where('priorite', $p);
        if ($s = $request->get('statut'))         $q->where('statut', $s);
        if ($a = $request->get('application_id')) $q->where('application_id', $a);
        if ($t = $request->get('technicien_id'))  $q->where('technicien_id', $t);
        if ($request->boolean('open'))            $q->whereNull('resolved_at');
        if ($request->boolean('mine'))            $q->where('user_id', auth()->id());

        $incidents = $q->orderByDesc('created_at')->paginate(10)->withQueryString();

        // Les données pour les listes déroulantes des filtres
        $priorites    = Incident::PRIORITES;
        $statuts      = ['Ouvert', 'En cours', 'Résolu', 'Fermé'];
        $applications = Application::orderBy('nom')->get(['id', 'nom']);
        $techniciens  = User::role('technicien')->orderBy('name')->get(['id', 'name']);

        return view('incidents.index', compact('incidents', 'priorites', 'statuts', 'applications', 'techniciens'));
    }

    public function mine()
    {
        // On redirige simplement vers la page de liste principale
        // en ajoutant le paramètre "mine=1" à l'URL.
        return redirect()->route('incidents.index', ['mine' => 1]);
    }

    public function create()
    {
        $apps = Application::with('service:id,nom')->orderBy('nom')->get(['id', 'nom', 'service_id']);
        $techs = User::role('technicien')->orderBy('name')->get(['id', 'name', 'service_id']);

        $year  = now()->format('y');
        $month = now()->format('m');
        $count = Incident::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count() + 1;
        $previewCode = 'INC' . $year . $month . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $mapAppServices = $apps
            ->filter(fn ($app) => $app->service)
            ->mapWithKeys(function ($app) {
                return [$app->id => ['id' => $app->service->id, 'nom' => $app->service->nom]];
            });

        return view('incidents.create', compact('apps', 'techs', 'previewCode', 'mapAppServices'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'application_id' => ['required', 'exists:applications,id'],
            'service_id'     => ['nullable', 'exists:services,id'],
            'technicien_id'  => ['nullable', 'exists:users,id'],
            'priorite'       => ['required', 'in:' . implode(',', Incident::PRIORITES)],
            'attachments'    => ['nullable', 'array', 'max:5'],
            'attachments.*'  => [File::types(['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'])->max(5 * 1024)],
        ]);

        $incident = new Incident($data);
        $incident->user_id = auth()->id();
        $incident->statut  = 'Ouvert';
        $incident->save();

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');
                $incident->attachments()->create([
                    'chemin_fichier' => $path,
                    'nom_original'   => $file->getClientOriginalName(),
                ]);
            }
        }

        // --- NOTIFICATION : Nouvel Incident ---
        if ($incident->service_id) {
            $destinataires = User::role('technicien')
                ->where('service_id', $incident->service_id)
                ->get();

            if ($destinataires->isNotEmpty()) {
                Notification::send($destinataires, new NouvelIncidentNotification($incident));
            }
        }

        return redirect()->route('incidents.show', $incident)->with('success', 'Incident créé.');
    }

    public function show(Incident $incident)
    {
        $incident->load(['application:id,nom', 'technicien:id,name', 'user:id,name', 'attachments']);
        $commentaires = $incident->commentaires()->latest()->with('user:id,name')->get();

        return view('incidents.show', compact('incident', 'commentaires'));
    }

    public function edit(Incident $incident)
    {
        // Votre vérification d'autorisation est conservée
        if (!auth()->user()->hasRole('admin') && $incident->user_id !== auth()->id() && $incident->technicien_id !== auth()->id()) {
            abort(403);
        }

        // MODIFICATION 1 : On s'assure de récupérer les données nécessaires pour le JS
        $apps = Application::with('service:id,nom')->orderBy('nom')->get(['id', 'nom']);
        $techs = User::role('technicien')->orderBy('name')->get(['id', 'name', 'service_id']);

        // AJOUT : On crée la carte [id_application => service] pour le JavaScript
        $mapAppServices = $apps
            ->filter(fn($app) => $app->service)
            ->mapWithKeys(function ($app) {
                return [
                    $app->id => [
                        'id' => $app->service->id,
                        'nom' => $app->service->nom
                    ]
                ];
            });

        // MODIFICATION 2 : On passe la nouvelle variable à la vue
        return view('incidents.edit', compact('incident', 'apps', 'techs', 'mapAppServices'));
    }

    public function update(Request $request, Incident $incident)
    {
        if (!auth()->user()->hasRole('admin') && $incident->user_id !== auth()->id() && $incident->technicien_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'titre'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'application_id' => ['required', 'exists:applications,id'],
            'service_id'     => ['nullable', 'exists:services,id'],
            'technicien_id'  => ['nullable', 'exists:users,id'],
            'priorite'       => ['required', 'in:' . implode(',', Incident::PRIORITES)],
            'statut'         => ['required', 'in:Ouvert,En cours,Résolu,Fermé'],
        ]);

        $ancienTechnicienId = $incident->technicien_id;

        if ($request->filled('technicien_id') && is_null($incident->acknowledged_at)) {
            $incident->acknowledged_at = now();
        }

        $incident->fill($data)->save();

        $nouveauTechnicienId = $incident->technicien_id;
        if ($nouveauTechnicienId && $nouveauTechnicienId != $ancienTechnicienId) {
            $technicien = User::find($nouveauTechnicienId);
            if ($technicien) {
                $technicien->notify(new IncidentAssignedNotification($incident));
            }
        }

        return redirect()->route('incidents.show', $incident)->with('success', 'Incident mis à jour.');
    }

    public function commenter(Request $request, Incident $incident)
    {
        $request->validate(['commentaire' => ['required', 'string', 'max:2000']]);

        $commentaire = $incident->commentaires()->create([
            'user_id'  => auth()->id(),
            'contenu'  => $request->commentaire,
        ]);

        $destinataires = collect();
        if ($incident->user) $destinataires->push($incident->user);
        if ($incident->technicien) $destinataires->push($incident->technicien);

        $destinataires = $destinataires->unique('id')->reject(fn ($user) => $user->id === auth()->id());

        if ($destinataires->isNotEmpty()) {
            $auteur = auth()->user()->name;
            $extrait = Str::limit($commentaire->contenu, 100);
            Notification::send($destinataires, new IncidentCommentedNotification($incident, $auteur, $extrait));
        }

        return back()->with('success', 'Commentaire ajouté.');
    }

    public function resolve(Request $request, Incident $incident)
    {
        if (!auth()->user()->hasRole('admin') && $incident->technicien_id !== auth()->id()) {
            abort(403);
        }

        $incident->update([
            'statut'      => 'Résolu',
            'resolved_at' => now(),
        ]);

        $destinataire = $incident->user;
        if ($destinataire && $destinataire->id !== auth()->id()) {
            $destinataire->notify(new IncidentResolvedNotification($incident));
        }

        return back()->with('success', 'Incident marqué comme résolu.');
    }

    public function close(Incident $incident)
    {
        if (!auth()->user()->hasRole('admin') && $incident->user_id !== auth()->id()) {
            abort(403);
        }

        $incident->update(['statut' => 'Fermé']);

        return back()->with('success', 'Incident fermé.');
    }

    public function reopenToSameTech(Incident $incident)
    {
        if (!auth()->user()->hasRole('admin') && $incident->user_id !== auth()->id()) {
            abort(403);
        }

        $incident->update([
            'statut'      => 'En cours',
            'resolved_at' => null,
        ]);

        return back()->with('success', 'Incident ré-ouvert au même technicien.');
    }

    public function slaAtRisk()
    {
        $list = Incident::slaAtRisk()->with(['application:id,nom', 'technicien:id,name'])
            ->orderBy('due_at')->paginate(10);

        return view('incidents.sla', ['incidents' => $list]);
    }

    public function export()
    {
        return Excel::download(new IncidentsExport, 'rapport-incidents.xlsx');
    }
}
