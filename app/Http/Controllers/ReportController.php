<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\User;
use App\Models\Report;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:reports.view')->only(['byApp','sla','technicians','show']);
    }

    public function byApp()
    {
        $byApp = Incident::selectRaw('application_id, COUNT(*) as total')
            ->groupBy('application_id')->orderByDesc('total')
            ->with('application:id,nom')->limit(10)->get();

        $labels = $byApp->map(fn($r)=>$r->application->nom ?? '—')->toArray();
        $counts = $byApp->pluck('total')->toArray();

        $rows = $byApp->map(function($r){
            $appId   = $r->application_id;
            $ouverts = Incident::where('application_id',$appId)->whereNull('resolved_at')->count();
            $resolus = Incident::where('application_id',$appId)->whereNotNull('resolved_at')->count();
            return (object)[
                'app'=>$r->application->nom ?? '—',
                'total'=>$r->total,'ouverts'=>$ouverts,'resolus'=>$resolus
            ];
        });

        return view('reports.byapp', compact('labels','counts','rows'));
    }

    public function sla()
    {
        $overdue = Incident::slaAtRisk()->where('due_at','<', now())->count();
        $soon    = Incident::slaAtRisk()->whereBetween('due_at', [now(), now()->addHours(4)])->count();

        $list = Incident::slaAtRisk()
            ->with(['application:id,nom','technicien:id,name'])
            ->orderBy('due_at')
            ->paginate(10)              // ✅ paginator (si tu veux la pagination)
            ->withQueryString();

        // (optionnel) marquage retard
        $list->getCollection()->transform(function ($i) {
            $i->is_late = $i->due_at && $i->due_at->isPast();
            return $i;
        });

        return view('reports.sla', [
            'overdueCount' => $overdue,
            'soonCount'    => $soon,
            'list'         => $list,    // ✅ le nom correspond à la vue
        ]);
    }

    public function technicians()
    {
        $techs = User::role('technicien')->get();

        $labels=[]; $openData=[]; $resolvedData=[]; $rows=[];
        foreach ($techs as $t) {
            $open     = Incident::where('technicien_id',$t->id)->whereNull('resolved_at')->count();
            $resolved = Incident::where('technicien_id',$t->id)
                        ->whereNotNull('resolved_at')
                        ->whereBetween('resolved_at',[now()->subDays(30), now()])->count();
            $slaRisk  = Incident::where('technicien_id',$t->id)->slaAtRisk()->count();

            $labels[] = $t->name; $openData[] = $open; $resolvedData[] = $resolved;
            $rows[] = (object)['tech'=>$t->name,'ouverts'=>$open,'resolus30'=>$resolved,'sla_risk'=>$slaRisk];
        }

        return view('reports.technicians', compact('labels','openData','resolvedData','rows'));
    }

    // --- Rapport d’intervention lié à un incident ---
    public function createForIncident(Incident $incident)
    {
        if ($incident->report) {
            return redirect()->route('reports.edit',$incident->report)->with('info','Un rapport existe déjà.');
        }
        return view('reports.create_for_incident', compact('incident'));
    }

    public function storeForIncident(Request $request, Incident $incident)
    {
        $data = $request->validate([
            'description'    => ['required','string'],
            'constats'       => ['required','string'],
            'causes'         => ['required','string'],
            'actions'        => ['required','string'],
            'impacts'        => ['required','string'],
            'recommendation' => ['nullable','string'],
            'started_at'     => ['required','date'],
            'ended_at'       => ['required','date','after:started_at'],
        ]);

        $started = Carbon::parse($data['started_at']);
        $ended   = Carbon::parse($data['ended_at']);
        $minutes = $ended->diffInMinutes($started);

        $report = Report::create(array_merge($data, [
            'incident_id'      => $incident->id,
            'author_id'        => auth()->id(),
            'ref'              => $incident->code,
            'duration_minutes' => $minutes,
        ]));

        $incident->update(['statut'=>'Résolu','resolved_at'=>$ended]);

        return redirect()->route('reports.show',$report)->with('success','Rapport enregistré.');
    }

    public function show(Report $report)
    {
        return view('reports.show', ['report'=>$report->load('incident.application','auteur')]);
    }

    public function edit(Report $report)
    {
        return view('reports.edit', compact('report'));
    }

    public function update(Request $request, Report $report)
    {
        $data = $request->validate([
            'description'    => ['required','string'],
            'constats'       => ['required','string'],
            'causes'         => ['required','string'],
            'actions'        => ['required','string'],
            'impacts'        => ['required','string'],
            'recommendation' => ['nullable','string'],
            'started_at'     => ['required','date'],
            'ended_at'       => ['required','date','after:started_at'],
        ]);

        $started = Carbon::parse($data['started_at']);
        $ended   = Carbon::parse($data['ended_at']);
        $minutes = $ended->diffInMinutes($started);

        $report->update(array_merge($data, ['duration_minutes'=>$minutes]));

        $report->incident->update(['statut'=>'Résolu','resolved_at'=>$ended]);

        return redirect()->route('reports.show',$report)->with('success','Rapport mis à jour.');
    }
}
