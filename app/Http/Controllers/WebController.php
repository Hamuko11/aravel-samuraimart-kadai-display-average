<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\MajorCategory;

class WebController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        //カテゴリーカラムを配列で取得する（pluck）
        //$major_category_names = Category::pluck('major_category_name')->unique();
        $major_categories = MajorCategory::all();
        return view('web.index', compact('major_categories', 'categories'));
    }
}
