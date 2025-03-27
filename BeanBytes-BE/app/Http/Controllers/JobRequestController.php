<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JobRequest;
use App\Models\Interaction;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobRequestController extends Controller
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

    public function getJobRequests(Request $request)
    {
        $authUser = auth()->user();

        $query = JobRequest::with([
            'user.profile.profileImage',
            'skills',
        ]);

        if ($request->filled('my_jobs') && $request->my_jobs == 1 && $authUser) {
            $query->where('user_id', $authUser->id);
        } elseif ($authUser) {
            $query->where('user_id', '!=', $authUser->id);
        }

        $jobRequests = $query->get();

        $jobIds = $jobRequests->pluck('id');

        $applications = Interaction::where('interactionable_type', JobRequest::class)
            ->whereIn('interactionable_id', $jobIds)
            ->where('type', 'job_application')
            ->with('user.profile.profileImage')
            ->get();

        $groupedApplications = $applications->groupBy('interactionable_id');

        foreach ($jobRequests as $job) {
            $job->applications = $groupedApplications->get($job->id, collect());
        }

        return response()->json($jobRequests);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:looking_for_job,hiring',
            'budget' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'skills' => 'array',
            'skills.*' => 'exists:skills,id',
        ]);

        $jobRequest = JobRequest::create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'budget' => $request->budget,
            'hourly_rate' => $request->hourly_rate,
            'user_id' => auth()->id(),
        ]);

        if ($request->filled('skills')) {
            $jobRequest->skills()->attach($request->skills);
        }

        return response()->json($jobRequest->load('skills'), 201);
    }

    public function show($id)
    {
        $jobRequest = JobRequest::with('user', 'skills', 'interactions')->findOrFail($id);
        return response()->json($jobRequest);
    }

    public function update(Request $request, JobRequest $jobRequest)
    {
        if (auth()->id() !== $jobRequest->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'title' => 'string|max:255',
            'description' => 'string',
            'type' => 'in:looking_for_job,hiring',
            'budget' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'status' => 'in:open,in_progress,closed',
            'skills' => 'array',
            'skills.*' => 'exists:skills,id',
        ]);

        $jobRequest->update($request->only(['title', 'description', 'type', 'budget', 'hourly_rate', 'status']));

        if ($request->filled('skills')) {
            $jobRequest->skills()->sync($request->skills);
        }

        return response()->json($jobRequest->load('skills'));
    }

    public function destroy(JobRequest $jobRequest)
    {
        if (auth()->id() !== $jobRequest->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $jobRequest->delete();
        return response()->json(['message' => 'Job request deleted.']);
    }

    public function apply($id)
    {
        $jobRequest = JobRequest::findOrFail($id);
        $userId = Auth::id();

        $alreadyApplied = Interaction::where([
            'user_id' => $userId,
            'interactionable_id' => $jobRequest->id,
            'interactionable_type' => JobRequest::class,
            'type' => 'job_application',
        ])->exists();

        if ($alreadyApplied) {
            return response()->json(['message' => 'You have already applied.'], 400);
        }

        Interaction::create([
            'user_id' => $userId,
            'interactionable_id' => $jobRequest->id,
            'interactionable_type' => JobRequest::class,
            'type' => 'job_application',
        ]);

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

    public function acceptApplicant($jobRequestId, $interactionId)
    {
        $jobRequest = JobRequest::findOrFail($jobRequestId);
        if (auth()->id() == $jobRequest->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $interaction = Interaction::where('interactionable_id', $jobRequestId)
            ->where('interactionable_type', JobRequest::class)
            ->where('id', $interactionId)
            ->firstOrFail();

        if ($interaction->status == 'accepted') {
            return response()->json(['message' => 'You have already accepted.'], 400);
        }

        $interaction->update(['status' => 'accepted']);

        $existingNotification = Notification::where([
            'user_id' => $interaction->user_id,
            'from_user_id' => auth()->id(),
            'notifiable_type' => JobRequest::class,
            'notifiable_id' => $jobRequest->id,
        ])->first();

        if ($existingNotification) {
            $existingNotification->update(['type' => 'job_application_accepted']);
        } else {
            Notification::create([
                'user_id' => $interaction->user_id,
                'from_user_id' => auth()->id(),
                'notifiable_type' => JobRequest::class,
                'notifiable_id' => $jobRequest->id,
                'type' => 'job_application_accepted',
                'read' => false,
            ]);
        }

        return response()->json(['message' => 'Applicant accepted.']);
    }

    public function rejectApplicant($jobRequestId, $interactionId)
    {
        $jobRequest = JobRequest::findOrFail($jobRequestId);
        if (auth()->id() == $jobRequest->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $interaction = Interaction::where('interactionable_id', $jobRequestId)
            ->where('interactionable_type', JobRequest::class)
            ->where('id', $interactionId)
            ->firstOrFail();

        if ($interaction->status == 'rejected') {
            return response()->json(['message' => 'You have already rejected.'], 400);
        }

        $interaction->update(['status' => 'rejected']);

        $existingNotification = Notification::where([
            'user_id' => $interaction->user_id,
            'from_user_id' => auth()->id(),
            'notifiable_type' => JobRequest::class,
            'notifiable_id' => $jobRequest->id,
        ])->first();

        if ($existingNotification) {
            $existingNotification->update(['type' => 'job_application_rejected']);
        } else {
            Notification::create([
                'user_id' => $interaction->user_id,
                'from_user_id' => auth()->id(),
                'notifiable_type' => JobRequest::class,
                'notifiable_id' => $jobRequest->id,
                'type' => 'job_application_rejected',
                'read' => false,
            ]);
        }

        return response()->json(['message' => 'Applicant rejected.']);
    }
}
