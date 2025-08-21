<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\Application;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Route /dashboard → redirige vers le bon dashboard selon le rôle
     */
    public function redirect(Request $request)
    {
        $u = $request->user();

        if ($u->hasRole('admin'))        return redirect()->route('dashboard.admin');
        if ($u->hasRole('superviseur'))  return redirect()->route('dashboard.superviseur');
        if ($u->hasRole('technicien'))   return redirect()->route('dashboard.technicien');

        // Par défaut: utilisateur (demandeur)
        return redirect()->route('dashboard.utilisateur');
    }

    /**
     * Dashboard Administrateur
     */
    public function admin(Request $request)
    {
        // KPI
        $incidentsOpen = Incident::whereNull('resolved_at')->count();
        $slaAtRisk     = Incident::slaAtRisk()->count();
        $resolved30    = Incident::whereNotNull('resolved_at')
                            ->whereBetween('resolved_at', [now()->subDays(30), now()])
                            ->count();
        $appsCount     = Application::count();
        $usersCount    = User::count();

        // Incidents par priorité
        $priorites  = Incident::PRIORITES;
        $byPriority = collect($priorites)->map(
            fn($p) => Incident::where('priorite',$p)->count()
        )->values();

        // Incidents par application (Top 10)
        $byAppRaw  = Incident::selectRaw('application_id, COUNT(*) AS c')
                        ->groupBy('application_id')
                        ->orderByDesc('c')
                        ->limit(10)
                        ->with('application:id,nom')
                        ->get();

        $appLabels = $byAppRaw->map(fn($r) => optional($r->application)->nom ?? '—')->values();
        $appCounts = $byAppRaw->pluck('c')->values();

        // Tendance 30 jours
        $days   = 30;
        $period = collect(range($days-1, 0))->map(fn($i) => now()->subDays($i)->startOfDay());

        $createdRaw = Incident::selectRaw('DATE(created_at) d, COUNT(*) c')
            ->where('created_at', '>=', now()->subDays($days)->startOfDay())
            ->groupBy('d')->orderBy('d')->pluck('c','d');

        $resolvedRaw = Incident::selectRaw('DATE(resolved_at) d, COUNT(*) c')
            ->whereNotNull('resolved_at')
            ->where('resolved_at', '>=', now()->subDays($days)->startOfDay())
            ->groupBy('d')->orderBy('d')->pluck('c','d');

        $trendLabels   = $period->map(fn($d) => $d->format('d/m'))->values();
        $trendCreated  = $period->map(fn($d) => (int) ($createdRaw[$d->toDateString()]  ?? 0))->values();
        $trendResolved = $period->map(fn($d) => (int) ($resolvedRaw[$d->toDateString()] ?? 0))->values();

        // ⏱ Temps moyen de prise en charge par technicien (h) — 30j
        // Moyenne de TIMESTAMPDIFF(MINUTE, created_at, taken_at) / 60
        $avgPickupByTech = Incident::query()
            ->whereNotNull('taken_at')
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->whereNotNull('technicien_id')
            ->selectRaw('technicien_id, AVG(TIMESTAMPDIFF(MINUTE, created_at, taken_at)) as avg_min')
            ->groupBy('technicien_id')
            ->with('technicien:id,name')
            ->orderBy('avg_min')   // du plus rapide au plus lent
            ->limit(10)
            ->get();

        $avgTechLabels = $avgPickupByTech->map(fn($r) => optional($r->technicien)->name ?? '—')->values();
        $avgTechHours  = $avgPickupByTech->map(fn($r) => round(($r->avg_min ?? 0)/60, 1))->values();

        return view('dashboards.admin', compact(
            'incidentsOpen','slaAtRisk','resolved30','appsCount','usersCount',
            'priorites','byPriority','appLabels','appCounts',
            'trendLabels','trendCreated','trendResolved',
            'avgTechLabels','avgTechHours'
        ));
    }

    /**
     * Dashboard Superviseur
     */
    public function superviseur(Request $request)
    {
        $u = $request->user();
        $serviceId = $u->service_id; // si le superviseur est chef de service

        // KPI
        $openCount = Incident::when($serviceId, fn($q) => $q->where('service_id',$serviceId))
            ->whereNull('resolved_at')->count();

        $slaAtRisk = Incident::when($serviceId, fn($q) => $q->where('service_id',$serviceId))
            ->slaAtRisk()->count();

        // Réaffectations sur 30j (approx : MAJ avec technicien_id non nul)
        $reassign30 = Incident::when($serviceId, fn($q) => $q->where('service_id',$serviceId))
            ->whereNotNull('technicien_id')
            ->whereBetween('updated_at', [now()->subDays(30), now()])
            ->count();

        // Incidents par technicien (ouverts)
        $byTechRaw = Incident::selectRaw('technicien_id, COUNT(*) as c')
            ->when($serviceId, fn($q) => $q->where('service_id',$serviceId))
            ->whereNull('resolved_at')
            ->groupBy('technicien_id')
            ->orderByDesc('c')
            ->with('technicien:id,name')
            ->get();

        $techLabels = $byTechRaw->map(fn($r) => optional($r->technicien)->name ?? 'Non assigné')->values();
        $techCounts = $byTechRaw->pluck('c')->values();

        // SLA à risque (Top 10)
        $slaList = Incident::with(['application:id,nom','technicien:id,name'])
            ->when($serviceId, fn($q) => $q->where('service_id',$serviceId))
            ->slaAtRisk()
            ->orderBy('due_at')
            ->limit(10)
            ->get();

        // ⏱ Temps moyen de prise en charge (h) — 30j, filtré par service
        $avgPickupByTech = Incident::query()
            ->whereNotNull('taken_at')
            ->when($serviceId, fn($q) => $q->where('service_id',$serviceId))
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->whereNotNull('technicien_id')
            ->selectRaw('technicien_id, AVG(TIMESTAMPDIFF(MINUTE, created_at, taken_at)) as avg_min')
            ->groupBy('technicien_id')
            ->with('technicien:id,name')
            ->orderBy('avg_min')
            ->limit(10)
            ->get();

        $avgTechLabels = $avgPickupByTech->map(fn($r) => optional($r->technicien)->name ?? '—')->values();
        $avgTechHours  = $avgPickupByTech->map(fn($r) => round(($r->avg_min ?? 0)/60, 1))->values();

        return view('dashboards.superviseur', compact(
            'openCount','slaAtRisk','reassign30',
            'techLabels','techCounts','slaList',
            'avgTechLabels','avgTechHours'
        ));
    }

    /**
     * Dashboard Technicien (tel qu’implémenté chez toi)
     */
    public function technicien(Request $request)
    {
        $u = $request->user();

        $assignedOpen   = Incident::where('technicien_id',$u->id)->whereNull('resolved_at')->count();
        $resolvedByMe30 = Incident::where('technicien_id',$u->id)
                            ->whereNotNull('resolved_at')
                            ->whereBetween('resolved_at', [now()->subDays(30), now()])
                            ->count();
        $mySlaRisk      = Incident::where('technicien_id',$u->id)->slaAtRisk()->count();

        $myQueue = Incident::with(['application:id,nom','user:id,name'])
            ->where('technicien_id',$u->id)
            ->whereNull('resolved_at')
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->limit(10)
            ->get();

        $priorites = Incident::PRIORITES;
        $byPriorityMine = collect($priorites)->map(function($p) use ($u) {
            return Incident::where('technicien_id',$u->id)
                ->whereNull('resolved_at')
                ->where('priorite',$p)->count();
        })->values();

        return view('dashboards.technicien', compact(
            'assignedOpen','resolvedByMe30','mySlaRisk','myQueue',
            'priorites','byPriorityMine'
        ));
    }

    /**
     * Dashboard Utilisateur (demandeur) — tel qu’implémenté chez toi
     */
    public function utilisateur(Request $request)
    {
        $u = $request->user();

        $myOpen       = Incident::where('user_id',$u->id)->whereNull('resolved_at')->count();
        $myCritique   = Incident::where('user_id',$u->id)->whereNull('resolved_at')->where('priorite','Critique')->count();
        $myResolved30 = Incident::where('user_id',$u->id)
                            ->whereNotNull('resolved_at')
                            ->whereBetween('resolved_at',[now()->subDays(30), now()])
                            ->count();

        $aSuivre = Incident::with(['application:id,nom','technicien:id,name'])
            ->where('user_id',$u->id)->whereNull('resolved_at')
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->limit(10)
            ->get();

        $months = collect(range(5,0))->map(fn($i) => now()->subMonths($i)->startOfMonth());
        $labelsMonths = $months->map(fn($d) => $d->format('m/Y'))->values();

        $createdByMonthRaw = Incident::selectRaw('DATE_FORMAT(created_at, "%Y-%m") ym, COUNT(*) c')
            ->where('user_id', $u->id)
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('ym')->orderBy('ym')
            ->pluck('c','ym');

        $dataMonths = $months->map(function($d) use ($createdByMonthRaw){
            $key = $d->format('Y-m');
            return (int) ($createdByMonthRaw[$key] ?? 0);
        })->values();

        return view('dashboards.utilisateur', compact(
            'myOpen','myCritique','myResolved30','aSuivre',
            'labelsMonths','dataMonths'
        ));
    }
}
