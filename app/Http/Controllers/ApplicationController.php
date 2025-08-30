<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ApplicationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:applications.create')->only(['create','store']);
        $this->middleware('can:applications.update')->only(['edit','update']);
        $this->middleware('can:applications.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $q = Application::with('service:id,nom')->orderBy('nom');

        if ($request->filled('q')) {
            $term = trim($request->q);
            $q->where('nom','like',"%{$term}%");
        }
        if ($sid = $request->get('service_id')) {
            $q->where('service_id',$sid);
        }

        $applications = $q->paginate(12)->withQueryString();   // ⬅ nom aligné avec la vue
        $services     = Service::orderBy('nom')->get(['id','nom']);

        return view('applications.index', compact('applications','services'));
    }

    public function gallery(Request $request)
    {
        $applications = Application::with('service:id,nom')
            ->orderBy('nom')
            ->paginate(12);

        return view('applications.gallery', compact('applications'));
    }

    public function create()
    {
        $services = Service::orderBy('nom')->get(['id','nom']);
        return view('applications.create', compact('services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'         => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'statut'      => ['required','in:Actif,En maintenance,Retirée'],
            'service_id'  => ['nullable','exists:services,id'],
            'logo'        => ['nullable','image','max:2048'],
        ]);

        $app = Application::create($data);

        if ($request->hasFile('logo')) {
            $this->handleLogo($app, $request->file('logo'));
        }

        return redirect()->route('applications.index')->with('success','Application créée.');
    }

    public function show(Application $application)
    {
        $application->load('service:id,nom');
        return view('applications.show', compact('application'));
    }

    public function edit(Application $application)
    {
        $services = Service::orderBy('nom')->get(['id','nom']);
        return view('applications.edit', compact('application','services'));
    }

    public function update(Request $request, Application $application)
    {
        $data = $request->validate([
            'nom'         => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'statut'      => ['required','in:Actif,En maintenance,Retirée'],
            'service_id'  => ['nullable','exists:services,id'],
            'logo'        => ['nullable','image','max:2048'],
        ]);

        $application->update($data);

        if ($request->hasFile('logo')) {
            $this->handleLogo($application, $request->file('logo'));
        }

        return redirect()->route('applications.index')->with('success','Application mise à jour.');
    }

    public function destroy(Application $application)
    {
        if ($application->logo_path) {
            Storage::disk('public')->delete([$application->logo_path, $application->thumb_path]);
        }
        $application->delete();
        return back()->with('success','Application supprimée.');
    }

    protected function handleLogo(Application $app, \Illuminate\Http\UploadedFile $file): void
    {
        $dir = 'applications/logos/'.$app->id;

        $image = Image::read($file->getRealPath());
        $image->cover(512,512);
        $originalPath = "{$dir}/logo-512.png";
        Storage::disk('public')->put($originalPath, (string) $image->encode('png'));

        $thumb = $image->scaleDown(width:128, height:128);
        $thumbPath = "{$dir}/thumb-128.png";
        Storage::disk('public')->put($thumbPath, (string) $thumb->encode('png'));

        $app->update([
            'logo_path'  => $originalPath,
            'thumb_path' => $thumbPath,
        ]);
    }

    public function serviceTechniciens(Application $application)
    {
        $service = $application->service()->select('id','nom')->first();
        $techs   = $service
            ? $service->techniciens()->select('id','name')->orderBy('name')->get()
            : collect();

        return response()->json([
            'service'     => $service,
            'techniciens' => $techs,
        ]);
    }
}
