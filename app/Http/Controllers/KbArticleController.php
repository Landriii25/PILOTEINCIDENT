<?php

namespace App\Http\Controllers;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KbArticleController extends Controller
{
    public function __construct()
    {
        // Protège les actions par permissions (Spatie) — adapte si besoin
        $this->middleware('can:kb.view')->only(['index','show']);
        $this->middleware('can:kb.create')->only(['create','store']);
        $this->middleware('can:kb.update')->only(['edit','update']);
        $this->middleware('can:kb.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $q = KbArticle::query()
            ->with('category')
            ->when($request->filled('cat'), fn($qq) => $qq->where('kb_category_id', $request->integer('cat')))
            ->when($request->filled('s'), function ($qq) use ($request) {
                $s = $request->string('s');
                $qq->where(function ($w) use ($s) {
                    $w->where('title', 'like', "%{$s}%")
                      ->orWhere('summary', 'like', "%{$s}%")
                      ->orWhere('content', 'like', "%{$s}%");
                });
            })
            ->orderByDesc('created_at');

        $articles   = $q->paginate(10)->withQueryString();
        $categories = KbCategory::orderBy('position')->get();

        return view('kb.index', compact('articles', 'categories'));
    }

    public function create()
    {
        $categories = KbCategory::orderBy('position')->get();
        return view('kb.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kb_category_id' => ['nullable','exists:kb_categories,id'],
            'title'          => ['required','string','max:255'],
            'summary'        => ['nullable','string'],
            'content'        => ['nullable','string'],
            'tags'           => ['nullable','array'],
            'is_published'   => ['sometimes','boolean'],
        ]);

        $data['slug'] = Str::slug($data['title']).'-'.Str::random(4);
        if (isset($data['tags'])) {
            $data['tags'] = array_values(array_filter($data['tags']));
        }
        $data['is_published'] = (bool)($data['is_published'] ?? true);

        $article = KbArticle::create($data);

        return redirect()->route('kb.show', $article)->with('success', 'Article créé.');
    }

    public function show(KbArticle $kb)
    {
        // Incrémente les vues
        $kb->increment('views');
        $kb->load('category');

        // articles liés (même catégorie)
        $related = KbArticle::where('kb_category_id', $kb->kb_category_id)
            ->where('id', '!=', $kb->id)
            ->latest()
            ->take(5)->get();

        return view('kb.show', compact('kb','related'));
    }

    public function edit(KbArticle $kb)
    {
        $categories = KbCategory::orderBy('position')->get();
        return view('kb.edit', compact('kb','categories'));
    }

    public function update(Request $request, KbArticle $kb)
    {
        $data = $request->validate([
            'kb_category_id' => ['nullable','exists:kb_categories,id'],
            'title'          => ['required','string','max:255'],
            'summary'        => ['nullable','string'],
            'content'        => ['nullable','string'],
            'tags'           => ['nullable','array'],
            'is_published'   => ['sometimes','boolean'],
        ]);

        if ($kb->title !== $data['title']) {
            $data['slug'] = Str::slug($data['title']).'-'.Str::random(4);
        }

        if (isset($data['tags'])) {
            $data['tags'] = array_values(array_filter($data['tags']));
        }

        $kb->update($data);

        return redirect()->route('kb.show', $kb)->with('success', 'Article mis à jour.');
    }

    public function destroy(KbArticle $kb)
    {
        $kb->delete();
        return redirect()->route('kb.index')->with('success', 'Article supprimé.');
    }
}
