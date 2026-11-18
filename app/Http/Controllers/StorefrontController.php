<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(): View
    {
        return view('storefront.index', [
            'products' => Product::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }
}
