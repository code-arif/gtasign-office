<?php

namespace App\Http\Controllers\Api\Frontend;

use Exception;
use App\Models\Contact;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Mail\ContactSubmittedMail;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    use ApiResponse;

    public function submitContact(Request $request)
    {
        // Validation according to UI
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'company_name' => 'required|string|max:150',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'required|email|max:150',
            'subject'      => 'required|string|max:150',
            'message'      => 'required|string|max:2000',
        ]);

        DB::beginTransaction();

        try {
            // Create Contact (status will be default = active)
            $contact = Contact::create($data);

            // Send Mail to Admin
            Mail::to(config('mail.from.address'))
                ->queue(new ContactSubmittedMail($contact));

            DB::commit();

            return $this->success(
                'Contact form submitted successfully!',
                $contact,
                201
            );
        } catch (Exception $e) {

            DB::rollBack();

            Log::error('Contact form submit failed', [
                'error' => $e->getMessage(),
                'payload' => $data,
            ]);

            return $this->error(
                [],
                'Something went wrong. Please try again later.',
                500
            );
        }
    }
}
