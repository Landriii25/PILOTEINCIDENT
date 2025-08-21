<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Intervention Image v3 (driver GD). Si tu utilises Imagick, remplace Gd\Driver par Imagick\Driver.
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class ApplicationController extends Controller
{
    public function __construct()
    {
        // Auth obligatoire partout
        $this->middleware('auth');

        // Permissions (Spatie). Lecture libre pour les authentifiés (gérée par les vues/policies).
        $this->middleware('can:applications.create')->only(['create', 'store']);
        $this->middleware('can:applications.update')->only(['edit', 'update']);
        $this->middleware('can:applications.delete')->only(['destroy']);
    }

    /**
     * Liste (table).
     */
    public function index()
    {
        // Astuce : eager load service si la relation existe (belongsTo Service dans le modèle Application)
        $applications = Application::with('service')->latest()->paginate(12);

        return view('applications.index', compact('applications'));
    }

    /**
     * Galerie (cartes).
     */
    public function gallery()
    {
        $applications = \App\Models\Application::with('service')->latest()->paginate(12);

        return view('applications.gallery', compact('applications'));

    }

    /**
     * Détails d’une application.
     */
    public function show(Application $application)
    {
        $application->load('service');

        return view('applications.show', compact('application'));
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        $services = Service::orderBy('nom')->get();

        return view('applications.create', compact('services'));
    }

    /**
     * Enregistrement.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'         => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'service_id'  => ['nullable', 'exists:services,id'],
            'statut'      => ['required', 'in:Actif,En maintenance,Retirée'],
            'logo'        => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $paths = $this->processLogoUpload($request->file('logo')); // ✅ on passe bien le fichier
            $data['logo_url']  = $paths['logo'];
            $data['thumb_url'] = $paths['thumb'];
        }

        Application::create($data);

        return redirect()
            ->route('applications.index')
            ->with('success', 'Application créée avec succès.');
    }

    /**
     * Formulaire d’édition.
     */
    public function edit(Application $application)
    {
        $services = Service::orderBy('nom')->get();

        return view('applications.edit', compact('application', 'services'));
    }

    /**
     * Mise à jour.
     */
    public function update(Request $request, Application $application)
    {
        $data = $request->validate([
            'nom'         => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'service_id'  => ['nullable', 'exists:services,id'],
            'statut'      => ['required', 'in:Actif,En maintenance,Retirée'],
            'logo'        => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            // nettoyage anciens fichiers si présents
            if ($application->logo_url) {
                Storage::disk('public')->delete($application->logo_url);
            }
            if ($application->thumb_url) {
                Storage::disk('public')->delete($application->thumb_url);
            }

            $paths = $this->processLogoUpload($request->file('logo')); // ✅
            $data['logo_url']  = $paths['logo'];
            $data['thumb_url'] = $paths['thumb'];
        }

        $application->update($data);

        return redirect()
            ->route('applications.index')
            ->with('success', 'Application mise à jour.');
    }

    /**
     * Suppression.
     */
    public function destroy(Application $application)
    {
        if ($application->logo_url) {
            Storage::disk('public')->delete($application->logo_url);
        }
        if ($application->thumb_url) {
            Storage::disk('public')->delete($application->thumb_url);
        }

        $application->delete();

        return redirect()
            ->route('applications.index')
            ->with('success', 'Application supprimée.');
    }

    /**
     * Upload + miniature 256x256 (v3 -> encode($format) OBLIGATOIRE)
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return array{logo:string, thumb:string}
     */
    protected function processLogoUpload(UploadedFile $file): array
    {
        $manager = new ImageManager(new GdDriver()); // ou new \Intervention\Image\Drivers\Imagick\Driver()

        $baseDir  = 'applications/logos';
        $thumbDir = 'applications/thumbs';

        $ext       = strtolower($file->getClientOriginalExtension() ?: 'png'); // png par défaut
        $basename  = uniqid('app_', true);
        $logoName  = $basename . '.' . $ext;
        $thumbName = $basename . '_thumb.' . $ext;

        // Lire l’image source
        $image = $manager->read($file->getRealPath());

        // Sauvegarder l’original (dans le format d’entrée)
        Storage::disk('public')->put(
            $baseDir . '/' . $logoName,
            (string) $image->encode($ext) // ✅ Intervention v3 requiert un format (png/jpg/webp…)
        );

        // Miniature 256x256 crop centré
        $thumb = $image->cover(256, 256);

        Storage::disk('public')->put(
            $thumbDir . '/' . $thumbName,
            (string) $thumb->encode($ext) // ✅ idem
        );

        return [
            'logo'  => $baseDir . '/' . $logoName,
            'thumb' => $thumbDir . '/' . $thumbName,
        ];
    }
}
