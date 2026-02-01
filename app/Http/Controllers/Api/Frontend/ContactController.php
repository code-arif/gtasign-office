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
        $data = $request->validate([
            'name'    => 'nullable|string|max:50',
            'email'   => 'required|email|max:100',
            'subject' => 'nullable|string|max:100',
            'message' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // Save contact
            $contact = Contact::create($data);

            // Send mail (admin / support)
            Mail::to(config('mail.from.address'))->queue(new ContactSubmittedMail($contact));

            DB::commit();

            return $this->success(
                $contact,
                'Contact form submitted successfully!',
                201
            );
        } catch (Exception $e) {

            DB::rollBack();

            Log::error('Contact form submit failed', [
                'error' => $e->getMessage(),
                'data'  => $data,
            ]);

            return $this->error(
                [],
                'Something went wrong. Please try again later.',
                500
            );
        }
    }
}
