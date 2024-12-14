<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ProductRequest;
use App\Models\Product;
use App\trait\ResponseGlobal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ApiProductController extends Controller
{
use ResponseGlobal ;
    public function store(ProductRequest $request){

         if($request->has("image")){
            $imageName=Storage::putFile("products",$request->image); // new
         }
        // Create
      $product = Product::create([
          "name"=>$request->name,
          "price"=>$request->price,
          "category_id"=>$request->category_id,
          'product_type'=>$request->product_type,
          "image"=>$imageName,
         ]);

           return $this->success($product);
      }
      public function update(Request $request,$id)
      {
          $validator = Validator::make($request->all(),[
              "name"=>"required|string|max:255",
              "price"=>"required|numeric",
              "category_id"=>"required|exists:categories,id",
             "image" => 'nullable|file|mimes:jpg,jpeg,png|max:3072', // Max size is 3MB (3072KB)
             ]);

             if($validator->fails())
             {
              return response()->json([
                  "errors"=>$validator->errors()
              ],404);
             }
          // find
           $product = Product::find($id);
           if(!$product){
            return $this->error('Operation failed',400,'Product not found');
            }
      // storage
           $imageName = $product->image; // اسسم الصوره القديمهold
          if($request->has("image")){
              if($product->image !== null){
                  Storage::delete($imageName);
              }
              $imageName=Storage::putFile("products",$request->image); // new
           }
      // update
          $product= $product->update([
          "name"=>$request->name,
          "price"=>$request->price,
          "image"=>$imageName,
          "category_id"=>$request->category_id,
           ]);
           return $this->success($product);
      }

      public function deleteProduct($id)
      {
          $product = Product::find($id);
          if (!$product) {
              return $this->error('Operation failed', 400, 'Product not found');
          }
          if ($product->image) {
              Storage::delete($product->image); // Deletes the file from storage
          }
          $product->delete();
          return $this->success('Product and associated image deleted successfully.');
      }



 public function getAllProducts()
{
    // Fetch all products
    $products = Product::all();

    // Check if there are any products
    if ($products->isEmpty()) {
        return response()->json(['msg' => 'No products found'], 404);
    }

    // Prepare products data
    $productsData = $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'image' => $product->image ? asset('storage/' . $product->image) : null, // Include the image URL if it exists
            'product_type' => $product->product_type,
        ];
    });
    return $this->success($productsData);
}



// public function getProductsByType($productType)
// {
//     // Fetch products by type (case-insensitive) and group by name
//     $products = Product::whereRaw('LOWER(product_type) = ?', [strtolower($productType)])
//         ->get()
//         ->groupBy('name');

//     // Check if any products are found
//     if ($products->isEmpty()) {
//         return response()->json(['msg' => 'No products found for this type'], 404);
//     }

//     // Prepare grouped products data
//     $groupedProducts = $products->map(function ($group) {
//         return $group->map(function ($product) {
//             return [
//                 'id' => $product->id,
//                 'price' => $product->price,
//                 'image' => $product->image ? asset('storage/' . $product->image) : null, // Include the image URL if it exists
//                 'product_type' => $product->product_type,
//             ];
//         });
//     });

//     // Return the response
//     return $this->success([
//         'product_type' => $productType,
//         'products' => $groupedProducts,
//     ]);
// }

public function getProductsByType($productType)
{
    // Fetch distinct products by name and type (case-insensitive)
    $products = Product::whereRaw('LOWER(product_type) = ?', [strtolower($productType)])
        ->selectRaw('MIN(id) as id, name, MIN(price) as price, MIN(image) as image, product_type')
        ->groupBy('name', 'product_type') // Group by name and type
        ->get();

    // Check if any products are found
    if ($products->isEmpty()) {
        return response()->json(['msg' => 'No products found for this type'], 404);
    }

    // Prepare the products data
    $productsData = $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'image' => $product->image ? asset('storage/' . $product->image) : null,
            'product_type' => $product->product_type,
        ];
    });

    // Return the response
    return $this->success($productsData);
}


}
