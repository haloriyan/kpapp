<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function get() {
        $categories = Category::orderBy('priority', 'DESC')->orderBy('updated_at', 'ASC')->get();

        return response()->json([
            'categories' => $categories
        ]);
    }
    public function create(Request $request) {
        $cover = $request->file('cover');
        $coverFileName = $cover->getClientOriginalName();
        $cover->storeAs('public/category_covers', $coverFileName);
        $icon = $request->file('icon');
        $iconFileName = $icon->getClientOriginalName();
        $icon->storeAs('public/category_icons', $iconFileName);

        $saveData = Category::create([
            'name' => $request->name,
            'icon' => $iconFileName,
            'cover' => $coverFileName,
            'priority' => 0,
        ]);

        return response()->json([
            'status' => 200,
        ]);
    }
    public function delete(Request $request) {
        $data = Category::where('id', $request->id);
        $category = $data->first();

        $deleteData = $data->delete();
        $deleteIcon = Storage::delete('public/category_icons/' . $category->icon);
        $deleteCover = Storage::delete('public/category_covers/' . $category->cover);

        return response()->json([
            'status' => 200,
        ]);
    }
}
