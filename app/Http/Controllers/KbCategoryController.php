<?php

namespace App\Http\Controllers;

use App\Models\KbCategory;
use Illuminate\Http\Request;

class KbCategoryController extends Controller
{
    public function __construct()
    {
        // Politiques d’accès (tu peux les ajuster selon tes rôles)
        $this->middleware('can:kb.categories.manage')->except(['index','show']);
    }

    /**
     * Liste des catégories
     */
    public function index()
    {
        $categories = KbCategory::withCount('articles')
            ->orderBy('position')
            ->get();

        return view('kb.categories.index', compact('categories'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('kb.categories.create');
    }

    /**
     * Enregistrement d’une nouvelle catégorie
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'         => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'position'    => ['nullable','integer'],
        ]);

        KbCategory::create($data);

        return redirect()->route('kb.categories')
            ->with('success','Catégorie créée avec succès.');
    }

    /**
     * Afficher les articles d’une catégorie
     */
    public function show(KbCategory $kbCategory)
    {
        $articles = $kbCategory->articles()
            ->latest()
            ->paginate(10);

        return view('kb.categories.show', [
            'category' => $kbCategory,
            'articles' => $articles,
        ]);
    }

    /**
     * Formulaire d’édition
     */
    public function edit(KbCategory $kbCategory)
    {
        return view('kb.categories.edit', ['category' => $kbCategory]);
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, KbCategory $kbCategory)
    {
        $data = $request->validate([
            'nom'         => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'position'    => ['nullable','integer'],
        ]);

        $kbCategory->update($data);

        return redirect()->route('kb.categories')
            ->with('success','Catégorie mise à jour.');
    }

    /**
     * Suppression
     */
    public function destroy(KbCategory $kbCategory)
    {
        // ⚠ Tu peux choisir ici de supprimer aussi les articles associés
        // ou bloquer la suppression si articles existants
        if ($kbCategory->articles()->exists()) {
            return back()->with('error','Impossible de supprimer : la catégorie contient encore des articles.');
        }

        $kbCategory->delete();

        return redirect()->route('kb.categories')
            ->with('success','Catégorie supprimée.');
    }
}
