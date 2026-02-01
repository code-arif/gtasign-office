<?php

namespace Modules\Director\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class ScheduleCreateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'game_duration' => 'required|integer|min:15|max:240',
            'max_referees_per_slot' => 'required|integer|min:2|max:6',
            'time_ranges' => 'required|array|min:1',
            'time_ranges.*.date' => 'required|date|date_format:Y-m-d',
            'time_ranges.*.start_time' => 'required|date_format:H:i',
            'time_ranges.*.end_time' => 'required|date_format:H:i|after:time_ranges.*.start_time',
            'locations' => 'required|array|min:1',
            'locations.*.location_name' => 'required|string|max:255',
            'locations.*.court_count' => 'required|integer|min:1|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];
    }

    public function messages()
    {
        return [
            'game_duration.required' => 'Game duration is required.',
            'game_duration.min' => 'Game duration must be at least 15 minutes.',
            'game_duration.max' => 'Game duration cannot exceed 240 minutes.',
            'time_ranges.required' => 'At least one time range is required.',
            'time_ranges.*.date.required' => 'Date is required for each time range.',
            'time_ranges.*.start_time.required' => 'Start time is required.',
            'time_ranges.*.end_time.after' => 'End time must be after start time.',
            'locations.required' => 'At least one location is required.',
            'locations.*.location_name.required' => 'Location name is required.',
            'locations.*.court_count.required' => 'Court count is required.',
            'locations.*.court_count.min' => 'At least 1 court is required.',
            'locations.*.court_count.max' => 'Maximum 20 courts allowed per location.'
        ];
    }

    /**
     * Validate that time ranges don't overlap for same date
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (isset($this->time_ranges)) {
                $timeRanges = $this->time_ranges;
                $dateGroups = [];

                // Group time ranges by date
                foreach ($timeRanges as $index => $range) {
                    if (!isset($range['date'])) continue;

                    $date = $range['date'];
                    if (!isset($dateGroups[$date])) {
                        $dateGroups[$date] = [];
                    }
                    $dateGroups[$date][] = [
                        'index' => $index,
                        'start' => $range['start_time'] ?? null,
                        'end' => $range['end_time'] ?? null
                    ];
                }

                // Check for overlaps within same date
                foreach ($dateGroups as $date => $ranges) {
                    for ($i = 0; $i < count($ranges); $i++) {
                        for ($j = $i + 1; $j < count($ranges); $j++) {
                            if ($this->timesOverlap(
                                $ranges[$i]['start'],
                                $ranges[$i]['end'],
                                $ranges[$j]['start'],
                                $ranges[$j]['end']
                            )) {
                                $validator->errors()->add(
                                    "time_ranges.{$ranges[$i]['index']}",
                                    "Time ranges overlap on {$date}."
                                );
                            }
                        }
                    }
                }
            }
        });
    }

    private function timesOverlap($start1, $end1, $start2, $end2)
    {
        if (!$start1 || !$end1 || !$start2 || !$end2) return false;

        $start1 = Carbon::parse($start1);
        $end1 = Carbon::parse($end1);
        $start2 = Carbon::parse($start2);
        $end2 = Carbon::parse($end2);

        return $start1->lt($end2) && $start2->lt($end1);
    }
}
