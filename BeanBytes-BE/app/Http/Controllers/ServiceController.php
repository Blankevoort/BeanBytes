<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\JobRequest;
use App\Models\CodeSnippet;
use App\Models\Interaction;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = JobRequest::with('user', 'skills')
            ->where('status', '!=', 'closed');

        $user = Auth::guard('sanctum')->user();
        if ($user) {
            $query->where('user_id', '=', $user->id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('min_budget')) {
            $query->where('budget', '>=', $request->min_budget);
        }
        if ($request->filled('max_budget')) {
            $query->where('budget', '<=', $request->max_budget);
        }

        return response()->json($query->paginate(10));
    }

    public function getServices(Request $request)
    {
        $authUser = Auth::guard('sanctum')->user();

        $query = Service::with([
            'user',
            'jobRequest.skills'
        ]);

        if ($authUser) {
            $query->where('user_id', '!=', $authUser->id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return response()->json($query->paginate(10));
    }

    public function getUserServices(Request $request)
    {
        $authUser = Auth::guard('sanctum')->user();

        if (!$authUser) {
            return response()->json([], 200);
        }

        $query = Service::with([
            'user',
            'jobRequest.applications.user.profile.profileImage',
        ])->where('user_id', $authUser->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return response()->json($query->paginate(10));
    }

    public function storeService(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'type'        => 'required|in:hiring,looking_for_job,code_snippet',
        ]);

        $serviceData = $request->only(['title', 'description', 'type']);
        $serviceData['user_id'] = Auth::guard('sanctum')->id();
        $serviceData['status'] = 'open';

        if (in_array($serviceData['type'], ['hiring', 'looking_for_job'])) {
            $request->validate([
                'budget'      => 'required|numeric|min:0',
                'hourly_rate' => 'required|numeric|min:0',
            ]);

            $jobRequest = JobRequest::create([
                'budget'      => $request->budget,
                'hourly_rate' => $request->hourly_rate,
                'type'        => $serviceData['type'],
                'status'      => 'open',
            ]);

            $service = Service::create([
                ...$serviceData,
            ]);

            $jobRequest->service()->associate($service);
            $jobRequest->save();

            return response()->json($service->load('details'), 201);
        }

        if ($serviceData['type'] === 'code_snippet') {
            $request->validate([
                'language'  => 'required|string',
                'license'   => 'nullable|string',
                'file_path' => 'required|string',
                'is_free'   => 'required|boolean',
            ]);

            $codeSnippet = CodeSnippet::create([
                'language'  => $request->language,
                'license'   => $request->license,
                'file_path' => $request->file_path,
                'is_free'   => $request->is_free,
            ]);

            $service = Service::create([
                ...$serviceData,
            ]);

            $codeSnippet->service()->associate($service);
            $codeSnippet->save();

            return response()->json($service->load('details'), 201);
        }

        return response()->json(['message' => 'Invalid type'], 400);
    }

    public function updateService(Request $request, Service $service)
    {
        if (Auth::guard('sanctum')->id() !== $service->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status'      => 'sometimes|in:open,in_progress,closed',
        ]);

        $service->update($request->only(['title', 'description', 'status']));

        if ($service->type !== 'code_snippet') {
            $service->details->update($request->only(['budget', 'hourly_rate', 'status']));
        } else {
            $service->details->update($request->only(['language', 'license', 'file_path', 'is_free']));
        }

        return response()->json($service->load('details'));
    }

    public function destroyService(Service $service)
    {
        if (Auth::guard('sanctum')->id() !== $service->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($service->details) {
            $service->details->delete();
        }

        $service->delete();

        return response()->json(['message' => 'Service deleted.']);
    }

    public function apply($serviceId)
    {
        $service = Service::findOrFail($serviceId);

        if ($service->type !== 'hiring') {
            return response()->json(['message' => 'Not a job service'], 400);
        }

        $job = JobRequest::where('service_id', $service->id)->first();

        if (!$job) {
            return response()->json(['message' => 'Job not found for this service'], 404);
        }

        if ($job->applications()->where('user_id', Auth::id())->exists()) {
            return response()->json(['message' => 'Already applied'], 400);
        }

        $job->interactions()->create([
            'user_id' => Auth::id(),
            'type' => 'job_application',
        ]);

        Notification::create([
            'user_id' => $service->user_id,
            'from_user_id' => Auth::id(),
            'notifiable_type' => JobRequest::class,
            'notifiable_id' => $job->id,
            'type' => 'job_application',
            'read' => false,
        ]);

        return response()->json(['message' => 'Application submitted'], 201);
    }

    public function acceptApplicant($serviceId, $applicantId)
    {
        $service = Service::findOrFail($serviceId);

        if ($service->type !== 'hiring') {
            return response()->json(['message' => 'Not a job service'], 400);
        }

        $job = JobRequest::where('service_id', $service->id)->first();

        if (!$job || Auth::id() !== $service->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($service->status !== 'open') {
            return response()->json(['message' => "Job already {$service->status}"], 400);
        }

        if (!$job->applications()->where('user_id', $applicantId)->exists()) {
            return response()->json(['message' => 'Did not apply'], 400);
        }

        $service->update(['status' => 'in_progress']);

        Notification::create([
            'user_id' => $applicantId,
            'from_user_id' => Auth::id(),
            'notifiable_type' => JobRequest::class,
            'notifiable_id' => $job->id,
            'type' => 'job_application_accepted',
            'read' => false,
        ]);

        return response()->json(['message' => 'Applicant accepted'], 200);
    }

    public function rejectApplicant($serviceId, $applicantId)
    {
        $service = Service::findOrFail($serviceId);

        if ($service->type !== 'hiring') {
            return response()->json(['message' => 'Not a job service'], 400);
        }

        $job = JobRequest::where('service_id', $service->id)->first();

        if (!$job || Auth::id() !== $service->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($service->status !== 'open') {
            return response()->json(['message' => "Job already {$service->status}"], 400);
        }

        if (!$job->applications()->where('user_id', $applicantId)->exists()) {
            return response()->json(['message' => 'Did not apply'], 400);
        }

        Notification::create([
            'user_id' => $applicantId,
            'from_user_id' => Auth::id(),
            'notifiable_type' => JobRequest::class,
            'notifiable_id' => $job->id,
            'type' => 'job_application_rejected',
            'read' => false,
        ]);

        return response()->json(['message' => 'Applicant rejected'], 200);
    }
}
