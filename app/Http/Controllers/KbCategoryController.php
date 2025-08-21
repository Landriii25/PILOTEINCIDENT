<?php

namespace App\Http\Controllers;

use App\Models\KbCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class KbCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:kb.view')->only(['index']);
        $this->middleware('can:kb.categories.manage')->only(['create','store','edit','update','destroy']);
    }

    public function index()
    {
        $categories = KbCategory::withCount('articles')
            ->orderBy('position')
            ->orderBy('nom')
            ->paginate(12);

        return view('kb.categories', compact('categories'));
    }

    public function create()
    {
        return view('kb.categories.create', [
            'category' => new KbCategory(['position' => 0]),
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nom'         => ['required','string','max:255'],
            'slug'        => ['nullable','string','max:255','unique:kb_categories,slug'],
            'description' => ['nullable','string','max:2000'],
            'position'    => ['nullable','integer','min:0'],
        ]);

        if (empty($data['slug'])) {
            $baseSlug = Str::slug($data['nom']);
            $slug = $baseSlug;
            $i = 1;
            while (KbCategory::where('slug',$slug)->exists()) {
                $slug = $baseSlug.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        $data['position'] = $data['position'] ?? 0;

        KbCategory::create($data);

        return redirect()->route('kb.categories')->with('success','Catégorie créée.');
    }

    public function edit(KbCategory $kbCategory)
    {
        return view('kb.categories.edit', ['category'=>$kbCategory]);
    }

    public function update(Request $r, KbCategory $kbCategory)
    {
        $data = $r->validate([
            'nom'         => ['required','string','max:255'],
            'slug'        => [
                'nullable','string','max:255',
                Rule::unique('kb_categories','slug')->ignore($kbCategory->id),
            ],
            'description' => ['nullable','string','max:2000'],
            'position'    => ['nullable','integer','min:0'],
        ]);

        if (empty($data['slug'])) {
            $baseSlug = Str::slug($data['nom']);
            $slug = $baseSlug;
            $i = 1;
            while (KbCategory::where('slug',$slug)->where('id','!=',$kbCategory->id)->exists()) {
                $slug = $baseSlug.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        $data['position'] = $data['position'] ?? 0;

        $kbCategory->update($data);

        return redirect()->route('kb.categories')->with('success','Catégorie mise à jour.');
    }

    public function destroy(KbCategory $kbCategory)
    {
        // (Option) empêcher la suppression si articles > 0
        // if ($kbCategory->articles()->exists()) {
        //    return back()->with('error','Catégorie non vide.');
        // }

        $kbCategory->delete();
        return redirect()->route('kb.categories')->with('success','Catégorie supprimée.');
    }
}
