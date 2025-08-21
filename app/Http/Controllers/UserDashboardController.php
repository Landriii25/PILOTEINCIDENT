<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $uid   = $user->id;
        $now   = now();
        $from30= $now->copy()->subDays(30);

        // KPIs (incidents créés par moi)
        $myOpen = Incident::where('user_id', $uid)->whereNull('resolved_at')->count();

        $resolved30Q = Incident::where('user_id', $uid)
            ->whereNotNull('resolved_at')
            ->whereBetween('resolved_at', [$from30, $now])
            ->get(['resolved_at','due_at','created_at']);

        $myResolved30 = $resolved30Q->count();
        $mySlaOk = $resolved30Q->filter(fn($i) => $i->resolved_at && $i->due_at && $i->resolved_at->lte($i->due_at))->count();
        $mySlaPercent = $myResolved30 ? (int) round(($mySlaOk / $myResolved30) * 100) : 100;

        $myCritiqueOpen = Incident::where('user_id', $uid)->whereNull('resolved_at')->where('priorite','Critique')->count();

        // Listes
        $aSuivre = Incident::with(['application:id,nom','technicien:id,name'])
            ->where('user_id',$uid)->whereNull('resolved_at')
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')->orderBy('due_at','asc')
            ->latest('created_at')->limit(10)->get();

        $mesRecents = Incident::with(['application:id,nom','technicien:id,name'])
            ->where('user_id',$uid)->latest()->limit(10)->get();

        // Donut (mes ouverts)
        $priorityLabels = ['Critique','Haute','Moyenne','Basse'];
        $priorityData = collect($priorityLabels)->map(fn($p) =>
            Incident::where('user_id',$uid)->whereNull('resolved_at')->where('priorite',$p)->count()
        );

        // Si technicien : tickets assignés à moi
        $isTech = method_exists($user,'hasRole') ? $user->hasRole('technicien') : false;
        $assignedOpen = 0; $assignedList = collect();
        if ($isTech) {
            $assignedOpen = Incident::where('technicien_id',$uid)->whereNull('resolved_at')->count();
            $assignedList = Incident::with(['application:id,nom','user:id,name'])
                ->where('technicien_id',$uid)->whereNull('resolved_at')
                ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')->orderBy('due_at','asc')
                ->limit(10)->get();
        }

        // Graphe mensuel (12 derniers mois) – incidents créés par moi
        $start = now()->startOfMonth()->subMonths(11);
        $rawMonthly = Incident::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COUNT(*) as cnt')
            ->where('user_id',$uid)
            ->where('created_at', '>=', $start)
            ->groupBy('ym')->orderBy('ym')->pluck('cnt','ym');

        $months = collect(range(0,11))->map(fn($i) => $start->copy()->addMonths($i));
        $monthlyLabels = $months->map(fn($d) => $d->isoFormat('MMM YYYY'));
        $monthlyData   = $months->map(fn($d) => (int)($rawMonthly[$d->format('Y-m')] ?? 0));

        return view('user.dashboard', compact(
            'myOpen','myResolved30','mySlaPercent','myCritiqueOpen',
            'aSuivre','mesRecents',
            'priorityLabels','priorityData',
            'isTech','assignedOpen','assignedList',
            'monthlyLabels','monthlyData'
        ));
    }
}
