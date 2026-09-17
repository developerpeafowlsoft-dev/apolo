<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Retrieves a paginated list of categories with their associated products.
     *
     * @param  Request  $request  The HTTP request object.
     * @return JsonResponse The JSON response containing the categories and the total count.
     */
    public function index(Request $request)
    {
        $page = $request->page;
        $perPage = $request->per_page;
        $skip = ($page * $perPage) - $perPage;

        $shop = generaleSetting('rootShop');

        $categories = CategoryRepository::query()->active()
            ->where('show_in_hero', 1)
            ->select('categories.*')
            ->selectSub(function ($q) {
                $q->from('inward_products')
                    ->join('product_categories', 'inward_products.product_id', '=', 'product_categories.product_id')
                    ->whereColumn('product_categories.category_id', 'categories.id')
                    ->selectRaw('count(*)');
            }, 'inward_products_count')
            ->selectSub(function ($q) {
                $q->from('products')
                    ->join('product_categories', 'products.id', '=', 'product_categories.product_id')
                    ->whereColumn('product_categories.category_id', 'categories.id')
                    ->where('products.is_online_product', 1)
                    ->where('products.is_active', 1)
                    ->selectRaw('count(*)');
            }, 'online_products_count')
            ->latest('id');

        $total = $categories->count();

        $categories = $categories->when($perPage && $page, function ($query) use ($perPage, $skip) {
            return $query->skip($skip)->take($perPage);
        })->with('subCategories')->get();

        return $this->json('categories', [
            'total' => $total,
            'categories' => CategoryResource::collection($categories),
        ]);
    }
}
