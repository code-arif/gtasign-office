<?php

namespace App\Http\Controllers\Web\Backend\CMS\Web\PrivacyTerms;

use Illuminate\Http\Request;
use App\Models\PrivecyAndTerms;
use App\Http\Controllers\Controller;

class PrivacAndTermsController extends Controller
{
    public function termsAndCondition()
    {
        $terms = PrivecyAndTerms::where('type', 'terms')->first();
        return view('backend.layouts.privacyandterms.terms_condition', compact('terms'));
    }

    public function termsAndConditionUpdate(Request $request)
    {
        $request->validate([
            'description' => 'required',
        ]);

        // Find existing record with type 'terms'
        $terms = PrivecyAndTerms::where('type', 'terms')->first();

        if ($terms) {
            // Update existing record
            $terms->description = $request->description;
        } else {
            // Create new record
            $terms = new PrivecyAndTerms();
            $terms->type = 'terms';
            $terms->description = $request->description;
        }

        $terms->save();

        return redirect()->back()->with('success', 'Terms and Conditions updated successfully.');
    }


    public function privacyPolicy()
    {
        $privacy = PrivecyAndTerms::where('type', 'privacy')->first();
        return view('backend.layouts.privacyandterms.privacy_policy', compact('privacy'));
    }

    public function privacyPolicyUpdate(Request $request)
    {
        $request->validate([
            'description' => 'required',
        ]);

        // Find existing record with type 'privacy'
        $privacy = PrivecyAndTerms::where('type', 'privacy')->first();

        if ($privacy) {
            // Update existing record
            $privacy->description = $request->description;
        } else {
            // Create new record
            $privacy = new PrivecyAndTerms();
            $privacy->type = 'privacy';
            $privacy->description = $request->description;
        }

        $privacy->save();

        return redirect()->back()->with('success', 'Privacy Policy updated successfully.');
    }

    /*
    * Veiw privecy policy page
    */
    public function getTerms()
    {
        // dd('hello');
        $page = PrivecyAndTerms::where('type', 'terms')->first();

        if (!$page) {
            abort(404, 'Terms & Condition page not found');
        }

        return view('backend.layouts.privacyandterms.terms', compact('page'));
    }

    /**
     * View terms page
     */
    public function getPrivecy()
    {
        // dd('hello');
        $page = PrivecyAndTerms::where('type', 'privacy')->first();

        if (!$page) {
            abort(404, 'Privacy Policy not found');
        }

        return view('backend.layouts.privacyandterms.privecy', compact('page'));
    }
}
