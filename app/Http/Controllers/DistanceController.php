<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;



class DistanceController extends Controller
{
    // Haversine formula to calculate the great-circle distance between two points
    public function calculateDistance(Request $request): JsonResponse
    {
        $earthRadius = 6371; // Radius of the earth in kilometers

        try {
            //Request Object contains input data, we use the validate function to define the validation rules, we require all params to be present and numeric!
            $validatedData = $request->validate([
                "latitudeFrom" => "required|numeric|between:-90,90",
                "longitudeFrom" => "required|numeric|between:-180,180",
                "latitudeTo" => "required|numeric|between:-90,90",
                "longitudeTo" => "required|numeric|between:-180,180",
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }

        //If input data fails validation, Laravel will auto-throw a ValidationException, which we can handle later

        //New vars to store converted values (from Degrees to Radians)
        $latitudeFrom = deg2rad($validatedData['latitudeFrom']);
        $longitudeFrom = deg2rad($validatedData['longitudeFrom']);
        $latitudeTo = deg2rad($validatedData['latitudeTo']);
        $longitudeTo = deg2rad($validatedData['longitudeTo']);

        $deltaLatitude = $latitudeTo - $latitudeFrom;
        $deltaLongitude = $longitudeTo - $longitudeFrom;

        $a = sin($deltaLatitude / 2) * sin($deltaLatitude / 2) + cos($latitudeFrom) * cos($latitudeTo) * sin($deltaLongitude / 2) * sin($deltaLongitude / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $locationDataFrom = $this->reverseGeocode($validatedData['latitudeFrom'], $validatedData['longitudeFrom']);
        $locationDataTo = $this->reverseGeocode($validatedData['latitudeTo'], $validatedData['longitudeTo']);

        return response()->json([
            'distance' => $earthRadius * $c,
            'location from' => $locationDataFrom->data,
            'location to' => $locationDataTo->data,
        ], 200);
    }

    public function getLocationFromCoordinates(Request $request): JsonResponse
    {
        try {
            //Request Object contains input data, we use the validate function to define the validation rules, we require all params to be present and numeric!
            $validatedData = $request->validate([
                "latitude" => "required|numeric|between:-90,90",
                "longitude" => "required|numeric|between:-180,180",
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }

        $locationData = $this->reverseGeocode($request['latitude'], $request['longitude']);
        return response()->json(['location data' => $locationData->data], 200);

    }

    public function reverseGeocode($from, $to): object
    {
        try {
            // Reverse Geocoding Functionality using PositionStack API, use access key from env
            $url = 'http://api.positionstack.com/v1/reverse?access_key=' . env('POSITIONSTACK_API_KEY') . '&query=' . $from . ',' . $to . '&limit=1';

            $response = Http::get($url);

            if ($response->successful()) {
                return $response->object(); // Returning as an Object rather than JSON format
            }
            // Handle non-successful response
            $statusCode = $response->status();
            return response()->json(['error' => 'API request failed with status code ' . $statusCode], $statusCode);
        } catch (\Exception $e) {
            // Handle any other exceptions (e.g., network error)
            return response()->json(['error' => 'API request failed: ' . $e->getMessage()], 500);
        }
    }
}
