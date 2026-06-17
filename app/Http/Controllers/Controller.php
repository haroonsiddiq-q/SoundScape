<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use App\Models\Concert_Log;
use App\Models\Artist;
use Carbon\Carbon;

abstract class Controller
{
    protected function logConcertChanges(Concert $concert, array $validated, array $actorDetails): array
    {
        $originalData = $concert->getOriginal();
        $groupedChanges = [];

        foreach ($validated as $key => $value) {
            if ($key === 'artist_ids') {
                $oldArtistIds = $concert->artists()->pluck('artists.id')->toArray();
                sort($oldArtistIds);
                $newArtistIds = $value ?? [];
                sort($newArtistIds);

                if ($oldArtistIds !== $newArtistIds) {
                    Concert_Log::create(array_merge([
                        'concert_id' => $concert->id,
                        'field_name' => 'artist_ids',
                        'old_value' => json_encode($oldArtistIds),
                        'new_value' => json_encode($newArtistIds),
                    ], $actorDetails));

                    $oldArtistNames = $concert->artists()->pluck('name')->implode(', ');
                    $newArtistNames = Artist::whereIn('id', $newArtistIds)->pluck('name')->implode(', ');

                    $groupedChanges['Artists'] = [
                        'old' => $oldArtistNames ?: 'No Artists',
                        'new' => $newArtistNames ?: 'No Artists'
                    ];
                }
                continue;
            }

            if ($key === 'start_datetime') {
                $oldDate = Carbon::parse($concert->start_datetime);
                $newDate = Carbon::parse($value);

                if ($oldDate->ne($newDate)) {
                    Concert_Log::create(array_merge([
                        'concert_id' => $concert->id,
                        'field_name' => $key,
                        'old_value' => $concert->start_datetime,
                        'new_value' => $newDate->toDateTimeString(),
                    ], $actorDetails));

                    $groupedChanges[$key] = [
                        'old' => $concert->start_datetime,
                        'new' => $newDate->toDateTimeString()
                    ];
                }
                continue;
            }

            if ($key !== 'picture_url' && $concert->{$key} != $value) {
                Concert_Log::create(array_merge([
                    'concert_id' => $concert->id,
                    'field_name' => $key,
                    'old_value' => $originalData[$key] ?? 'null',
                    'new_value' => $value,
                ], $actorDetails));

                $excludedFields = ['genre', 'event_type', 'price_max', 'end_show_date', 'end_show_time'];

                if (!in_array($key, $excludedFields)) {
                    $groupedChanges[$key] = [
                        'old' => $originalData[$key] ?? 'None',
                        'new' => $value,
                    ];
                }
            }
        }

        return $groupedChanges; 
    }
}