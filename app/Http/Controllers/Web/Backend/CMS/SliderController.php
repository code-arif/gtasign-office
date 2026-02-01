<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use Exception;
use App\Models\CMS;
use App\Models\Slider;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class SliderController extends Controller
{
    // Show slider list
    public function index(Request $request)
    {
        $data = CMS::where('page', 'home')->where('section', 'partner')->where('name', 'item')->first();
        $sliders = Slider::orderBy('order', 'asc')->get();

        return view("backend.layouts.cms.home.slider", compact(["data", "sliders"]));
    }

    /**
     * update hero section
     **/
    public function headerUpdate(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'home')
                ->where('section', 'partner')
                ->where('name', 'item')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'partner',
                    'name' => 'item'
                ],
                $validated_data
            );

            return back()->with('t-success', 'Content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }


    // Store new slider
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:png,jpg,jpeg,webp|max:2048',
            'status' => 'nullable|boolean',
            'link' => 'nullable|url'
        ]);

        try {
            $data = [];

            // Handle Image Upload
            if ($request->hasFile('image')) {
                $data['image'] = Helper::fileUpload($request->file('image'), 'sliders');
            }

            $data['status'] = $request->has('status') ? true : false;
            $data['order'] = Slider::max('order') + 1;
            $data['link'] = $request->input('link');

            Slider::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Slider added successfully!'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Slider Store Failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add slider. Please try again.'
            ], 500);
        }
    }

    // Update slider status
    public function updateStatus(Request $request, $id)
    {
        try {
            $slider = Slider::findOrFail($id);
            $slider->status = $request->status;
            $slider->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully!'
            ], 200);
        } catch (Exception $e) {
            Log::error('Slider Status Update Failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.'
            ], 500);
        }
    }

    // Delete slider
    public function destroy($id)
    {
        try {
            $slider = Slider::findOrFail($id);

            // Delete image file
            if ($slider->image) {
                Helper::fileDelete($slider->image);
            }

            $slider->delete();

            return response()->json([
                'success' => true,
                'message' => 'Slider deleted successfully!'
            ], 200);
        } catch (Exception $e) {
            Log::error('Slider Delete Failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete slider.'
            ], 500);
        }
    }

    // Update slider order
    public function updateOrder(Request $request)
    {
        try {
            $orders = $request->orders;

            foreach ($orders as $order) {
                Slider::where('id', $order['id'])->update(['order' => $order['position']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully!'
            ], 200);
        } catch (Exception $e) {
            Log::error('Slider Order Update Failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update order.'
            ], 500);
        }
    }
}
