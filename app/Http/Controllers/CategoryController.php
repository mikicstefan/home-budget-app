<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{

    /**
     * Index
     *
     * List all categories
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = Category::all();
        
        return response()->json(['data' => $categories]);
    }

    /**
     * Store
     *
     * Create and store new categories
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:categories',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $category = Category::create(['name' => $request->name]);
        
        return response()->json(['data' => $category]);
    }

    /**
     * Update
     *
     * Update existing category
     *
     * @param Request $request
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('categories')->ignore($category->id)
            ]
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $category->name = $request->name;
        $category->save();
        
        return response()->json(['data' => $category]);
    }

    /**
     * Delete
     *
     * Delete category. Only possible if category does not have expanses
     *
     * @param Request $request
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Category $category)
    {
        if ($category->expanses->isNotEmpty()) {
            return response()->json(['message' => 'Action not possible! Expanses created for this category']);
        }

        $category->delete();

        return response()->json(['message' => 'Category successfully deleted']);
    }
}
