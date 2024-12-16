<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\CountryRequest;
use App\Models\Country;
use App\trait\ResponseGlobal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ApiCountryController extends Controller
{
    use ResponseGlobal;
    public function store(CountryRequest $request)
    {
        $countryPhotoPaths = [];
        if ($request->hasFile('country_photo')) {
            foreach ($request->file('country_photo') as $file) {
                $countryPhotoPaths[] = Storage::putFile("country_photos", $file);
            }
        }

        // Handle multiple event photo uploads
        $eventPhotoPaths = [];
        if ($request->hasFile('event_photo')) {
            foreach ($request->file('event_photo') as $file) {
                $eventPhotoPaths[] = Storage::putFile("event_photos", $file);
            }
        }

        $cleandescCountry = trim(preg_replace('/\s*\n\s*/', ' ', $request->country_desc)); // Replace all \n with a single space
        $cleandescCountry = htmlspecialchars($cleandescCountry, ENT_QUOTES, 'UTF-8'); // Sanitize for HTML

        $cleandescEvent = trim(preg_replace('/\s*\n\s*/', ' ', $request->desc_event)); // Replace all \n with a single space
        $cleandescEvent = htmlspecialchars($cleandescEvent, ENT_QUOTES, 'UTF-8'); // Sanitize for HTML

        // Create a new country event with latitude and longitude
       $country= Country::create([
            "country_name" => $request->country_name,
            "country_photo" =>json_encode($countryPhotoPaths),
            "city_name" => $request->city_name,
            "event_name" => $request->event_name,
            "date" => $request->date,
            "desc_event" =>$cleandescEvent,
            "country_desc" =>$cleandescCountry,
            "event_photo" =>json_encode($eventPhotoPaths),
            "latitude" => $request->latitude,  // Add latitude
            "longitude" => $request->longitude,  // Add longitude
        ]);
        return $this->success($country);
    }

    public function index1()
{
    // Fetch country_name, country_photo, and country_desc
    $countries = Country::select('country_name', 'country_photo', 'country_desc')
        ->get()
        ->groupBy('country_name');

    $formattedCountries = $countries->map(function ($photos, $countryName) {
        // Extract the first country description (if exists) from the group
        $countryDesc = $photos->first()->country_desc;

        // Extract all photos for the country
        $allPhotos = $photos->pluck('country_photo')->filter()->flatMap(function ($photo) {
            // Decode JSON string into an array if multiple photos exist
            $decodedPhotos = json_decode($photo, true);
            if (is_array($decodedPhotos)) {
                return $decodedPhotos; // Return array of photos
            }
            return [$photo]; // Return single photo in an array
        });

        // Initialize the response structure
        $data = [
            'country_name' => $countryName,
            'country_desc' => $countryDesc, // Add the country description
        ];

        // Check if there are any photos
        if ($allPhotos->isEmpty()) {
            $data['country_photos'] = null; // No photos available, return null
        } else {
            // Map the photos to their full URLs
            $data['country_photos'] = $allPhotos->map(function ($photo) {
                return asset('storage/' . $photo); // Construct the full URL for each photo
            });
        }

        return $data;
    });

    // Return the formatted data
    return $this->success($formattedCountries->values());
}

    public function indexsabry($country_name)
    {
        // Find the country by its name
        $country = Country::where('country_name', $country_name)->first();
        // Check if the country exists
        if (!$country) {
            return response()->json(['msg' => 'Country not found'], 404);
        }
        // Assuming 'event_name' is a comma-separated string in your current schema
        $city = Country::where('country_name', $country_name)
            ->pluck('city_name')
            ->filter(); // Use filter to remove null/empty values
        // Format the response
        $response = [
            'city_name' => $city, // The list of event names
        ];
      return $this->success($response);

    }
    public function index2($country_name)
    {
        // Fetch countries based on the given country name
        $countries = Country::where('country_name', $country_name)
            ->select('country_name', 'country_photo', 'country_desc', 'city_name')
            ->get()
            ->groupBy('country_name');

        // Check if the country exists
        if ($countries->isEmpty()) {
            return response()->json(['msg' => 'Country not found'], 404);
        }

        // Format the grouped data
        $formattedData = $countries->map(function ($countryGroup, $countryName) {
            // Extract the first country description (assuming it's the same for all records in the group)
            $countryDesc = $countryGroup->first()->country_desc;

            // Collect all unique city names
            $cityNames = $countryGroup->pluck('city_name')->unique()->filter();

            // Extract all photos for the country
            $allPhotos = $countryGroup->pluck('country_photo')->filter()->flatMap(function ($photo) {
                // Decode JSON string into an array if multiple photos exist
                $decodedPhotos = json_decode($photo, true);
                if (is_array($decodedPhotos)) {
                    return $decodedPhotos; // Return array of photos
                }
                return [$photo]; // Return single photo in an array
            });

            // Format the response
            return [
                'country_name' => $countryName,
                'country_desc' => $countryDesc,
                'city_names' => $cityNames->values(), // Return city names as an array
                'country_photos' => $allPhotos->isEmpty() ? null : $allPhotos->map(function ($photo) {
                    return asset('storage/' . $photo); // Construct the full URL for each photo
                }),
            ];
        });

        // Return the formatted data
        return $this->success($formattedData->values());
    }



    public function index3($city_name)
    {

        $countries = Country::where('city_name', $city_name)
        ->select('country_name', 'city_name', 'event_photo')
        ->get();
    // Format the countries data
    $formattedCountries = $countries->map(function ($country) {
        // Extract and handle event photos
        $allPhotos = collect([$country->event_photo])->filter()->flatMap(function ($photo) {
            // Decode JSON if it contains multiple photos
            $decodedPhotos = json_decode($photo, true);
            if (is_array($decodedPhotos)) {
                return $decodedPhotos; // Return array of photos
            }
            return [$photo]; // Return single photo as an array
        });

        // Construct the response structure
        return [
            'country_name' => $country->country_name,
            'city_name' => $country->city_name,
            'event_name' => $country->event_name,
            'date' => $country->date,
            'desc_event' => $country->desc_event,
            'latitude' => $country->latitude,
            'longitude' => $country->longitude,
            'event_photos' => $allPhotos->isEmpty()
                ? null // If no photos, return null
                : $allPhotos->map(function ($photo) {
                    return asset('storage/' . $photo); // Construct the full URL for each photo
                }),
        ];
    });

    // Return the formatted data
    return $this->success($formattedCountries);
    }

    public function update(Request $request, $id)
    {
        // Find the event by its ID
        $event = Country::find($id);

        // Check if the event exists
        if (!$event) {
            return $this->error('Operation failed',400,'Event not found');
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            "country_name" => "required|string|max:255",
            "city_name" => "required|string|max:255",
            "event_name" => "required|string|max:255",
            "date" => "required|date",
            "desc_event" => "required|string",
            "country_photo" => "nullable|file|mimes:jpg,jpeg,png|max:2048", // Country photo (optional)
            "event_photo" => "nullable|file|mimes:jpg,jpeg,png|max:2048", // Event photo (optional)
            "latitude" => "required|numeric",    // Latitude (required)
            "longitude" => "required|numeric",   // Longitude (required)
        ]);

        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors()
            ], 404);
        }

        // Delete old country photo if a new one is being uploaded
        if ($request->hasFile('country_photo')) {
            if ($event->country_photo) {
                Storage::delete($event->country_photo);  // Delete old country photo
            }
            $countryPhotoName = Storage::putFile("country_photos", $request->country_photo);
            $event->country_photo = $countryPhotoName;
        }
        // Delete old event photo if a new one is being uploaded
        if ($request->hasFile('event_photo')) {
            if ($event->event_photo) {
                Storage::delete($event->event_photo);  // Delete old event photo
            }
            $eventPhotoName = Storage::putFile("event_photos", $request->event_photo);
            $event->event_photo = $eventPhotoName;
        }
        // Update the event details
        $event->country_name = $request->country_name;
        $event->city_name = $request->city_name;
        $event->event_name = $request->event_name;
        $event->date = $request->date;
        $event->desc_event = $request->desc_event;
        $event->latitude = $request->latitude;
        $event->longitude = $request->longitude;
        // Save the updated event
        $event->save();
        // Response
        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event
        ], 200);
    }


    public function destroy($id)
{
    // Find the country by its ID
    $country = Country::find($id);

    // Check if the country exists
    if (!$country) {
        return response()->json(['message' => 'Country not found'], 404);
    }
    // Delete country photos if they exist
    if ($country->country_photo) {
        $countryPhotos = json_decode($country->country_photo, true);
        foreach ($countryPhotos as $photo) {
            // Delete each country photo from storage
            Storage::delete($photo);
        }
    }
    // Delete event photos if they exist
    if ($country->event_photo) {
        $eventPhotos = json_decode($country->event_photo, true);
        foreach ($eventPhotos as $photo) {
            // Delete each event photo from storage
            Storage::delete($photo);
        }
    }
    // Delete the country from the database
    $country->delete();
    // Return success response
    return response()->json([
        'message' => 'Country and associated photos deleted successfully'
    ], 200);
}

}
