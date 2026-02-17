<?php

namespace App\Http\Controllers\Web\Backend\Gig;

use Exception;
use App\Models\Gig;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class GigManageController extends Controller
{
    /**
     * Gig list page
     */
    public function index(Request $request)
    {
        // Get statistics for cards
        $totalGigs = Gig::withTrashed()->count();
        $activeGigs = Gig::where('status', 'active')->count();
        $pendingGigs = Gig::where('status', 'pending_approval')->count();
        $rejectedGigs = Gig::where('status', 'rejected')->count();
        $draftGigs = Gig::where('status', 'draft')->count();
        $deletedGigs = Gig::onlyTrashed()->count();

        // Get categories for filter
        $categories = Category::select('id', 'name')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('backend.layouts.gigs.gig-list', compact(
            'totalGigs',
            'activeGigs',
            'pendingGigs',
            'rejectedGigs',
            'draftGigs',
            'deletedGigs',
            'categories'
        ));
    }

    /**
     * Get all gigs data for DataTable
     */
    public function getData(Request $request)
    {
        if ($request->ajax() && $request->wantsJson()) {
            $query = Gig::query()
                ->select([
                    'gigs.id',
                    'gigs.title',
                    'gigs.user_id',
                    'gigs.category_id',
                    'gigs.sub_category_id',
                    'gigs.price',
                    'gigs.delivery_days',
                    'gigs.status',
                    'gigs.published_at',
                    'gigs.created_at',
                    'gigs.deleted_at'
                ])
                ->with([
                    'user.profile:id,user_id,first_name,last_name,avatar',
                    'category:id,name',
                    'subCategory:id,name',
                    'images' => function ($query) {
                        $query->select('id', 'gig_id', 'path', 'is_primary')
                            ->where('is_primary', true)
                            ->limit(1);
                    }
                ]);

            // Include soft deleted
            if ($request->filled('show_deleted') && $request->show_deleted === 'true') {
                $query->withTrashed();
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('gigs.status', $request->status);
            }

            // Category filter
            if ($request->filled('category_id')) {
                $query->where('gigs.category_id', $request->category_id);
            }

            // Sub-category filter
            if ($request->filled('sub_category_id')) {
                $query->where('gigs.sub_category_id', $request->sub_category_id);
            }

            // Price range filter
            if ($request->filled('min_price')) {
                $query->where('gigs.price', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('gigs.price', '<=', $request->max_price);
            }

            // Delivery days filter
            if ($request->filled('max_delivery_days')) {
                $query->where('gigs.delivery_days', '<=', $request->max_delivery_days);
            }

            // Date range filter
            if ($request->filled('date_from')) {
                $query->whereDate('gigs.created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('gigs.created_at', '<=', $request->date_to);
            }

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('gigs.title', 'like', "%{$search}%")
                        ->orWhere('gigs.scope', 'like', "%{$search}%")
                        ->orWhereHas('user.profile', function ($q) use ($search) {
                            $q->where(DB::raw("CONCAT(first_name, ' ', ' ', COALESCE(last_name, ''))"), 'like', "%{$search}%");
                        });
                });
            }

            $query->orderBy('gigs.id', 'desc');

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->filterColumn('title', function ($query, $keyword) {
                    $query->where('gigs.title', 'like', "%{$keyword}%");
                })
                ->addColumn('gig_info', function ($data) {
                    $primaryImage = $data->images->first();
                    $imageUrl = $primaryImage
                        ? asset('/' . $primaryImage->path)
                        : asset('default/no_image.webp');

                    $isDeleted = $data->deleted_at ? '<span class="badge bg-danger ms-2">Deleted</span>' : '';

                    return '<div class="d-flex align-items-center">
                                <img src="' . $imageUrl . '" alt="gig" class="rounded me-3 flex-shrink-0"
                                     width="60" height="60" style="object-fit: cover;">
                                <div class="text-truncate">
                                    <div class="fw-semibold text-truncate" title="' . e($data->title) . '">
                                        ' . e($data->title) . $isDeleted . '
                                    </div>
                                    <small class="text-muted">ID: ' . $data->id . '</small>
                                </div>
                            </div>';
                })
                ->addColumn('seller', function ($data) {
                    $profile = $data->user->profile ?? null;

                    if (!$profile) {
                        return '<span class="text-muted">No Profile</span>';
                    }

                    $fullName = trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
                    $avatar = $profile->avatar
                        ? asset($profile->avatar)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=random';

                    return '<div class="d-flex align-items-center">
                                <img src="' . $avatar . '" alt="avatar" class="rounded-circle me-2 flex-shrink-0"
                                     width="32" height="32" style="object-fit: cover;">
                                <div class="text-truncate">
                                    <div class="fw-semibold text-truncate" style="max-width: 150px;" title="' . e($fullName) . '">' . e($fullName) . '</div>
                                    <small class="text-muted">' . e($data->user->email) . '</small>
                                </div>
                            </div>';
                })
                ->addColumn('category', function ($data) {
                    $categoryName = $data->category->name ?? 'N/A';
                    $subCategoryName = $data->subCategory->name ?? '';

                    $output = '<div class="fw-semibold">' . e($categoryName) . '</div>';
                    if ($subCategoryName) {
                        $output .= '<small class="text-muted">' . e($subCategoryName) . '</small>';
                    }

                    return $output;
                })
                ->addColumn('pricing', function ($data) {
                    return '<div class="text-center">
                                <div class="fw-bold text-success">$' . number_format($data->price, 2) . '</div>
                                <small class="text-muted">' . $data->delivery_days . ' days</small>
                            </div>';
                })
                ->addColumn('status', function ($data) {
                    $statusColors = [
                        'draft' => 'secondary',
                        'pending_approval' => 'warning',
                        'active' => 'success',
                        'rejected' => 'danger'
                    ];

                    $color = $statusColors[$data->status] ?? 'secondary';
                    $statusText = ucfirst(str_replace('_', ' ', $data->status));

                    return '<span class="badge bg-' . $color . ' p-2">' . e($statusText) . '</span>';
                })
                ->addColumn('published_at', function ($data) {
                    if ($data->published_at) {
                        return '<small>' . $data->published_at->format('M d, Y') . '<br>' .
                            $data->published_at->format('h:i A') . '</small>';
                    }
                    return '<span class="text-muted">Not published</span>';
                })
                ->addColumn('action', function ($data) {
                    $viewBtn = '<a href="' . route('admin.gigs.show', $data->id) . '" class="btn btn-primary btn-sm me-1" title="View Details">
                                    <i class="fe fe-eye"></i>
                                </a>';

                    $statusBtn = '';
                    if (!$data->deleted_at) {
                        $statusBtn = '<div class="btn-group">
                                        <button type="button" class="btn btn-info btn-sm dropdown-toggle"
                                                data-bs-toggle="dropdown" title="Change Status">
                                            <i class="fe fe-refresh-cw"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="changeGigStatus(' . $data->id . ', \'active\')">
                                                <i class="fe fe-check-circle text-success me-2"></i>Approve</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="changeGigStatus(' . $data->id . ', \'rejected\')">
                                                <i class="fe fe-x-circle text-danger me-2"></i>Reject</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="changeGigStatus(' . $data->id . ', \'draft\')">
                                                <i class="fe fe-file text-secondary me-2"></i>Set Draft</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="changeGigStatus(' . $data->id . ', \'pending_approval\')">
                                                <i class="fe fe-clock text-warning me-2"></i>Set Pending</a></li>
                                        </ul>
                                    </div>';
                    }

                    return '<div class="d-flex">' . $viewBtn . $statusBtn . '</div>';
                })
                ->rawColumns(['gig_info', 'seller', 'category', 'pricing', 'status', 'published_at', 'action'])
                ->make(true);
        }
    }

    /**
     * Show gig details
     */
    public function show($id)
    {
        $gig = Gig::withTrashed()
            ->with([
                'user.profile',
                'category',
                'subCategory',
                'images',
                'documents',
                'tags'
            ])
            ->findOrFail($id);

        // Get all gigs for sidebar navigation
        $gigs = Gig::withTrashed()
            ->with(['user.profile:id,user_id,first_name,last_name,avatar'])
            ->select('id', 'user_id', 'title', 'status')
            ->orderBy('id', 'desc')
            ->get();

        return view('backend.layouts.gigs.gig-details', compact('gig', 'gigs'));
    }

    /**
     * Change gig status
     */
    public function changeStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:draft,pending_approval,active,rejected',
                'rejection_reason' => 'required_if:status,rejected|nullable|string|max:500'
            ]);

            $gig = Gig::withTrashed()->findOrFail($id);

            // Don't allow status change for deleted gigs
            if ($gig->deleted_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot change status of deleted gig'
                ], 422);
            }

            DB::beginTransaction();

            $updateData = ['status' => $request->status];

            // Set published_at when approving
            if ($request->status === 'active' && !$gig->published_at) {
                $updateData['published_at'] = now();
            }

            // Add rejection reason if rejecting
            if ($request->status === 'rejected') {
                $updateData['rejection_reason'] = $request->rejection_reason;
            } else {
                $updateData['rejection_reason'] = null;
            }

            $gig->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gig status updated successfully',
                'status' => $request->status
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gig status change error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update gig status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sub-categories by category
     */
    public function getSubCategories($categoryId)
    {
        try {
            $subCategories = Category::where('parent_id', $categoryId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subCategories
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sub-categories'
            ], 500);
        }
    }

    /**
     * Export gigs to CSV
     */
    public function export(Request $request)
    {
        try {
            $query = Gig::with(['user.profile', 'category', 'subCategory']);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('show_deleted') && $request->show_deleted === 'true') {
                $query->withTrashed();
            }

            $gigs = $query->get();

            $filename = 'gigs_export_' . date('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($gigs) {
                $file = fopen('php://output', 'w');

                // Headers
                fputcsv($file, [
                    'ID',
                    'Title',
                    'Seller Name',
                    'Seller Email',
                    'Category',
                    'Sub-Category',
                    'Price',
                    'Delivery Days',
                    'Status',
                    'Published At',
                    'Created At',
                    'Deleted'
                ]);

                // Data
                foreach ($gigs as $gig) {
                    $profile = $gig->user->profile ?? null;
                    $sellerName = $profile
                        ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''))
                        : 'N/A';

                    fputcsv($file, [
                        $gig->id,
                        $gig->title,
                        $sellerName,
                        $gig->user->email ?? 'N/A',
                        $gig->category->name ?? 'N/A',
                        $gig->subCategory->name ?? 'N/A',
                        $gig->price,
                        $gig->delivery_days,
                        ucfirst(str_replace('_', ' ', $gig->status)),
                        $gig->published_at ? $gig->published_at->format('Y-m-d H:i:s') : 'Not Published',
                        $gig->created_at->format('Y-m-d H:i:s'),
                        $gig->deleted_at ? 'Yes' : 'No'
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            Log::error('Gig export error: ' . $e->getMessage());
            return back()->with('error', 'Failed to export gigs');
        }
    }
}
