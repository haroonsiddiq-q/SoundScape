<?php

namespace App\Http\Controllers\Api;

use App\Models\Artist;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArtistController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'picture_url' => 'nullable|url'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            $artist = Artist::updateOrCreate(
                ['name' => $data['name']],
                $data
            );

            $status = $artist->wasRecentlyCreated ? 201 : 200;
            $msg = $artist->wasRecentlyCreated ? 'Artist created successfully' : 'Artist updated successfully';

            return response()->json(['message' => $msg, 'artist' => $artist], $status);

        } catch (\Exception $e) {
            Log::error('Error processing artist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}