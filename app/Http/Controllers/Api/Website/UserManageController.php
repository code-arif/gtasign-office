<?php
namespace App\Http\Controllers\Api\Website;

use Carbon\Carbon;
use App\Models\User;
use App\Helper\Helper;
use App\Models\Company;
use App\Models\Employee;
use App\Models\CompanyJob;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\EmployeeSpecialize;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class UserManageController extends Controller
{
    use ApiResponse;

    private function getAuthenticatedCompany()
    {
        $user = auth('api')->user();
        return Company::where('user_id', $user->id)->first();
    }

    public function user_info(Request $request)
    {
        $user = User::with([
            'employee.experiences',
            'employee.certifications',
            'employee.qualifications',
            'employee.specializations.specialize',
            'employee.employee_job_categories.job_category',
            'company',
            'company.company_specializes.specialize',
            'company.company_projects',
        ])->find(auth('api')->id());

        if (! $user) {
            return $this->error([], 'User not found.', 404);
        }

        // Laravel Cashier Subscription
        $subscription = $user->subscription('default');
        $plan         = null;

        if ($subscription) {
            $plan = \App\Models\Plan::where('stripe_price_id', $subscription->stripe_price)->with('features')->first();
        }

        $company         = $user->company;
        $postedJobsCount = $company ? $company->CompanyJobs()->count() : 0;

        // Determine max job posts allowed based on plan
        $maxJobPosts = match ($plan?->slug) {
            'basic'    => 3,
            'pro'      => 10,
            'platinum' => PHP_INT_MAX, // unlimited
            default    => 0,
        };

        $isJobPostPossible = $postedJobsCount < $maxJobPosts;

        $data = [
            'id'                        => $user->id,
            'name'                      => $user->name,
            'email'                     => $user->email,
            'role'                      => $user->role,
            'status'                    => $user->status,
            'age'                       => $user->age,
            'gender'                    => $user->gender,
            'phone_number'              => $user->phone_number,
            'date_of_birth'             => $user->date_of_birth,
            'avatar'                    => $user->avatar,

            'employee_profile_complete' => ! empty($user->employee?->location) && ! empty($user->employee?->bio),
            'employee'                  => $user->employee,

            'company_profile_complete'  => ! empty($user->company?->image_url) && ! empty($user->company?->name) && ! empty($user->company?->display_name) && ! empty($user->company?->bio) && ! empty($user->company?->location),
            'company'                   => $user->company,

            'is_subscribed'             => $user->subscribed('default'),
            'is_cancelled'              => $subscription ? $subscription->canceled() : false,
            'is_subscription_active'    => $subscription?->active() ?? false,
            'is_on_trial'               => $subscription?->onTrial() ?? false,
            'trial_ends_at'             => $subscription?->trial_ends_at?->format('Y-m-d H:i:s'),
            'subscription_ends_at'      => $subscription?->ends_at?->format('Y-m-d H:i:s'),
            'subscription_status'       => $subscription?->stripe_status,

            'subscription_plan_id'      => $plan?->id,
            'subscription_plan'         => $plan->name ?? null,
            'subscription_price'        => $plan->price ?? null,
            'plan_slug'                 => $plan->slug ?? null,

            'is_job_post_possible'      => $isJobPostPossible,
            'job_posted_count'          => $postedJobsCount,
            'max_job_posts_allowed'     => $maxJobPosts,
        ];

        return $this->success($data, 'User Information retrieved successfully.');
    }

    public function user_avatar_update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            ]);

            if ($validator->fails()) {
                return $this->error($validator->errors(), 'Validation Failed', 422);
            }

            $user = auth('api')->user();

            if (! $user) {
                return $this->error([], 'User not authenticated.', 401);
            }

            $employee = $user->employee;
            $company  = $user->company;

            // Handle avatar
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($user->avatar) {
                    Helper::deleteImage($user->avatar);
                }

                // Upload new avatar
                $image  = $request->file('avatar');
                $avatar = Helper::uploadImage($image, 'profile');

                // Save avatar based on user role
                if ($user->role === "employee") {
                    $user->avatar = $avatar;
                    if ($employee) {
                        $employee->image_url = $avatar;
                        $employee->save();
                    }
                } else {
                    $user->avatar = $avatar;
                    if ($company) {
                        $company->image_url = $avatar;
                        $company->save();
                    }
                }
            }

            $user->save();

            $userData = [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'avatar' => $user->avatar ? url($user->avatar) : null,
            ];

            return $this->success($userData, 'Avatar updated successfully.', 200);

        } catch (\Exception $e) {
            Log::error('Profile update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error([], 'An unexpected error occurred. Please try again.', 500);
        }
    }

    // job list for website
    public function featuredJobList(Request $request)
    {

        $perPage = $request->get('per_page', 10);

        $jobs = CompanyJob::with('company')->latest();

        // Apply filters before pagination
        if ($request->has('job_category_ids')) {
            $jobs->whereIn('job_category_id', $request->job_category_ids);
        }

        if ($request->filled('location')) {
            $jobs->where('location', 'LIKE', '%' . $request->location . '%');
        }

        if ($request->filled('certification')) {
            $jobs->where('certification', 'LIKE', '%' . $request->certification . '%');
        }

        if ($request->has('min_salary') && $request->has('max_salary')) {
            $salaryMin = (int) $request->min_salary;
            $salaryMax = (int) $request->max_salary;
            $jobs->whereBetween(DB::raw('CAST(salary AS SIGNED)'), [$salaryMin, $salaryMax]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $jobs->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('location', 'LIKE', '%' . $search . '%')
                    ->orWhere('job_type', 'LIKE', '%' . $search . '%')
                    ->orWhere('salary', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('company', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('display_name', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        // Paginate after filtering
        $paginatedJobs = $jobs->paginate($perPage);

        // Structure the response
        $response = [
            'success' => true,
            'message' => 'Featured Jobs retrieved successfully',
            'data'    => [

                'featured_jobs' => $paginatedJobs->items(),

                'pagination'    => [
                    'current_page' => $paginatedJobs->currentPage(),
                    'last_page'    => $paginatedJobs->lastPage(),
                    'per_page'     => $paginatedJobs->perPage(),
                    'total'        => $paginatedJobs->total(),
                ],
            ],
            'code'    => 200,
        ];

        return response()->json($response, 200);
    }

    public function recommadedJobList(Request $request)
    {
        $user    = auth('api')->user();
        $perPage = $request->get('per_page', 10);

        // Get employee and their specialization IDs
        $employee      = Employee::where('user_id', $user->id)->first();
        $specializeIds = $employee
            ? EmployeeSpecialize::where('employee_id', $employee->id)
            ->pluck('specialize_id')
            ->toArray()
            : [];



        // Base query
        $jobs = CompanyJob::with('company')->latest();



        // Recommended logic — based on employee specialization
        if (! empty($specializeIds)) {
            $jobs->whereIn('job_category_id', $specializeIds);
        }


        // Job category filter (manual filter from frontend)
        if ($request->has('job_category_ids') && is_array($request->job_category_ids)) {
            $jobs->whereIn('job_category_id', $request->job_category_ids);
        }



        //  Location filter
        if ($request->filled('location')) {
            $jobs->where('location', 'LIKE', '%' . $request->location . '%');
        }

        // 🎓 Certification filter (support multiple comma-separated)
        if ($request->filled('certification')) {
            $certifications = array_map('trim', explode(',', $request->certification));
            $jobs->where(function ($q) use ($certifications) {
                foreach ($certifications as $cert) {
                    $q->orWhere('certification', 'LIKE', '%' . $cert . '%');
                }
            });
        }

        // Salary range filter
        if ($request->filled('min_salary') && $request->filled('max_salary')) {
            $salaryMin = (int) $request->min_salary;
            $salaryMax = (int) $request->max_salary;
            $jobs->whereBetween(DB::raw('CAST(salary AS SIGNED)'), [$salaryMin, $salaryMax]);
        }

        //  General search (title, location, job_type, salary, company name)
        if ($request->filled('search')) {
            $search = $request->search;
            $jobs->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('location', 'LIKE', '%' . $search . '%')
                    ->orWhere('job_type', 'LIKE', '%' . $search . '%')
                    ->orWhere('salary', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('company', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('display_name', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        //  Pagination
        $recommendedJobs = $jobs->paginate($perPage);

        // Response
        if ($recommendedJobs->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No recommended jobs found',
                'data'    => [],
                'code'    => 200,
            ]);
        }

        $data = [
            'recommended_jobs' => $recommendedJobs->items(),
            'pagination'       => [
                'current_page' => $recommendedJobs->currentPage(),
                'last_page'    => $recommendedJobs->lastPage(),
                'per_page'     => $recommendedJobs->perPage(),
                'total'        => $recommendedJobs->total(),
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Recommended Jobs retrieved successfully',
            'data'    => $data,
            'code'    => 200,
        ], 200);
    }

    // company job list
    public function companyJobList(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $company = $this->getAuthenticatedCompany();

        if (! $company) {
            return $this->error([], 'Company not found.', 404);
        }

        $search = $request->search;

        $jobsQuery = CompanyJob::with('company')
            ->where('company_id', $company->id)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    if (is_numeric($search)) {
                        $q->where('salary', '>=', (int) $search);
                    } else {
                        $q->where('title', 'LIKE', '%' . $search . '%')
                            ->orWhere('description', 'LIKE', '%' . $search . '%')
                            ->orWhere('location', 'LIKE', '%' . $search . '%')
                            ->orWhere('job_type', 'LIKE', '%' . $search . '%');

                    }
                });
            });

        $paginatedJobs = $jobsQuery->latest()->paginate($perPage);

        if ($paginatedJobs->isEmpty()) {
            return $this->success(null, 'No jobs found for this company.');
        }

        $formattedJobs = $paginatedJobs->map(function ($job) {
            return [
                'id'                 => $job->id,
                'company_id'         => $job->company_id,
                'company_name'       => $job->company->name ?? null,
                'company_logo'       => $job->company->image_url ? url($job->company->image_url) : null,
                'company_location'   => $job->company->location ?? null,
                'title'              => $job->title,
                'description'        => $job->description,
                'location'           => $job->location,
                'salary'             => $job->salary,
                'salary_type'        => $job->salary_type,
                'job_type'           => $job->job_category->title ?? null,
                'benefits'           => $job->benefits,
                'year_of_experience' => $job->year_of_experience,
                'certification'      => $job->certification,
                'education'          => $job->education,
                'created_at'         => Carbon::parse($job->created_at)->format('Y-m-d H:i:s'),

            ];
        });

        $data = [
            'company_jobs' => $formattedJobs,

            'pagination'   => [
                'current_page' => $paginatedJobs->currentPage(),
                'last_page'    => $paginatedJobs->lastPage(),
                'per_page'     => $paginatedJobs->perPage(),
                'total'        => $paginatedJobs->total(),
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Company jobs retrieved successfully.',
            'data'    => $data,
            'code'    => 200,
        ]);
    }

    public function jobApplicantList(Request $request, $id)
    {
        $perPage = $request->get('per_page', 10);

        $company = $this->getAuthenticatedCompany();

        if (! $company) {
            return $this->error([], 'Company not found.', 404);
        }

        $job = CompanyJob::where('company_id', $company->id)->find($id);

        if (! $job) {
            return $this->error([], 'Job not found.', 404);
        }

        $years_of_experience = $request->years_of_experience;
        $job_category_ids    = $request->job_category_ids;
        $certification       = $request->certification;
        $location            = $request->location;
        $age                 = $request->age;
        $search              = $request->search;

        $ageRange = explode('-', $age);
        $minAge   = isset($ageRange[0]) ? (int) $ageRange[0] : null;
        $maxAge   = isset($ageRange[1]) ? (int) $ageRange[1] : null;

        $yearRange     = explode('-', $years_of_experience);
        $minExperience = isset($yearRange[0]) ? (int) $yearRange[0] : null;
        $maxExperience = isset($yearRange[1]) ? (int) $yearRange[1] : null;

        $applicantsQuery = $job->jobApplicants()->with(['employee.experiences', 'employee.user']);

        // Filters
        if ($location || ! empty($job_category_ids) || $minAge || $maxAge || $years_of_experience || $certification) {
            $applicantsQuery->whereHas('employee', function ($query) use ($location, $job_category_ids, $minAge, $maxAge, $minExperience, $maxExperience, $certification) {

                if ($location) {
                    $query->where('address', 'LIKE', '%' . $location . '%');
                }

                if ($minAge || $maxAge) {
                    $query->whereHas('user', function ($q) use ($minAge, $maxAge) {
                        if ($minAge && $maxAge) {
                            $q->whereBetween(DB::raw('CAST(age AS UNSIGNED)'), [$minAge, $maxAge]);
                        } elseif ($minAge) {
                            $q->where(DB::raw('CAST(age AS UNSIGNED)'), '>=', $minAge);
                        } elseif ($maxAge) {
                            $q->where(DB::raw('CAST(age AS UNSIGNED)'), '<=', $maxAge);
                        }
                    });
                }

                if (! empty($job_category_ids)) {
                    $query->whereHas('employee_job_categories', function ($q) use ($job_category_ids) {
                        $q->whereIn('job_category_id', $job_category_ids);
                    });
                }

                if ($minExperience || $maxExperience) {
                    if (isset($minExperience) && isset($maxExperience)) {
                        $query->whereBetween(DB::raw('CAST(year_of_experice AS UNSIGNED)'), [$minExperience, $maxExperience]);
                    } elseif (isset($minExperience)) {
                        $query->where(DB::raw('CAST(year_of_experice AS UNSIGNED)'), '>=', $minExperience);
                    } elseif (isset($maxExperience)) {
                        $query->where(DB::raw('CAST(year_of_experice AS UNSIGNED)'), '<=', $maxExperience);
                    }
                }

                if ($certification) {
                    $query->whereHas('certifications', function ($q) use ($certification) {
                        $q->where('name', 'LIKE', '%' . $certification . '%')
                            ->orWhere('issue_organization', 'LIKE', '%' . $certification . '%')
                            ->orWhere('creadential_id', 'LIKE', '%' . $certification . '%');
                    });
                }
            });
        }

        // Search
        $applicantsQuery->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%')
                    ->orWhere('address', 'LIKE', '%' . $search . '%')
                    ->orWhere('cell_number', 'LIKE', '%' . $search . '%');
            });
        });

        // Pagination
        $paginatedApplicants = $applicantsQuery->latest()->paginate($perPage);

        if ($paginatedApplicants->isEmpty()) {
            return $this->success(null, 'No applicants found for the given criteria.');
        }

        $formattedApplicants = $paginatedApplicants->map(function ($applicant) {
            $job             = $applicant->job;
            $firstExperience = $applicant->employee->experiences->first();

            return [
                'id'          => $applicant->id,
                'job_title'   => $job->title,
                'full_name'   => $applicant->full_name,
                'email'       => $applicant->email,
                'cell_number' => $applicant->cell_number,
                'address'     => $applicant->address,
                'resume'      => $applicant->resume ? url($applicant->resume) : null,
                'employee'    => [
                    'id'                  => $applicant->employee->id,
                    'avatar'              => $applicant->employee->image_url ? url($applicant->employee->image_url) : null,
                    'age'                 => $applicant->employee->user->age ? (int) $applicant->employee->user->age : null,
                    'years_of_experience' => $applicant->employee->year_of_experice ? (int) $applicant->employee->year_of_experice : null,
                ],
            ];
        });

        $download = [
            'csv' => url("company/jobs/{$job->id}/applicants/export/csv"),
            'pdf' => url("company/jobs/{$job->id}/applicants/export/pdf"),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Job applicants retrieved successfully.',
            'data'    => [
                'download_link' => $download, // ✅ moved outside 'data'
                'applicants'    => $formattedApplicants,
                'pagination'    => [
                    'current_page' => $paginatedApplicants->currentPage(),
                    'last_page'    => $paginatedApplicants->lastPage(),
                    'per_page'     => $paginatedApplicants->perPage(),
                    'total'        => $paginatedApplicants->total(),
                ],
            ],
            'code'    => 200,
        ]);

    }

}
