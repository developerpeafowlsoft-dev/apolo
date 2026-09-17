<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\TranslateUtility;
use Illuminate\Support\Str;

class CategoryRepository extends Repository
{
    /**
     * base method
     *
     * @method model()
     */
    public static function model()
    {
        return Category::class;
    }

    /**
     * store a new category
     */
    public static function storeByRequest(CategoryRequest $request): Category
    {
        $thumbnail = MediaRepository::storeByRequest(
            $request->file('thumbnail'),
            'categories',
            'image'
        );

        $category = self::create([
            'name' => $request->name,
            'media_id' => $thumbnail->id ?? null,
            'description' => $request->description,
            'status' => true,
            'created_by' => auth()->id() ?? 1,
            'show_in_hero' => $request->boolean('show_in_hero'),
        ]);

        // create translation
        foreach ($request->names ?? [] as $lang => $name) {
            if (! $lang || ! $name) {
                continue;
            }
            TranslateUtility::create([
                'category_id' => $category->id,
                'name' => $name,
                'lang' => $lang,
                'slug' => Str::slug($name, '-'),
            ]);
        }

        return $category;
    }

    /**
     * update a category
     */
    public static function updateByRequest(CategoryRequest $request, Category $category): Category
    {
        $thumbnail = $category->media;
        if ($request->hasFile('thumbnail')) {
            $thumbnail = MediaRepository::updateByRequest(
                $request->file('thumbnail'),
                'categories',
                'image',
                $thumbnail
            );
        }

        $updateData = [
            'name' => $request->name,
            'media_id' => $thumbnail->id ?? $category->media_id,
            'description' => $request->description,
            'show_in_hero' => $request->boolean('show_in_hero'),
        ];

        $category->update($updateData);

        // update and create translation
        foreach ($request->names ?? [] as $lang => $name) {
            if (! $lang || ! $name) {
                continue;
            }
            TranslateUtility::updateOrCreate([
                'category_id' => $category->id,
                'lang' => $lang,
            ], [
                'name' => $name,
                'slug' => Str::slug($name, '-'),
            ]);
        }

        return $category;
    }
}
