<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use App\Enums\PageEnum;
use App\Enums\SectionEnum;
use App\Services\CmsService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;


class OrderAndDeliveryController extends Controller
{
    protected $cmsService;

    public $name = "home";
    public $section = "orders";
    public $page = PageEnum::HOME;
    public $item = SectionEnum::ORDER_AND_DELIVERY_CONTENT;
    public $items = SectionEnum::ORDER_AND_DELIVERY_ITEMS;
    public $count = 20;

    public function __construct(CmsService $cmsService)
    {
        $this->cmsService = $cmsService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CMS::where('page', $this->page)->where('section', $this->items)->latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($data) {
                    if ($data->image) {
                        $url = asset($data->image);
                        return '<img src="' . $url . '" alt="image" width="50px" height="50px" style="margin-left:20px;">';
                    } else {
                        return '<span>No Image Available</span>';
                    }
                })
                ->addColumn('status', function ($data) {
                    $backgroundColor = $data->status == "active" ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->status == "active" ? '26px' : '2px';
                    $sliderStyles = "position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: white; border-radius: 50%; transition: transform 0.3s ease; transform: translateX($sliderTranslateX);";

                    $status = '<div class="form-check form-switch" style="margin-left:40px; position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;">';
                    $status .= '<span style="' . $sliderStyles . '"></span>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label" style="margin-left: 10px;"></label>';
                    $status .= '</div>';

                    return $status;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                <a href="#" onClick="editItem(' . $data->id . ')" type="button" class="btn btn-primary fs-14 text-white edit-icn" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>

                                <a href="#" type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['image', 'status', 'action'])
                ->make();
        }

        $data = CMS::where('page', $this->page)->where('section', $this->item)->latest()->first();
        return view("backend.layouts.cms.{$this->name}.{$this->section}.index", ["data" => $data, "name" => $this->name, "section" => $this->section]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("backend.layouts.cms.{$this->name}.{$this->section}.create", ["name" => $this->name, "section" => $this->section]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'name'              => 'nullable|string|max:50',
            'title'             => 'nullable|string|max:255',
            'sub_title'         => 'nullable|string|max:255',
            'description'       => 'nullable|string',
            'sub_description'   => 'nullable|string',
            'bg'                => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'image'             => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'btn_text'          => 'nullable|string|max:50',
            'btn_link'          => 'nullable|string|max:100',
            'btn_color'         => 'nullable|string|max:50',
            'rating'            => 'nullable|integer|between:1,5'
        ]);


        try {
            // Add the page and section to validated data
            $validatedData['page'] = $this->page;
            $validatedData['section'] = $this->items;

            $counting = CMS::where('page', $validatedData['page'])->where('section', $validatedData['section'])->count();
            if ($counting >= $this->count) {
                return redirect()->back()->with('t-error', "Maximum  {$this->count} Item You Can Add");
            }

            if ($request->hasFile('bg')) {
                $validatedData['bg'] = Helper::fileUpload($request->file('bg'), $this->section, time() . '_' . $request->file('bg'));
            }

            if ($request->hasFile('image')) {
                $validatedData['image'] = Helper::fileUpload($request->file('image'), $this->section, time() . '_' . $request->file('image'));
            }



            // Create or update the CMS entry
            CMS::create($validatedData);

            return redirect()->route("admin.cms.{$this->name}.{$this->section}.index")->with('t-success', 'Created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = CMS::findOrFail($id);
        return view("backend.layouts.cms.{$this->name}.{$this->section}.update", ["data" => $data, "name" => $this->name, "section" => $this->section]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = CMS::findOrFail($id);
        return view("backend.layouts.cms.{$this->name}.{$this->section}.update", ["data" => $data, "name" => $this->name, "section" => $this->section]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name'              => 'nullable|string|max:50',
            'title'             => 'nullable|string|max:255',
            'sub_title'         => 'nullable|string|max:255',
            'description'       => 'nullable|string',
            'sub_description'   => 'nullable|string',
            'bg'                => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'image'             => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'btn_text'          => 'nullable|string|max:50',
            'btn_link'          => 'nullable|string|max:100',
            'btn_color'         => 'nullable|string|max:50',
            'rating'            => 'nullable|integer|between:1,5'
        ]);

        try {
            // Find the existing CMS record by ID
            $section = CMS::findOrFail($id);

            // Update the page and section if necessary
            $validatedData['page'] = $this->page;
            $validatedData['section'] = $this->items;

            if($request->hasFile('bg')) {
                if ($section->bg && file_exists(public_path($section->bg))) {
                    Helper::fileDelete(public_path($section->bg));
                }
                $validatedData['bg'] = Helper::fileUpload($request->file('bg'), $this->section, time() . '_' . $request->file('bg'));
            }

            if ($request->hasFile('image')) {
                if ($section->image && file_exists(public_path($section->image))) {
                    Helper::fileDelete(public_path($section->image));
                }
                $validatedData['image'] = Helper::fileUpload($request->file('image'), $this->section, time() . '_' . $request->file('image'));
            }

            // Update the meta data
            if($request->has('rating')) {
                $validatedData['metadata']['rating'] = $validatedData['rating'];
                unset($validatedData['rating']);
            }

            // Update the CMS entry with the validated data
            $section->update($validatedData);

            return redirect()->route("admin.cms.{$this->name}.{$this->section}.index")->with('t-success', 'Updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $cms = CMS::findOrFail($id);
            // Delete associated images
            if ($cms->bg && file_exists(public_path($cms->bg))) {
                Helper::fileDelete(public_path($cms->bg));
            }
            if ($cms->image && file_exists(public_path($cms->image))) {
                Helper::fileDelete(public_path($cms->image));
            }
            $cms->delete();







            return response()->json([
                't-success' => true,
                'message' => 'Deleted successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                't-success' => false,
                'message' => 'Failed to delete.',
            ]);
        }
    }

    public function status(int $id): JsonResponse
    {
        try {
            $this->cmsService->status($id);
            return response()->json([
                't-success' => true,
                'message' => 'Updated successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                't-success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function content(Request $request)
    {
        $validatedData = $request->validate([
            'sub_description'       => 'nullable|string',
            'description'       => 'nullable|string',
        ]);

        try {
            $validatedData['page'] = $this->page;
            $validatedData['section'] = $this->item;
            $section = CMS::where('page', $this->page)->where('section', $this->item)->first();






            if ($section) {
                CMS::where('page', $validatedData['page'])->where('section', $validatedData['section'])->update($validatedData);
            } else {
                CMS::create($validatedData);
            }

            return redirect()->route("admin.cms.{$this->name}.{$this->section}.index")->with('t-success', 'Updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }

}
