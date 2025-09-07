<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    ProfileController,
    IncidentController,
    ApplicationController,
    KbArticleController,
    KbCategoryController,
    ReportController,
    SettingController,
    UserController,
    RoleController,
    NotificationController,
    ServiceController
};

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {

    // --------------------- Dashboards ---------------------
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');



    // --------------------- Profil -------------------------
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    // --------------------- Incidents ----------------------
    Route::resource('incidents', IncidentController::class);
    Route::get('mes-incidents', [IncidentController::class, 'mine'])->name('incidents.mine');
    Route::get('incidents-sla', [IncidentController::class, 'slaAtRisk'])->name('incidents.sla');

    Route::post('incidents/{incident}/commenter', [IncidentController::class, 'commenter'])->name('incidents.commenter');
    Route::post('incidents/{incident}/comments', [IncidentController::class, 'commenter'])->name('incidents.comments.store');

    Route::put('incidents/{incident}/resolve',        [IncidentController::class, 'resolve'])->name('incidents.resolve');
    Route::put('incidents/{incident}/close',          [IncidentController::class, 'close'])->name('incidents.close');
    Route::put('incidents/{incident}/reopen-to-tech', [IncidentController::class, 'reopenToSameTech'])->name('incidents.reopen_to_tech');
    Route::get('/incidents/export',                   [IncidentController::class, 'export'])->name('incidents.export')->middleware('auth');

    // --------------------- Applications -------------------
    Route::get('applications/galerie', [ApplicationController::class, 'gallery'])->name('applications.gallery');
    Route::resource('applications', ApplicationController::class);
    // AJAX: récupérer service + techniciens d’une application
    Route::get('applications/{application}/service-techniciens', [ApplicationController::class, 'serviceTechniciens'])
        ->name('applications.service_techniciens');

    // --------------------- Base de connaissances ----------
    // --------------------- Base de connaissances (CATEGORIES D’ABORD) ----------
    Route::get   ('kb/categories',                   [KbCategoryController::class, 'index'])->name('kb.categories');
    Route::get   ('kb/categories/create',            [KbCategoryController::class, 'create'])->name('kb.categories.create');
    Route::post  ('kb/categories',                   [KbCategoryController::class, 'store'])->name('kb.categories.store');
    Route::get   ('kb/categories/{kbCategory}',      [KbCategoryController::class, 'show'])->name('kb.categories.show');
    Route::get   ('kb/categories/{kbCategory}/edit', [KbCategoryController::class, 'edit'])->name('kb.categories.edit');
    Route::put   ('kb/categories/{kbCategory}',      [KbCategoryController::class, 'update'])->name('kb.categories.update');
    Route::delete('kb/categories/{kbCategory}',      [KbCategoryController::class, 'destroy'])->name('kb.categories.destroy');

    // Ensuite seulement, les articles
    Route::resource('kb', KbArticleController::class);


    // --------------------- Rapports -----------------------
    Route::get('rapports/incidents-par-appli', [ReportController::class, 'byApp'])->name('reports.byapp');
    Route::get('rapports/sla',                 [ReportController::class, 'sla'])->name('reports.sla');
    Route::get('rapports/techniciens',         [ReportController::class, 'technicians'])->name('reports.technicians');

    // Rapport d’intervention lié à un incident (noms stables)
    Route::get ('incidents/{incident}/report/create', [ReportController::class, 'createForIncident'])
        ->name('reports.create_for_incident');
    Route::post('incidents/{incident}/report',        [ReportController::class, 'storeForIncident'])
        ->name('reports.store_for_incident');

    // Alias “compat” si tes vues utilisent ces noms plus anciens :
    Route::get ('incidents/{incident}/rapport/create', [ReportController::class, 'createForIncident'])
        ->name('incidents.report.create');
    Route::post('incidents/{incident}/rapport',        [ReportController::class, 'storeForIncident'])
        ->name('incidents.report.store');

    // Show / Edit / Update d’un report
    Route::get('reports/{report}',        [ReportController::class, 'show'])->name('reports.show');
    Route::get('reports/{report}/edit',   [ReportController::class, 'edit'])->name('reports.edit');
    Route::put('reports/{report}',        [ReportController::class, 'update'])->name('reports.update');

    // --------------------- Administration -----------------
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class)->only(['index','create','store','edit','update','destroy']);

    // --------------------- Paramètres ---------------------
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');

    // --------------------- Notifications ------------------
    Route::get ('/notifications',           [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all',  [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get ('/notifications/go/{id}',   [NotificationController::class, 'go'])->name('notifications.go');

    // --------------------- Services -----------------------
    Route::resource('services', ServiceController::class);
    Route::get('services/{service}/techniciens', [ServiceController::class, 'technicians'])->name('services.technicians');
    Route::get('/services/by-application/{application}', [ServiceController::class, 'getByApplication'])->name('services.by_application');
});

// Auth (Breeze)
require __DIR__.'/auth.php';
