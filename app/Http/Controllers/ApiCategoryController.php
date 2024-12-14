<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\trait\ResponseGlobal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\CategoryRequest;
use App\Http\Requests\Auth\CategoryAndAlternativeRequest;

class ApiCategoryController extends Controller
{
use ResponseGlobal ;
    public function store(CategoryRequest $request)
    {
        // Create the category
        $categoryPhotoPath = null;
        if ($request->hasFile('category_photo')) {
            $categoryPhotoPath = Storage::putFile('category_photos', $request->file('category_photo')); // Save file
        }

        $category = Category::create([
            'name' => $request->input('name'),
            'desc' => $request->input('desc'),
            'code' => strtolower($request->input('code')), // Store code in lowercase
            'category_photo' => $categoryPhotoPath,
            'category_type' => $request->input('category_type'),
        ]);
        return $this->success($category) ;
    }


    public function checkCategoryByCodeAndAlternatives(CategoryAndAlternativeRequest $request)
    {
    $category = null;
    if ($request->has('code')){
        $input = trim(strtolower($request->input('code')));
        $category = Category::whereRaw('LOWER(code) = ?', [$input])->first();
    }elseif ($request->has('name')) {
        $input = trim(strtolower($request->input('name')));
        $category = Category::whereRaw('LOWER(name) = ?', [$input])->first();
    }
    if (!$category) {
        return $this->success( 'هذا المنتج ليس في المقاطعه') ;
    }
        // Retrieve the products for this category (relationship)
        $products = $category->products;
        $response = [
            'msg' => 'هذا المنتج في المقاطعه',
            'category_desc' => $category->desc,
            'category_photo' => $category->category_photo
        ? asset('storage/' . $category->category_photo)  // Include category photo URL if it exists
        : null,
        ];
        // Add alternatives to the response if they exist
        if (!$products->isEmpty()){
            $response['alternatives'] = $products->map(function ($product) {
                return [
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                    'product_image' => asset('storage') . '/' . $product->image,
                ];
            });
        }else {
            $response['alternatives'] = 'لا توجد منتجات بديلة في هذه الفئة';
        }
        return $this->success($response) ;
    }


    public function deleteCategory($id)
    {
        // Find the category by ID
        $category = Category::find($id);

        // If category not found, return error
        if (!$category) {
            return $this->error('Operation failed', 400, 'Category not found');
        }

        if ($category->category_photo) {
            Storage::delete($category->category_photo); // Deletes the file from storage
        }
        $category->delete();
        return $this->success();
    }

public function getAllCategories()
{
    // Retrieve all categories
    $categories = Category::all();

    // Transform categories to include photo URLs
    $categoriesData = $categories->map(function ($category) {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'desc' => $category->desc,
            'code' => $category->code,
            'category_photo' => $category->category_photo
                ? asset('storage/' . $category->category_photo)
                : null, // Include the photo URL if it exists
            'category_type' => $category->category_type,
        ];
    });
    return $this->success($categoriesData);
}

public function getCategoryName($name)
{
    // Fetch the category by name (case-insensitive)
    $category = Category::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

    // Check if the category exists
    if (!$category) {
        return response()->json(['msg' => 'Category not found'], 404);
    }

    // Prepare category data
    $categoryData = [
        'id' => $category->id,
        'name' => $category->name,
        'desc' => $category->desc,
        'code' => $category->code,
        'category_photo' => $category->category_photo
            ? asset('storage/' . $category->category_photo)
            : null, // Include the photo URL if it exists
        'category_type' => $category->category_type,
    ];

    return $this->success($categoryData);
    // Return the response

}
public function getCategoryByType($categoryType)
{
    // Fetch categories by type (case-insensitive)
    $categories = Category::whereRaw('LOWER(category_type) = ?', [strtolower($categoryType)])->get();

    // Check if any categories are found
    if ($categories->isEmpty()) {
        return response()->json(['msg' => 'No categories found for this type'], 404);
    }

    // Prepare categories data
    $categoriesData = $categories->map(function ($category) {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'desc' => $category->desc,
            'code' => $category->code,
            'category_photo' => $category->category_photo
                ? asset('storage/' . $category->category_photo)
                : null, // Include the photo URL if it exists
            'category_type' => $category->category_type,
        ];
    });

    // Return the response
    return $this->success($categoriesData);
}

}
