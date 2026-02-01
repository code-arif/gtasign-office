<?php

namespace App\Http\Controllers\Web\Backend\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class UserManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Optimized query with eager loading and selective columns
            $query = User::query()
                ->select([
                    'users.id',
                    'users.first_name',
                    'users.last_name',
                    'users.username',
                    'users.email',
                    'users.phone',
                    'users.avatar',
                    'users.status',
                    'users.created_at',
                    'users.last_activity_at'
                ])
                ->with(['roles:id,name']) // Eager load roles with only needed columns
                ->orderBy('users.id', 'desc');
            // Role filter
            if ($request->filled('role')) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('name', $request->role);
                });
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Date range filter
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->filterColumn('first_name', function ($query, $keyword) {
                    $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$keyword}%");
                })
                ->addColumn('full_name', function ($data) {
                    $fullName = trim($data->first_name . ' ' . $data->last_name);
                    $avatar = $data->avatar
                        ? asset($data->avatar)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=random';

                    return '<div class="d-flex align-items-center">
                                <img src="' . $avatar . '" alt="avatar" class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                                <div>
                                    <div class="fw-semibold">' . e($fullName) . '</div>
                                    <small class="text-muted">' . e($data->username) . '</small>
                                </div>
                            </div>';
                })
                ->addColumn('contact', function ($data) {
                    $email = '<div class="text-break"><i class="fe fe-mail me-1"></i>' . e($data->email) . '</div>';
                    $phone = $data->phone
                        ? '<div class="text-muted mt-1"><i class="fe fe-phone me-1"></i>' . e($data->phone) . '</div>'
                        : '';
                    return $email . $phone;
                })
                ->addColumn('roles', function ($data) {
                    if ($data->roles->isEmpty()) {
                        return '<span class="badge bg-secondary">No Role</span>';
                    }

                    $badges = '';
                    $colors = [
                        'admin' => 'danger',
                        'director' => 'primary',
                        'referee' => 'success',
                        'evaluator' => 'info'
                    ];

                    foreach ($data->roles as $role) {
                        $color = $colors[$role->name] ?? 'secondary';
                        $badges .= '<span class="badge bg-' . $color . ' me-1">' . e($role->name) . '</span>';
                    }
                    return $badges;
                })
                ->addColumn('last_active', function ($data) {
                    if ($data->last_activity_at) {
                        $diff = $data->last_activity_at->diffForHumans();
                        $isRecent = $data->last_activity_at->gt(now()->subMinutes(15));
                        $indicator = $isRecent
                            ? '<span class="badge badge-dot bg-success me-1"></span>'
                            : '<span class="badge badge-dot bg-secondary me-1"></span>';
                        return $indicator . '<small>' . e($diff) . '</small>';
                    }
                    return '<small class="text-muted">Never</small>';
                })
                ->addColumn('status', function ($data) {
                    $backgroundColor = $data->status == "active" ? '#00AEEF' : '#ccc';
                    $sliderTranslateX = $data->status == "active" ? '26px' : '2px';
                    $sliderStyles = "position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: white; border-radius: 50%; transition: transform 0.3s ease; transform: translateX($sliderTranslateX);";

                    $status = '<div class="form-check form-switch" style="margin-left:40px; position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;"' . ($data->status == 'active' ? ' checked' : '') . '>';
                    $status .= '<span style="' . $sliderStyles . '"></span>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label" style="margin-left: 10px;"></label>';
                    $status .= '</div>';

                    return $status;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                <a href="#" type="button" onclick="goToView(' . $data->id . ')" class="btn btn-success fs-14 text-white" title="View Details">
                                    <i class="fe fe-eye"></i>
                                </a>
                                <a href="#" type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger fs-14 text-white" title="Delete User">
                                    <i class="fe fe-trash"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['full_name', 'contact', 'roles', 'last_active', 'status', 'action'])
                ->make(true);
        }

        // Get role counts for filter
        $roleCounts = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('COUNT(*) as count'))
            ->groupBy('roles.name')
            ->pluck('count', 'name');

        return view("backend.layouts.users.index", compact('roleCounts'));
    }

    /**
     * User Details
     */
    public function show($id)
    {
        $user = User::with(['roles', 'permissions'])->findOrFail($id);
        return view("backend.layouts.users.show", compact('user'));
    }

    /**
     * Delete User
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent self-deletion
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account!'
                ], 403);
            }

            // Soft delete
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle User Status
     */
    public function status($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent self-deactivation
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot change your own status!'
                ], 403);
            }

            $user->status = $user->status === 'active' ? 'inactive' : 'active';
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export users to CSV or Excel
     */
    public function export(Request $request)
    {
        try {
            $request->validate([
                'format' => 'required|in:csv,excel',
                'fields' => 'required|array',
                'fields.*' => 'in:name,username,email,phone,roles,status,created_at'
            ]);

            // Build query with filters
            $query = User::query()
                ->with(['roles:id,name'])
                ->orderBy('id', 'desc');

            // Apply filters if requested
            if ($request->filled('role')) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('name', $request->role);
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $users = $query->get();

            if ($users->isEmpty()) {
                return response()->json(['message' => 'No users found to export'], 404);
            }

            // Prepare data based on selected fields
            $exportData = [];
            $fields = $request->fields;

            // Add header row
            $headers = [];
            foreach ($fields as $field) {
                $headers[] = ucfirst(str_replace('_', ' ', $field));
            }
            $exportData[] = $headers;

            // Add data rows
            foreach ($users as $user) {
                $row = [];
                foreach ($fields as $field) {
                    switch ($field) {
                        case 'name':
                            $row[] = trim($user->first_name . ' ' . $user->last_name);
                            break;
                        case 'username':
                            $row[] = $user->username;
                            break;
                        case 'email':
                            $row[] = $user->email;
                            break;
                        case 'phone':
                            $row[] = $user->phone ?? 'N/A';
                            break;
                        case 'roles':
                            $row[] = $user->roles->pluck('name')->implode(', ') ?: 'No Role';
                            break;
                        case 'status':
                            $row[] = ucfirst($user->status);
                            break;
                        case 'created_at':
                            $row[] = $user->created_at;
                            break;
                    }
                }
                $exportData[] = $row;
            }

            // Generate file based on format
            if ($request->format === 'csv') {
                return $this->generateCSV($exportData);
            } else {
                return $this->generateExcel($exportData);
            }
        } catch (\Exception $e) {
            Log::error('Export Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to export users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate CSV file
     */
    private function generateCSV($data)
    {
        $filename = 'users_export_' . date('Y-m-d_His') . '.csv';

        $handle = fopen('php://temp', 'r+');

        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Generate Excel file using PhpSpreadsheet
     */
    private function generateExcel($data)
    {
        $filename = 'users_export_' . date('Y-m-d_His') . '.xlsx';

        // Check if PhpSpreadsheet is available
        if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            // Fallback to CSV if PhpSpreadsheet not installed
            return $this->generateCSV($data);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers style
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '007bff']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ];

        // Populate data
        $rowNumber = 1;
        foreach ($data as $index => $row) {
            $columnLetter = 'A';
            foreach ($row as $cell) {
                $sheet->setCellValue($columnLetter . $rowNumber, $cell);

                // Apply header style to first row
                if ($index === 0) {
                    $sheet->getStyle($columnLetter . $rowNumber)->applyFromArray($headerStyle);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }

                $columnLetter++;
            }
            $rowNumber++;
        }

        // Add borders
        $lastColumn = chr(64 + count($data[0]));
        $lastRow = count($data);
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        // Create writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Save to output
        $temp_file = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($temp_file);

        return response()->download($temp_file, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ])->deleteFileAfterSend(true);
    }
}
