<?php

namespace App\Http\Controllers\Promoter;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Artist;
use App\Models\Concert;
use App\Models\Province;
use App\Models\Concert_Log;
use App\Notifications\ConcertUpdatedNotification;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class ConcertController extends Controller
{
    public function index(Request $request)
    {
        $concerts = Concert::where('promoter_id', auth('web')->user()->promoter->id)
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $provinces = Province::all()->keyBy('id');

        return inertia::render('Promoter/Concert/Index', [
            'concerts' => $concerts,
            'provinces' => $provinces,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        $artists = Artist::all();

        return inertia::render('Promoter/Concert/Create', [
            'artists' => $artists,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());
        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];

        if (is_null($latitude) && !empty($validated['venue_name']) && !empty($validated['province_id'])) {

            $province = Province::find($validated['province_id']);

            if ($province) {
                $queryString = urlencode($validated['venue_name'] . ', ' . $province->name_en . ', Thailand');
                $url = "https://nominatim.openstreetmap.org/search?q={$queryString}&format=json&limit=1&accept-language=th,en";

                $response = Http::withHeaders([
                    'User-Agent' => 'SoundScape/1.0 (haroonsiddiq.q@kkumail.com)'
                ])->get($url);

                if ($response->successful()) {
                    $data = $response->json();

                    if (!empty($data)) {
                        $latitude = $data[0]['lat'];
                        $longitude = $data[0]['lon'];
                    } else {
                        throw ValidationException::withMessages([
                            'venue_name' => 'Not found this location. Please check the venue name and province again, or use the map to pin the location manually.',
                        ]);
                    }
                }
            }
        }

        $validated['latitude'] = $latitude;
        $validated['longitude'] = $longitude;

        $concert = Concert::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'genre' => $validated['genre'],
            'event_type' => $validated['event_type'],
            'venue_name' => $validated['venue_name'],
            'province_id' => $validated['province_id'],
            'price_min' => $validated['price_min'],
            'price_max' => $validated['price_max'],
            'start_show_date' => $validated['start_show_date'],
            'start_show_time' => $validated['start_show_time'],
            'end_show_date' => $validated['end_show_date'],
            'end_show_time' => $validated['end_show_time'],
            'start_sale_date' => $validated['start_sale_date'],
            'end_sale_date' => $validated['end_sale_date'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'picture_url' => $validated['picture_url']->store('concerts', 'public'),
            'ticket_link' => $validated['ticket_link'],

            'promoter_id' => auth('web')->user()->promoter->id,
        ]);

        if (isset($validated['artist_ids'])) {
            $concert->artists()->attach($validated['artist_ids']);
        }

        return redirect()->route('promoter.concert.index')->with('success', 'Concert created successfully.');
    }

    public function detail(Concert $concert)
    {
        if ($concert->promoter_id !== Auth::user()->promoter->id) {
            abort(403);
        }

        $concert->load(['artists', 'comments.user', 'comments.admin', 'comments.promoter']);
        $provinces = Province::all()->keyBy('id');
        
        $promoterId = Auth::user()->promoter->id;

        $concert->comments->each(function ($comment) use ($concert, $promoterId) {
            $comment->is_author = false;
            $comment->is_owner = false;

            if ($comment->admin_id) {
                $comment->author_name = $comment->admin->name;
                if ($concert->admin_id === $comment->admin_id) $comment->is_author = true;
            } 
            elseif ($comment->promoter_id) {
                $comment->author_name = $comment->promoter->business_name ?? $comment->promoter->fullname;
                if ($concert->promoter_id === $comment->promoter_id) $comment->is_author = true;
                
                if ($promoterId && (int)$comment->promoter_id === (int)$promoterId) {
                    $comment->is_owner = true;
                }
            } 
            elseif ($comment->user_id) {
                $comment->author_name = $comment->user->name;
            }
        });

        return Inertia::render('Promoter/Concert/Detail', [
            'concert' => $concert,
            'provinces' => $provinces,
        ]);
    }

    public function edit(Concert $concert)
    {
        if ($concert->promoter_id !== Auth::user()->promoter->id) {
            abort(403);
        }

        $concert->load('artists');
        $artists = Artist::all();

        return Inertia::render('Promoter/Concert/Edit', [
            'concert' => $concert,
            'artists' => $artists,
        ]);
    }

    public function update(Request $request, Concert $concert)
    {
        if ($concert->promoter_id !== Auth::user()->promoter->id) {
            abort(403);
        }

        $validated = $request->validate($this->validationRules(true));
        $originalData = $concert->getOriginal();
        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];

        if (is_null($latitude) && !empty($validated['venue_name']) && !empty($validated['province_id'])) {

            $province = Province::find($validated['province_id']);

            if ($province) {
                $queryString = urlencode($validated['venue_name'] . ', ' . $province->name_en . ', Thailand');
                $url = "https://nominatim.openstreetmap.org/search?q={$queryString}&format=json&limit=1&accept-language=th,en";

                $response = Http::withHeaders([
                    'User-Agent' => 'SoundScape/1.0 (haroonsiddiq.q@kkumail.com)'
                ])->get($url);

                if ($response->successful()) {
                    $data = $response->json();

                    if (!empty($data)) {
                        $latitude = $data[0]['lat'];
                        $longitude = $data[0]['lon'];
                    } else {
                        throw ValidationException::withMessages([
                            'venue_name' => 'Not found this location. Please check the venue name and province again, or use the map to pin the location manually.',
                        ]);
                    }
                }
            }
        }

        $validated['latitude'] = $latitude;
        $validated['longitude'] = $longitude;
        $promoterId = Auth::user()->promoter->id;
        $actor = ['promoter_id' => Auth::user()->promoter->id];

        $groupedChanges = $this->logConcertChanges($concert, $validated, $actor);

        if ($request->hasFile('picture_url')) {
            $newPath = $request->file('picture_url')->store('concerts', 'public');
            Concert_Log::create([
                'concert_id' => $concert->id,
                'promoter_id' => $promoterId,
                'field_name' => 'picture_url',
                'old_value' => $concert->picture_url,
                'new_value' => $newPath,
            ]);
            $validated['picture_url'] = $newPath;
        } else {
            unset($validated['picture_url']);
        }

        if ($request->has('artist_ids')) {
            $concert->artists()->sync($validated['artist_ids'] ?? []);

            unset($validated['artist_ids']);
        }

        $concert->update($validated);

        if (!empty($groupedChanges)) {
            $followers = User::whereHas('followedConcert', function ($query) use ($concert) {
                $query->where('concerts.id', $concert->id);
            })->get();

            if ($followers->count() > 0) {
                Notification::send($followers, new ConcertUpdatedNotification($concert, $groupedChanges));
            }
        }

        return redirect()->route('promoter.concert.detail', $concert)->with('success', 'Concert updated successfully.');
    }

    public function destroy(Concert $concert)
    {
        if ($concert->promoter_id !== Auth::user()->promoter->id) {
            abort(403);
        }

        if ($concert->picture_url) {
            Storage::disk('public')->delete($concert->picture_url);
        }
        $concert->delete();
        return redirect()->route('promoter.concert.index')->with('success', 'Concert deleted successfully.');
    }

    private function validationRules($isUpdate = false)
    {
        $rules = [
            // Core Information
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'event_type' => 'nullable|in:music_festival,concert,club,fan_meeting,folk,other',
            'genre' => 'nullable|in:pop,rock,hiphop,jazz,classical,country,edm,other',

            // Location
            'venue_name' => 'nullable|string|max:255',
            'province_id' => 'nullable|exists:provinces,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',

            // Prices
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0|gte:price_min',

            // Dates and Times
            'start_sale_date' => 'nullable|date_format:Y-m-d',
            'end_sale_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_sale_date',
            'start_show_date' => 'nullable|date_format:Y-m-d',
            'start_show_time' => 'nullable|date_format:H:i:s',
            'end_show_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_show_date',
            'end_show_time' => 'nullable|date_format:H:i:s',

            // Additional Information
            'ticket_link' => 'nullable|url',

            'artist_ids' => 'nullable|array',
            'artist_ids.*' => 'exists:artists,id',
        ];

        if ($isUpdate) {
            $rules['picture_url'] = 'nullable|image|max:2048';
        } else {
            $rules['picture_url'] = 'required|image|max:2048';
        }

        return $rules;
    }
}
