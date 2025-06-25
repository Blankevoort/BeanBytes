<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\JobRequest;
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
        $query = Service::with(['details', 'user.profile.profileImage']);

        if ($request->filled('my_services') && $request->my_services == 1 && $authUser) {
            $query->where('user_id', $authUser->id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $services = $query->paginate(10);
        return response()->json($services);
    }

    public function storeService(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'type'        => 'required|in:job_request,code_snippet',
        ]);

        $serviceData = $request->only(['title', 'description', 'type']);
        $serviceData['user_id'] = Auth::guard('sanctum')->id();
        $serviceData['status'] = 'open';

        if ($serviceData['type'] === 'job_request') {
            $request->validate([
                'budget'      => 'required|numeric|min:0',
                'hourly_rate' => 'required|numeric|min:0',
            ]);

            $jobRequest = JobRequest::create([
                'title'             => $serviceData['title'],
                'description'       => $serviceData['description'],
                'type'              => 'hiring',
                'budget'            => $request->budget,
                'hourly_rate'       => $request->hourly_rate,
                'user_id'           => $serviceData['user_id'],
                'applicants_count'  => 0,
                'status'            => 'open',
            ]);

            $serviceData['details_id'] = $jobRequest->id;
            $serviceData['details_type'] = JobRequest::class;
        } elseif ($serviceData['type'] === 'code_snippet') {
            $request->validate([
                'language'  => 'required|string',
                'license'   => 'nullable|string',
                'file_path' => 'required|string',
                'is_free'   => 'required|boolean',
            ]);

            $codeSnippet = \App\Models\CodeSnippet::create([
                'language'  => $request->language,
                'license'   => $request->license,
                'file_path' => $request->file_path,
                'is_free'   => $request->is_free,
            ]);

            $serviceData['details_id'] = $codeSnippet->id;
            $serviceData['details_type'] = \App\Models\CodeSnippet::class;
        }

        $service = Service::create($serviceData);

        return response()->json($service->load('details'), 201);
    }

    public function updateService(Request $request, Service $service)
    {
        if (Auth::guard('sanctum')->id() !== $service->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'type'        => 'sometimes|in:job_request,code_snippet',
        ]);

        $service->update($request->only(['title', 'description', 'type', 'status']));

        if ($service->type === 'job_request' && $service->details_type === JobRequest::class) {
            $jobRequest = $service->details;
            $jobRequest->update($request->only(['budget', 'hourly_rate', 'status']));
        } elseif ($service->type === 'code_snippet' && $service->details_type === \App\Models\CodeSnippet::class) {
            $codeSnippet = $service->details;
            $codeSnippet->update($request->only(['language', 'license', 'file_path', 'is_free']));
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

    public function apply($id)
    {
        $jobRequest = JobRequest::findOrFail($id);
        $userId = Auth::id();

        if ($jobRequest->status !== 'open') {
            return response()->json(['message' => 'Job is no longer open for applications.'], 400);
        }

        $jobRequest->update(['status' => 'in_progress']);

        Notification::create([
            'user_id' => $jobRequest->user_id,
            'from_user_id' => $userId,
            'notifiable_type' => JobRequest::class,
            'notifiable_id' => $jobRequest->id,
            'type' => 'job_application',
            'read' => false,
        ]);

        return response()->json(['message' => 'Application submitted.']);
    }

    public function acceptApplicant($serviceId)
    {
        $jobRequest = JobRequest::findOrFail($serviceId);

        if (auth()->id() !== $jobRequest->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($jobRequest->status === 'in_progress') {
            return response()->json(['message' => 'Job is already in progress.'], 400);
        }

        $jobRequest->update(['status' => 'in_progress']);

        Notification::create([
            'user_id' => $jobRequest->user_id,
            'from_user_id' => auth()->id(),
            'notifiable_type' => JobRequest::class,
            'notifiable_id' => $jobRequest->id,
            'type' => 'job_application_accepted',
            'read' => false,
        ]);

        return response()->json(['message' => 'Applicant accepted. Job is now in progress.']);
    }

    public function rejectApplicant($serviceId)
    {
        $jobRequest = JobRequest::findOrFail($serviceId);

        if (auth()->id() !== $jobRequest->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($jobRequest->status === 'closed') {
            return response()->json(['message' => 'Job is already closed.'], 400);
        }

        $jobRequest->update(['status' => 'closed']);

        Notification::create([
            'user_id' => $jobRequest->user_id,
            'from_user_id' => auth()->id(),
            'notifiable_type' => JobRequest::class,
            'notifiable_id' => $jobRequest->id,
            'type' => 'job_application_rejected',
            'read' => false,
        ]);

        return response()->json(['message' => 'Applicant rejected. Job request is now closed.']);
    }
}
