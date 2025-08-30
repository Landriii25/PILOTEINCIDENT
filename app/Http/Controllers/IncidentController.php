<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Application;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IncidentController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:incidents.create')->only(['create','store']);
        // autres actions : gardes métier dans les méthodes (créateur / technicien assigné / superviseur)
    }

    public function index(Request $request)
    {
        $q = Incident::query()->with(['application:id,nom','technicien:id,name','user:id,name']);

        // Filtres rapides
        if ($request->filled('q')) {
            $term = trim($request->q);
            $q->where(function($qq) use ($term){
                $qq->where('code', 'like', "%{$term}%")
                   ->orWhere('titre','like',"%{$term}%")
                   ->orWhere('description','like',"%{$term}%");
            });
        }
        if ($p = $request->get('priorite'))    $q->where('priorite',$p);
        if ($s = $request->get('statut'))      $q->where('statut',$s);
        if ($a = $request->get('application_id')) $q->where('application_id',$a);
        if ($t = $request->get('technicien_id'))  $q->where('technicien_id',$t);

        if ($request->boolean('open')) $q->whereNull('resolved_at');
        if ($request->boolean('mine')) $q->where('user_id', auth()->id());

        $incidents = $q->orderByDesc('created_at')->paginate(10)->withQueryString();

        $priorites    = Incident::PRIORITES;
        $statuts      = ['Ouvert','En cours','Résolu','Fermé'];
        $applications = Application::orderBy('nom')->get(['id','nom']);
        $techniciens  = User::role('technicien')->orderBy('name')->get(['id','name']);

        return view('incidents.index', compact('incidents','priorites','statuts','applications','techniciens'));
    }

    public function mine(Request $request)
    {
        $q = Incident::with(['application:id,nom','technicien:id,name'])
            ->where('user_id', auth()->id());

        $incidents = $q->orderByDesc('created_at')->paginate(10)->withQueryString();

        return view('incidents.mine', compact('incidents'));
    }

    public function create()
    {
        $apps = Application::with('service:id,nom')->orderBy('nom')->get(['id','nom','service_id']);
        $techs = User::role('technicien')->orderBy('name')->get(['id','name','service_id']);

        // Code visible (prévisualisation) — sera confirmé au saving
        $year  = now()->format('y');
        $month = now()->format('m');
        $count = Incident::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count() + 1;
        $nextCode = 'INC'.$year.$month.'-'.str_pad($count,4,'0',STR_PAD_LEFT);

        return view('incidents.create', compact('apps','techs','nextCode'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'          => ['required','string','max:255'],
            'description'    => ['nullable','string'],
            'application_id' => ['required','exists:applications,id'],
            'service_id'     => ['nullable','exists:services,id'],
            'technicien_id'  => ['nullable','exists:users,id'],
            'priorite'       => ['required','in:'.implode(',', Incident::PRIORITES)],
        ]);

        $incident = new Incident($data);
        $incident->user_id = auth()->id();
        $incident->statut  = 'Ouvert';
        $incident->save(); // booted() génère code + due_at

        return redirect()->route('incidents.show', $incident)->with('success','Incident créé.');
    }

    public function show(Incident $incident)
    {
        $incident->load(['application:id,nom','technicien:id,name','user:id,name']);
        $commentaires = $incident->commentaires()->latest()->with('user:id,name')->get();

        return view('incidents.show', compact('incident','commentaires'));
    }

    public function edit(Incident $incident)
    {
        // Garde métier simple : admin, créateur, technicien assigné
        if (!auth()->user()->hasRole('admin')
            && $incident->user_id !== auth()->id()
            && $incident->technicien_id !== auth()->id()) {
            abort(403);
        }

        $apps = \App\Models\Application::orderBy('nom')->get(['id','nom']);
        $techs = \App\Models\User::role('technicien')->orderBy('name')->get(['id','name']);

        return view('incidents.edit', compact('incident','apps','techs'));
    }

    public function update(Request $request, Incident $incident)
    {
        if (!auth()->user()->hasRole('admin')
            && $incident->user_id !== auth()->id()
            && $incident->technicien_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'titre'          => ['required','string','max:255'],
            'description'    => ['nullable','string'],
            'application_id' => ['required','exists:applications,id'],
            'service_id'     => ['nullable','exists:services,id'],
            'technicien_id'  => ['nullable','exists:users,id'],
            'priorite'       => ['required','in:'.implode(',', Incident::PRIORITES)],
            'statut'         => ['required','in:Ouvert,En cours,Résolu,Fermé'],
        ]);

        $incident->fill($data)->save();

        return redirect()->route('incidents.show',$incident)->with('success','Incident mis à jour.');
    }

    public function commenter(Request $request, Incident $incident)
    {
        $request->validate(['commentaire'=>['required','string','max:2000']]);

        $incident->commentaires()->create([
            'user_id'  => auth()->id(),
            'contenu'  => $request->commentaire,
        ]);

        return back()->with('success','Commentaire ajouté.');
    }

    public function addComment(Request $request, Incident $incident)
    {
        return $this->commenter($request,$incident);
    }

    public function resolve(Request $request, Incident $incident)
    {
        // technicien assigné ou admin
        if (!auth()->user()->hasRole('admin') && $incident->technicien_id !== auth()->id()) {
            abort(403);
        }

        $incident->update([
            'statut'      => 'Résolu',
            'resolved_at' => now(),
        ]);

        return back()->with('success','Incident marqué comme résolu.');
    }

    public function close(Incident $incident)
    {
        // créateur ou admin
        if (!auth()->user()->hasRole('admin') && $incident->user_id !== auth()->id()) {
            abort(403);
        }

        $incident->update(['statut'=>'Fermé']);

        return back()->with('success','Incident fermé.');
    }

    public function reopenToSameTech(Incident $incident)
    {
        // créateur ou admin
        if (!auth()->user()->hasRole('admin') && $incident->user_id !== auth()->id()) {
            abort(403);
        }

        $incident->update([
            'statut'      => 'En cours',
            'resolved_at' => null,
        ]);

        return back()->with('success','Incident ré-ouvert au même technicien.');
    }

    public function slaAtRisk()
    {
        $list = Incident::slaAtRisk()->with(['application:id,nom','technicien:id,name'])
            ->orderBy('due_at')->paginate(10);

        return view('incidents.sla', ['incidents'=>$list]);
    }
}
