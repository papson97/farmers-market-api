<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Lister toutes les catégories avec leurs enfants
    public function index()
    {
        $categories = Category::with('children.children')
            ->whereNull('parent_id')
            ->get();
        return response()->json($categories);
    }

    // Créer une catégorie
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $level = 1;
        if ($request->parent_id) {
            $parent = Category::findOrFail($request->parent_id);
            $level = $parent->level + 1;
        }

        $category = Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'level' => $level,
        ]);

        return response()->json($category, 201);
    }

    // Afficher une catégorie
    public function show($id)
    {
        $category = Category::with('children.children')->findOrFail($id);
        return response()->json($category);
    }

    // Modifier une catégorie
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::findOrFail($id);
        $category->update(['name' => $request->name]);
        return response()->json($category);
    }

    // Supprimer une catégorie
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Catégorie supprimée']);
    }
}