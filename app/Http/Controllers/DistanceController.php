<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


class DistanceController extends Controller
{
    // Haversine formula to calculate the great-circle distance between two points
    public function calculateDistance(Request $request): \Illuminate\Http\JsonResponse
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

        return response()->json(['distance' => $earthRadius * $c], 200);
    }
}
