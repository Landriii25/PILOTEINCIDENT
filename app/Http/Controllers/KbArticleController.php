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
        $this->middleware('can:kb.view')->only(['index','show']);
        $this->middleware('can:kb.create')->only(['create','store']);
        $this->middleware('can:kb.update')->only(['edit','update']);
        $this->middleware('can:kb.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $q = KbArticle::with('category:id,nom')->orderByDesc('created_at');

        if ($request->filled('q')) {
            $term = trim($request->q);
            $q->where(fn($qq)=>$qq->where('title','like',"%{$term}%")->orWhere('summary','like',"%{$term}%"));
        }
        if ($cid = $request->get('kb_category_id')) {
            $q->where('kb_category_id',$cid);
        }

        $articles = $q->paginate(10)->withQueryString();
        $categories = KbCategory::orderBy('position')->get(['id','nom']);

        return view('kb.index', compact('articles','categories'));
    }

    public function create()
    {
        $categories = KbCategory::orderBy('position')->get(['id','nom']);
        return view('kb.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kb_category_id' => ['required','exists:kb_categories,id'],
            'title'          => ['required','string','max:255'],
            'summary'        => ['nullable','string'],
            'content'        => ['nullable','string'],
            'is_published'   => ['boolean'],
        ]);

        $data['slug'] = Str::slug($data['title']).'-'.Str::random(4);

        KbArticle::create($data);

        return redirect()->route('kb.index')->with('success','Article créé.');
    }

    public function show(KbArticle $kb)
    {
        return view('kb.show', ['article'=>$kb->load('category:id,nom')]);
    }

    public function edit(KbArticle $kb)
    {
        $categories = KbCategory::orderBy('position')->get(['id','nom']);
        return view('kb.edit', ['article'=>$kb,'categories'=>$categories]);
    }

    public function update(Request $request, KbArticle $kb)
    {
        $data = $request->validate([
            'kb_category_id' => ['required','exists:kb_categories,id'],
            'title'          => ['required','string','max:255'],
            'summary'        => ['nullable','string'],
            'content'        => ['nullable','string'],
            'is_published'   => ['boolean'],
        ]);

        if ($kb->title !== $data['title']) {
            $data['slug'] = Str::slug($data['title']).'-'.Str::random(4);
        }

        $kb->update($data);

        return redirect()->route('kb.index')->with('success','Article mis à jour.');
    }

    public function destroy(KbArticle $kb)
    {
        $kb->delete();
        return back()->with('success','Article supprimé.');
    }
}
