<?php

namespace App\Http\Controllers\Api\User\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreAvailabilityRequest;
use App\Models\UserAvailability;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\DB;

class UserAvailabilityController extends Controller
{
    use ApiResponse;

    /**
     * Set Availability for user
     */
    public function store(StoreAvailabilityRequest $request)
    {
        $user = auth()->user();

        DB::beginTransaction();

        try {

            // deactivate previous vacation
            UserAvailability::where('user_id', $user->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $availability = UserAvailability::create([
                'user_id'    => $user->id,
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'message'    => $request->message,
                'is_active'  => true,
            ]);

            DB::commit();

            return $this->success('Availability scheduled successfully', $availability, 201);
        } catch (Exception $e) {

            DB::rollBack();

            return $this->error([], 'Failed to set availability', 500);
        }
    }

    /**
     * Cancle Availability for user
     */
    public function cancel()
    {
        $user = auth()->user();

        UserAvailability::where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return $this->success('Availability cancelled', []);
    }

    /**
     * Show active availability for user
     */
    public function show()
    {
        $availability = auth()->user()->activeAvailability;

        return $this->success('Current availability', $availability, 200);
    }
}
