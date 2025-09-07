<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\DB; // N'oubliez pas d'importer la façade DB

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

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
            $resolved30    = Incident::whereNotNull('resolved_at')->whereBetween('resolved_at', [now()->subDays(30), now()])->count();
            $appsCount     = Application::count();
            $usersCount    = User::count();

            // Graph 1 : Incidents par priorité
            $priorites  = Incident::PRIORITES;
            $byPriority = collect($priorites)->map(fn ($p) => Incident::where('priorite', $p)->count())->values();

            // Graph 2 : Incidents par application (Top 10)
            $byAppRaw = Incident::selectRaw('application_id, COUNT(*) AS c')->groupBy('application_id')->orderByDesc('c')->limit(10)->with('application:id,nom')->get();
            $appLabels = $byAppRaw->map(fn ($r) => optional($r->application)->nom ?? '—')->values();
            $appCounts = $byAppRaw->pluck('c')->values();

            // Tendance 30 jours
            $days   = 30;
            $period = collect(range($days - 1, 0))->map(fn ($i) => now()->subDays($i)->startOfDay());
            $createdRaw = Incident::selectRaw('DATE(created_at) d, COUNT(*) c')->where('created_at', '>=', now()->subDays($days)->startOfDay())->groupBy('d')->orderBy('d')->pluck('c', 'd');
            $resolvedRaw = Incident::selectRaw('DATE(resolved_at) d, COUNT(*) c')->whereNotNull('resolved_at')->where('resolved_at', '>=', now()->subDays($days)->startOfDay())->groupBy('d')->orderBy('d')->pluck('c', 'd');
            $trendLabels   = $period->map(fn ($d) => $d->format('d/m'))->values();
            $trendCreated  = $period->map(fn ($d) => (int)($createdRaw[$d->toDateString()] ?? 0))->values();
            $trendResolved = $period->map(fn ($d) => (int)($resolvedRaw[$d->toDateString()] ?? 0))->values();

            // Temps moyen de prise en charge (MTTA) pour TOUS les techniciens
            $avgPickupByTech = $this->getMttaStats();
            $avgPickupLabels = $avgPickupByTech->pluck('technicien_nom');
            $avgPickupData   = $avgPickupByTech->pluck('temps_moyen_minutes')->map(fn ($minutes) => round($minutes / 60, 1));

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
            $reassign30 = Incident::whereNotNull('technicien_id')->whereBetween('updated_at', [now()->subDays(30), now()])->count();

            // Incidents par technicien (ouverts)
            $byTechRaw = Incident::selectRaw('technicien_id, COUNT(*) AS c')->whereNull('resolved_at')->groupBy('technicien_id')->orderByDesc('c')->with('technicien:id,name')->get();
            $techLabels = $byTechRaw->map(fn ($r) => optional($r->technicien)->name ?? 'Non assigné')->values();
            $techCounts = $byTechRaw->pluck('c')->values();

            // SLA à risque (Top 10)
            $slaList = Incident::with(['application:id,nom','technicien:id,name'])->slaAtRisk()->orderBy('due_at')->limit(10)->get()->map(function ($i) {
                $i->is_late = $i->due_at && $i->due_at->isPast();
                return $i;
            });

            // Temps moyen de prise en charge (MTTA) UNIQUEMENT pour les techniciens du service du superviseur
            $avgPickupByTech = $this->getMttaStats($u->service_id);
            $avgPickupLabels = $avgPickupByTech->pluck('technicien_nom');
            $avgPickupData   = $avgPickupByTech->pluck('temps_moyen_minutes')->map(fn ($minutes) => round($minutes / 60, 1));

            return view('dashboards.superviseur', compact(
                'openCount','slaAtRisk','reassign30',
                'techLabels','techCounts','slaList',
                'avgPickupLabels','avgPickupData'
            ));
        }

        // ==============================
        // TECHNICIEN
        // ==============================
        if ($u->hasRole('technicien')) {

            $assignedOpen   = Incident::where('technicien_id', $u->id)->whereNull('resolved_at')->count();
            $resolvedByMe30 = Incident::where('technicien_id', $u->id)->whereNotNull('resolved_at')->whereBetween('resolved_at', [now()->subDays(30), now()])->count();
            $mySlaRisk      = Incident::where('technicien_id', $u->id)->slaAtRisk()->count();
            $myQueue = Incident::with(['application:id,nom','user:id,name'])->where('technicien_id', $u->id)->whereNull('resolved_at')->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')->orderBy('due_at')->paginate(5)->withQueryString();
            $priorites = Incident::PRIORITES;
            $byPriorityMine = collect($priorites)->map(function ($p) use ($u) {
                return Incident::where('technicien_id', $u->id)->whereNull('resolved_at')->where('priorite', $p)->count();
            })->values();

            return view('dashboards.technicien', compact(
                'assignedOpen','resolvedByMe30','mySlaRisk','myQueue',
                'priorites','byPriorityMine'
            ));
        }

        // ==============================
        // UTILISATEUR (demandeur) par défaut
        // ==============================
        $myOpen       = Incident::where('user_id', $u->id)->whereNull('resolved_at')->count();
        $myCritique   = Incident::where('user_id', $u->id)->whereNull('resolved_at')->where('priorite','Critique')->count();
        $myResolved30 = Incident::where('user_id', $u->id)->whereNotNull('resolved_at')->whereBetween('resolved_at', [now()->subDays(30), now()])->count();
        $aSuivre = Incident::with(['application:id,nom','technicien:id,name'])->where('user_id', $u->id)->whereNull('resolved_at')->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')->orderBy('due_at')->paginate(10);
        $months       = collect(range(5,0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());
        $labelsMonths = $months->map(fn ($d) => $d->format('m/Y'))->values();
        $createdByMonthRaw = Incident::selectRaw('DATE_FORMAT(created_at, "%Y-%m") ym, COUNT(*) c')->where('user_id', $u->id)->where('created_at', '>=', now()->subMonths(5)->startOfMonth())->groupBy('ym')->orderBy('ym')->pluck('c','ym');
        $dataMonths = $months->map(function ($d) use ($createdByMonthRaw) {
            $key = $d->format('Y-m');
            return (int)($createdByMonthRaw[$key] ?? 0);
        })->values();

        return view('dashboards.utilisateur', compact(
            'myOpen','myCritique','myResolved30','aSuivre',
            'labelsMonths','dataMonths'
        ));
    }

    /**
     * Méthode privée pour calculer le MTTA afin d'éviter la duplication de code.
     * Elle accepte un ID de service pour filtrer les résultats.
     */
    private function getMttaStats($serviceId = null)
    {
        $pickupWindowStart = now()->subDays(30)->startOfDay();

        $query = Incident::join('users as technicien', 'incidents.technicien_id', '=', 'technicien.id')
            ->where('incidents.created_at', '>=', $pickupWindowStart)
            ->whereNotNull('incidents.acknowledged_at')
            ->selectRaw('
                technicien.name as technicien_nom,
                AVG(TIMESTAMPDIFF(MINUTE, incidents.created_at, incidents.acknowledged_at)) as temps_moyen_minutes
            ');

        // Si un ID de service est fourni (pour le superviseur), on ajoute le filtre.
        if ($serviceId) {
            $query->where('technicien.service_id', $serviceId);
        }

        return $query->groupBy('technicien.id', 'technicien.name')->get();
    }
}
