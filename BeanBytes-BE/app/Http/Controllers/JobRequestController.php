<?php

namespace App\Http\Controllers;

use App\Models\JobRequest;
use App\Models\Interaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = JobRequest::with('user', 'skills')
            ->where('status', '!=', 'closed');

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

        return response()->json(['message' => 'Application submitted.']);
    }

    public function acceptApplicant($jobRequestId, $interactionId)
    {
        $jobRequest = JobRequest::findOrFail($jobRequestId);
        if (auth()->id() !== $jobRequest->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $interaction = Interaction::where('interactable_id', $jobRequestId)
            ->where('interactable_type', JobRequest::class)
            ->where('id', $interactionId)
            ->firstOrFail();

        $interaction->update(['status' => 'accepted']);

        return response()->json(['message' => 'Applicant accepted.']);
    }

    public function rejectApplicant($jobRequestId, $interactionId)
    {
        $jobRequest = JobRequest::findOrFail($jobRequestId);
        if (auth()->id() !== $jobRequest->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $interaction = Interaction::where('interactable_id', $jobRequestId)
            ->where('interactable_type', JobRequest::class)
            ->where('id', $interactionId)
            ->firstOrFail();

        $interaction->update(['status' => 'rejected']);

        return response()->json(['message' => 'Applicant rejected.']);
    }
}
