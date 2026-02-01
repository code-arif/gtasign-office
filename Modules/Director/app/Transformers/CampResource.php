<?php

namespace Modules\Director\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampResource extends JsonResource
{
    public function toArray($request)
    {
        $checkin = null;
        $evaluatorRegistration = null;

        if (auth('api')->check()) {
            $user = auth('api')->user();

            if ($user->hasRole('referee')) {
                $checkin = $this->checkedInReferees->first();
            }

            if ($user->hasRole('evaluator')) {
                $evaluatorRegistration = $this->evaluatorRegistrations->first();
            }
        }

        return [
            'id'                => $this->id,
            'camp_name'         => $this->camp_name,
            'camp_logo'         => $this->camp_logo ? asset($this->camp_logo) : asset('default/no_image.webp'),
            'location'          => $this->location,
            'start_date'        => $this->start_date->todateString(),
            'end_date'          => $this->end_date->todateString(),
            'camp_details'      => $this->camp_details,
            'price'             => number_format($this->price, 2),
            'sports_type_id'    => $this->sports_type_id,
            'sports_type_name'  => $this->sports_type_name,
            'status'            => $this->status,
            'created_at'        => $this->created_at->format('Y-m-d H:i:s'),
            'timezone'          => $this->timezone,

            // NEW: Day-wise date range list
            'date_range'        => $this->generateDateRange($this->start_date, $this->end_date),

            'sport'             => [
                'id'            => $this->sportsType->id ?? null,
                'sports_name'   => $this->sportsType->sports_name ?? null,
                'icon'          => $this->sportsType && $this->sportsType->icon ? asset($this->sportsType->icon) : asset('default/no_image.webp'),
            ],

            // Director information added here
            'director'          => [
                'id'         => $this->director->id ?? null,
                'name'       => $this->director
                    ? trim($this->director->first_name . ' ' . $this->director->last_name)
                    : null,
                'avatar'     => $this->director && $this->director->avatar
                    ? asset($this->director->avatar)
                    : asset('default/profile.jpg'),
                'email'      => $this->director->email ?? null,
                'phone'      => $this->director->phone ?? null,
                'address'    => $this->director->address ?? null,
            ],

            $this->mergeWhen(
                auth('api')->check() && auth('api')->user()->hasRole('referee'),
                function () use ($checkin) {
                    return [
                        'referee_registration_status' => optional($checkin)->registration_status,
                        'registered_at'               => optional($checkin)->registered_at,
                        'checked_in_at'               => optional($checkin)->checked_in_at,
                    ];
                }
            ),

            // Evaluator registration status
            $this->mergeWhen(
                auth('api')->check() && auth('api')->user()->hasRole('evaluator'),
                function () use ($evaluatorRegistration) {
                    return [
                        'evaluator_registration_status' => optional($evaluatorRegistration)->status,
                        'registration_note'             => optional($evaluatorRegistration)->registration_note,
                        'rejection_reason'              => optional($evaluatorRegistration)->rejection_reason,
                        'registered_at'                 => optional($evaluatorRegistration)->registered_at,
                        'approved_at'                   => optional($evaluatorRegistration)->approved_at,
                        'rejected_at'                   => optional($evaluatorRegistration)->rejected_at,
                        'can_view_own_evaluations'      => optional($evaluatorRegistration)->can_view_own_evaluations ?? false,
                    ];
                }
            ),
        ];
    }

    /**
     * Generate date range array
     */
    private function generateDateRange($start, $end)
    {
        $dates = [];

        $startDate = Carbon::parse($start);
        $endDate   = Carbon::parse($end);

        while ($startDate->lte($endDate)) {
            $dates[] = [
                'day' => $startDate->format('F d'),
            ];
            $startDate->addDay();
        }

        return $dates;
    }
}
