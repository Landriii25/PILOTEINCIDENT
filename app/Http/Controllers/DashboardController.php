<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\Application;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']); // une seule entrée protégée
    }

    /**
     * Route unique /dashboard
     * Renvoie la vue selon le rôle courant (admin, superviseur, technicien, utilisateur).
     */
    public function index(Request $request)
    {
        $u = $request->user();

        // ==============================
        // ADMIN
        // ==============================
        if ($u->hasRole('admin')) {

            // KPI
            $incidentsOpen = Incident::whereNull('resolved_at')->count();
            $slaAtRisk     = Incident::slaAtRisk()->count();
            $resolved30    = Incident::whereNotNull('resolved_at')
                ->whereBetween('resolved_at', [now()->subDays(30), now()])
                ->count();
            $appsCount     = Application::count();
            $usersCount    = User::count();

            // Graph 1 : Incidents par priorité (ordre fixe)
            $priorites  = Incident::PRIORITES;
            $byPriority = collect($priorites)->map(
                fn ($p) => Incident::where('priorite', $p)->count()
            )->values();

            // Graph 2 : Incidents par application (Top 10)
            $byAppRaw = Incident::selectRaw('application_id, COUNT(*) AS c')
                ->groupBy('application_id')
                ->orderByDesc('c')
                ->limit(10)
                ->with('application:id,nom')
                ->get();

            $appLabels = $byAppRaw->map(fn ($r) => optional($r->application)->nom ?? '—')->values();
            $appCounts = $byAppRaw->pluck('c')->values();

            // Tendance 30 jours
            $days   = 30;
            $period = collect(range($days - 1, 0))->map(fn ($i) => now()->subDays($i)->startOfDay());

            $createdRaw = Incident::selectRaw('DATE(created_at) d, COUNT(*) c')
                ->where('created_at', '>=', now()->subDays($days)->startOfDay())
                ->groupBy('d')->orderBy('d')->pluck('c', 'd');

            $resolvedRaw = Incident::selectRaw('DATE(resolved_at) d, COUNT(*) c')
                ->whereNotNull('resolved_at')
                ->where('resolved_at', '>=', now()->subDays($days)->startOfDay())
                ->groupBy('d')->orderBy('d')->pluck('c', 'd');

            $trendLabels   = $period->map(fn ($d) => $d->format('d/m'))->values();
            $trendCreated  = $period->map(fn ($d) => (int)($createdRaw[$d->toDateString()] ?? 0))->values();
            $trendResolved = $period->map(fn ($d) => (int)($resolvedRaw[$d->toDateString()] ?? 0))->values();

            // (Optionnel) temps moyen de prise en charge par technicien sur 30j
            $pickupWindowStart = now()->subDays(30)->startOfDay();
            $avgPickupByTech = Incident::whereNotNull('technicien_id')
                ->where('created_at', '>=', $pickupWindowStart)
                ->selectRaw('technicien_id, AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) AS avg_min')
                ->groupBy('technicien_id')
                ->with('technicien:id,name')
                ->get();

            $avgPickupLabels = $avgPickupByTech->map(fn ($r) => optional($r->technicien)->name ?? '—')->values();
            $avgPickupData   = $avgPickupByTech->map(fn ($r) => round(($r->avg_min ?? 0) / 60, 1))->values(); // en heures

            return view('dashboards.admin', compact(
                'incidentsOpen','slaAtRisk','resolved30','appsCount','usersCount',
                'priorites','byPriority','appLabels','appCounts',
                'trendLabels','trendCreated','trendResolved',
                'avgPickupLabels','avgPickupData'
            ));
        }

        // ==============================
        // SUPERVISEUR
        // ==============================
        if ($u->hasRole('superviseur')) {

            $openCount = Incident::whereNull('resolved_at')->count();
            $slaAtRisk = Incident::slaAtRisk()->count();

            // Réaffectations “approx” 30j (exemple)
            $reassign30 = Incident::whereNotNull('technicien_id')
                ->whereBetween('updated_at', [now()->subDays(30), now()])
                ->count();

            // Incidents par technicien (ouverts)
            $byTechRaw = Incident::selectRaw('technicien_id, COUNT(*) AS c')
                ->whereNull('resolved_at')
                ->groupBy('technicien_id')
                ->orderByDesc('c')
                ->with('technicien:id,name')
                ->get();

            $techLabels = $byTechRaw->map(fn ($r) => optional($r->technicien)->name ?? 'Non assigné')->values();
            $techCounts = $byTechRaw->pluck('c')->values();

            // SLA à risque (Top 10)
            $slaList = Incident::with(['application:id,nom','technicien:id,name'])
                ->slaAtRisk()
                ->orderBy('due_at')
                ->limit(10)
                ->get()
                ->map(function ($i) {
                    $i->is_late = $i->due_at && $i->due_at->isPast();
                    return $i;
                });

            return view('dashboards.superviseur', compact(
                'openCount','slaAtRisk','reassign30',
                'techLabels','techCounts','slaList'
            ));
        }

        // ==============================
        // TECHNICIEN
        // ==============================
        if ($u->hasRole('technicien')) {

            $assignedOpen   = Incident::where('technicien_id', $u->id)->whereNull('resolved_at')->count();
            $resolvedByMe30 = Incident::where('technicien_id', $u->id)
                ->whereNotNull('resolved_at')
                ->whereBetween('resolved_at', [now()->subDays(30), now()])
                ->count();
            $mySlaRisk      = Incident::where('technicien_id', $u->id)->slaAtRisk()->count();

            // File priorisée — pagination 5
            $myQueue = Incident::with(['application:id,nom','user:id,name'])
                ->where('technicien_id', $u->id)
                ->whereNull('resolved_at')
                ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
                ->orderBy('due_at')
                ->paginate(5)
                ->withQueryString();

            // Graphe : mes incidents par priorité
            $priorites = Incident::PRIORITES;
            $byPriorityMine = collect($priorites)->map(function ($p) use ($u) {
                return Incident::where('technicien_id', $u->id)
                    ->whereNull('resolved_at')
                    ->where('priorite', $p)->count();
            })->values();

            return view('dashboards.technicien', compact(
                'assignedOpen','resolvedByMe30','mySlaRisk','myQueue',
                'priorites','byPriorityMine'
            ));
        }

        // ==============================
        // UTILISATEUR (demandeur)
        // ==============================
        // Par défaut si aucun autre rôle
        // KPI
        $myOpen       = Incident::where('user_id', $u->id)->whereNull('resolved_at')->count();
        $myCritique   = Incident::where('user_id', $u->id)->whereNull('resolved_at')->where('priorite','Critique')->count();
        $myResolved30 = Incident::where('user_id', $u->id)
            ->whereNotNull('resolved_at')
            ->whereBetween('resolved_at', [now()->subDays(30), now()])
            ->count();

        // À suivre (10 plus urgents)
        $aSuivre = Incident::with(['application:id,nom','technicien:id,name'])
            ->where('user_id', $u->id)->whereNull('resolved_at')
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->paginate(10);
            //->get();

        // Graphe barres : incidents créés par mois (6 derniers mois)
        $months       = collect(range(5,0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());
        $labelsMonths = $months->map(fn ($d) => $d->format('m/Y'))->values();

        $createdByMonthRaw = Incident::selectRaw('DATE_FORMAT(created_at, "%Y-%m") ym, COUNT(*) c')
            ->where('user_id', $u->id)
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('ym')->orderBy('ym')
            ->pluck('c','ym');

        $dataMonths = $months->map(function ($d) use ($createdByMonthRaw) {
            $key = $d->format('Y-m');
            return (int)($createdByMonthRaw[$key] ?? 0);
        })->values();

        return view('dashboards.utilisateur', compact(
            'myOpen','myCritique','myResolved30','aSuivre',
            'labelsMonths','dataMonths'
        ));
    }
}
