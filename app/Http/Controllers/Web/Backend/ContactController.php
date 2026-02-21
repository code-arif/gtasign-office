<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Contact::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()

                // Show Name + Company
                ->addColumn('user', function ($row) {
                    return '<strong>' . $row->name . '</strong><br>
                            <small class="text-muted">' . $row->company_name . '</small>';
                })

                // Email Column
                ->addColumn('email', function ($row) {
                    return '<a href="mailto:' . $row->email . '">' . $row->email . '</a>';
                })

                // Phone Column
                ->addColumn('phone', function ($row) {
                    return $row->phone ?? '<span class="text-muted">N/A</span>';
                })

                // Subject Badge
                ->addColumn('subject', function ($row) {
                    return '<span class="badge bg-info">' . $row->subject . '</span>';
                })

                // Short Message Preview
                ->addColumn('message', function ($row) {
                    return Str::limit($row->message, 60);
                })

                // Status Toggle (your design kept)
                ->addColumn('status', function ($row) {

                    $checked = $row->status == "active" ? "checked" : "";
                    $backgroundColor = $row->status == "active" ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $row->status == "active" ? '26px' : '2px';

                    return '
                        <div class="form-check form-switch"
                             style="margin-left:40px; position: relative; width: 50px; height: 24px;
                             background-color: ' . $backgroundColor . ';
                             border-radius: 12px; transition: 0.3s;">

                            <input ' . $checked . '
                                   onclick="showStatusChangeAlert(' . $row->id . ')"
                                   type="checkbox"
                                   style="position:absolute;width:100%;height:100%;opacity:0;cursor:pointer">

                            <span style="
                                position:absolute;top:2px;left:2px;width:20px;height:20px;
                                background:white;border-radius:50%;
                                transform:translateX(' . $sliderTranslateX . ');
                                transition:0.3s;"></span>
                        </div>';
                })

                // Reply Button (NEW)
                ->addColumn('action', function ($row) {

                    $subject = urlencode("Reply Regarding: " . $row->subject);
                    $body = urlencode(
                        "Hello " . $row->name . ",\n\n" .
                            "Thank you for contacting us.\n\n" .
                            "------------------------------\n" .
                            $row->message . "\n" .
                            "------------------------------\n\n"
                    );

                    $mailto = "mailto:{$row->email}?subject={$subject}&body={$body}";

                    return '
                        <div class="btn-group btn-group-sm">

                            <a href="' . $mailto . '"
                               class="btn btn-success d-inline-flex align-items-center"
                               title="Reply via Email">
                                <i class="fe fe-mail me-1"></i> Reply
                            </a>

                        </div>';
                })

                ->rawColumns(['user', 'email', 'subject', 'message', 'status', 'action'])
                ->make(true);
        }

        return view("backend.layouts.contact");
    }


    public function status(int $id): JsonResponse
    {
        $data = Contact::findOrFail($id);

        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();

        return response()->json([
            'status' => 't-success',
            'message' => 'Status updated successfully!',
        ]);
    }
}
