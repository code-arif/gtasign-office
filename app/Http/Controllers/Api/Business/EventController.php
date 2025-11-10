<?php

namespace App\Http\Controllers\Api\Business;

use Exception;
use App\Models\Event;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    use ApiResponse;

    public function seasonalEventList()
    {
        try {
            $events = Event::where('type', 'seasonal')->where('business_profile_id', Auth::user()->business_profile->id)->get();

            if ($events->isEmpty()) {
                return $this->success([], 'Seasonal events not found', 200);
            }

            return $this->success($events, 'Seasonal events retrieved successfully', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error($e->getMessage(), 'Error while listing seasonal events', 500);
        }
    }

    public function specialEventList()
    {
        try {
            $events = Event::where('type', 'special')->where('business_profile_id', Auth::user()->business_profile->id)->get();

            if ($events->isEmpty()) {
                return $this->success([], 'Special events not found', 200);
            }

            return $this->success($events, 'Special events retrieved successfully', 200);
        } catch (Exception $e) {
            
            Log::error($e->getMessage());
            return $this->error($e->getMessage(), 'Error while listing special events', 500);
        }
    }

    public function store(Request $request)
    {

       
        try {
            $validatedData = $request->validate([
                'type' => 'required|in:seasonal,special',
                'name' => 'required|string',
                'discount' => 'nullable|numeric',
                'on_offer' => 'nullable|boolean',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i',
                'description' => 'nullable|string',
            ]);


            $event = Event::create([
                'business_profile_id'        => Auth::user()->business_profile->id,
                'type'                       => $validatedData['type'],
                'name'                       => $validatedData['name'] ?? null,
                'discount'                   => $validatedData['discount'] ?? null,
                'on_offer'                   => $validatedData['on_offer'] ?? null,
                'start_date'                 => $validatedData['start_date'] ?? null,
                'end_date'                   => $validatedData['end_date'] ?? null,
                'start_time'                 => $validatedData['start_time']  ?? null,
                'end_time'                   => $validatedData['end_time'] ?? null,
                'description'                => $validatedData['description'] ?? null,
            ]);

            $type = $event->type === 'seasonal' ? 'Seasonal' : 'Special';


            return $this->success($event, $type . ' event created successfully', 201);

        } catch (Exception $e) {

            Log::error($e->getMessage());
            return $this->error($e->getMessage(), 'Error while creating event', 500);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'type'                        => 'nullable|in:seasonal,special',
                'name'                        => 'nullable|string',
                'discount'                    => 'nullable|numeric',
                'on_offer'                    => 'nullable|boolean',
                'start_date'                  => 'nullable|date',
                'end_date'                    => 'nullable|date',
                'start_time'                  => 'nullable|date_format:H:i',
                'end_time'                    => 'nullable|date_format:H:i',
                'description'                 => 'nullable|string',
            ]);

            $event = Event::find($id);

            if (!$event) {
                return $this->success([], 'Event not found', 200);
            }

            if ($event->business_profile_id !== Auth::user()->business_profile->id) {
                return $this->error([], 'Unauthorized access', 403);
            }

            $event->update($validatedData);

            return $this->success($event, 'Event updated successfully', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error($e->getMessage(), 'Error while updating event', 500);
        }
    }

    public function cancel($id)
    {
        try {
            $event = Event::find($id);

            if (!$event) {
                return $this->success([], 'Event not found', 200);
            }

            if ($event->business_profile_id !== Auth::user()->business_profile->id) {
                return $this->error([], 'Unauthorized access', 403);
            }

            $event->delete();

            return $this->success([], 'Event cancelled successfully', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error($e->getMessage(), 'Error while cancelling event', 500);
        }
    }


    public function delete($id)
    {
        try {
            $event = Event::find($id);

            if (!$event) {
                return $this->success([], 'Event not found', 200);
            }

            if ($event->business_profile_id!== Auth::user()->business_profile->id) {
                return $this->error([], 'Unauthorized access', 403);
            }

            $event->delete();

            return $this->success([], 'Event deleted successfully', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error($e->getMessage(), 'Error while deleting event', 500);
        }
    }
}
