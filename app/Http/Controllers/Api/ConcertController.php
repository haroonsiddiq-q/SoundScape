<?php

namespace App\Http\Controllers\Api;

use App\Models\Concert;
use App\Models\Concert_Log;
use App\Models\Artist;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Province;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ConcertUpdatedNotification;
use Carbon\Carbon;

class ConcertController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'ticket_link' => 'required|string',
                'description' => 'nullable|string',
                'genre' => 'nullable|string|max:100',
                'event_type' => 'nullable|string|max:100',
                'venue_name' => 'nullable|string|max:255',
                'province_id' => 'nullable|integer|exists:provinces,id',
                'province_name' => 'nullable|string|max:255',
                'price_min' => 'nullable|numeric|min:0',
                'price_max' => 'nullable|numeric|min:0',
                'start_show_date' => 'nullable|date',
                'start_show_time' => 'nullable|date_format:H:i:s',
                'end_show_date' => 'nullable|date',
                'end_show_time' => 'nullable|date_format:H:i:s',
                'start_sale_date' => 'nullable|date',
                'end_sale_date' => 'nullable|date',
                'picture_url' => 'nullable|url',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'origin' => 'nullable|string',
                'artists' => 'nullable|array',
                'artists.*.name' => 'required_with:artists|string',
                'artists.*.image_url' => 'nullable|url',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();
            $artistsData = $data['artists'] ?? [];
            unset($data['artists']);

            if (empty($data['province_id']) && !empty($data['province_name'])) {
                $name = $data['province_name'];
                $prov = Province::where('name_en', $name)->orWhere('name_th', $name)->first();
                if ($prov) {
                    $data['province_id'] = $prov->id;
                }
                unset($data['province_name']);
            }

            $concert = Concert::withTrashed()->where('ticket_link', $data['ticket_link'])->first();

            $statusMessage = '';
            $statusCode = 200;

            if ($concert) {
                if ($concert->trashed()) {
                    $concert->restore();
                }

                $logData = $data;
                unset($logData['latitude'], $logData['longitude']);

                $groupedChanges = $this->logConcertChanges($concert, $logData, []);

                if (isset($data['picture_url']) && $concert->picture_url !== $data['picture_url']) {
                    Concert_Log::create([
                        'concert_id' => $concert->id,
                        'field_name' => 'picture_url',
                        'old_value' => (string)$concert->picture_url,
                        'new_value' => (string)$data['picture_url'],
                    ]);
                    $groupedChanges['picture_url'] = [
                        'old' => $concert->picture_url ?? 'None',
                        'new' => 'Picture updated',
                    ];
                }

                if (!empty($artistsData)) {
                    $artistIds = [];

                    foreach ($artistsData as $artistItem) {
                        $artist = Artist::firstOrCreate(
                            ['name' => $artistItem['name']],
                            ['picture_url' => $artistItem['image_url'] ?? null]
                        );
                        $artistIds[] = $artist->id;
                    }

                    $oldArtistIds = $concert->artists()->pluck('artists.id')->toArray();
                    sort($oldArtistIds);
                    $newArtistIds = $artistIds;
                    sort($newArtistIds);

                    if ($oldArtistIds !== $newArtistIds) {
                        Concert_Log::create([
                            'concert_id' => $concert->id,
                            'field_name' => 'artist_ids',
                            'old_value' => json_encode($oldArtistIds),
                            'new_value' => json_encode($newArtistIds),
                        ]);

                        $oldArtistNames = $concert->artists()->pluck('name')->implode(', ');
                        $newArtistNames = Artist::whereIn('id', $newArtistIds)->pluck('name')->implode(', ');

                        $groupedChanges['Artists'] = [
                            'old' => $oldArtistNames ?: 'No Artists',
                            'new' => $newArtistNames ?: 'No Artists'
                        ];
                    }

                    $concert->artists()->syncWithoutDetaching($artistIds);
                }

                $concert->update($data);
                $concert->touch();

                if (!empty($groupedChanges)) {
                    $followers = User::whereHas('followedConcert', function ($query) use ($concert) {
                        $query->where('concerts.id', $concert->id);
                    })->get();

                    if ($followers->count() > 0) {
                        Notification::send($followers, new ConcertUpdatedNotification($concert, $groupedChanges));
                    }
                }

                $statusMessage = 'Concert updated';
            } else {
                $concert = Concert::create($data);
                $statusMessage = 'Concert created';
                $statusCode = 201;
            }

            if (!empty($artistsData)) {
                $artistIds = [];

                foreach ($artistsData as $artistItem) {
                    $artist = Artist::firstOrCreate(
                        ['name' => $artistItem['name']],
                        ['picture_url' => $artistItem['image_url'] ?? null]
                    );

                    $artistIds[] = $artist->id;
                }

                $concert->artists()->syncWithoutDetaching($artistIds);
            }

            return response()->json(['message' => $statusMessage, 'concert' => $concert], $statusCode);
        } catch (\Exception $e) {
            Log::error('Error processing concert: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function cleanup(Request $request)
    {
        $origin = $request->input('origin');

        if (!$origin) {
            return response()->json(['message' => 'Origin required'], 400);
        }

        $threshold = Carbon::now()->subMinutes(30);

        $deletedCount = Concert::where('origin', $origin)
            ->where('updated_at', '<', $threshold)
            ->delete();

        return response()->json([
            'message' => "Cleanup completed for $origin",
            'deleted_count' => $deletedCount
        ]);
    }
}
