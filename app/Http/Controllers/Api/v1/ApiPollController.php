<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiPollController extends Controller
{
    public function index(Request $request)
    {
        $polls = $request->user()->polls()->with('options')->orderBy('created_at', 'desc')->get();
        return response()->json($polls);
    }

    public function show(string $token)
    {
        $poll = Poll::with(['options' => function ($query) {
            $query->withCount('votes');
        }])->where('secret_token', $token)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        return response()->json($poll);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'is_draft' => 'boolean',
            'allow_multiple_choices' => 'boolean',
            'results_public' => 'boolean',
            'duration' => 'nullable|integer|min:1',
            'options' => 'required|array|min:2',
            'options.*.label' => 'required|string|max:255',
        ]);

        $poll = new Poll();
        $poll->user_id = $request->user()->id;
        $poll->question = $validated['question'];
        $poll->title = $validated['title'] ?? null;
        $poll->secret_token = Str::random(32);
        $poll->is_draft = $validated['is_draft'] ?? true;
        $poll->allow_multiple_choices = $validated['allow_multiple_choices'] ?? false;
        $poll->results_public = $validated['results_public'] ?? false;
        $poll->duration = $validated['duration'] ?? null;

        if (!$poll->is_draft) {
            $poll->started_at = now();
            if ($poll->duration) {
                $poll->ends_at = now()->addSeconds($poll->duration);
            }
        }

        $poll->save();

        foreach ($validated['options'] as $option) {
            $poll->options()->create(['label' => $option['label']]);
        }

        return response()->json($poll->load('options'), 201);
    }

    public function update(Request $request, int $id)
    {
        $poll = Poll::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'is_draft' => 'boolean',
            'allow_multiple_choices' => 'boolean',
            'results_public' => 'boolean',
            'duration' => 'nullable|integer|min:1',
            'options' => 'required|array|min:2',
            'options.*.label' => 'required|string|max:255',
        ]);

        $poll->question = $validated['question'];
        $poll->title = $validated['title'] ?? null;
        $poll->allow_multiple_choices = $validated['allow_multiple_choices'] ?? false;
        $poll->results_public = $validated['results_public'] ?? false;
        $poll->duration = $validated['duration'] ?? null;

        if ($poll->is_draft && isset($validated['is_draft']) && !$validated['is_draft'] && !$poll->started_at) {
            $poll->started_at = now();
            if ($poll->duration) {
                $poll->ends_at = now()->addSeconds($poll->duration);
            }
        }

        $poll->is_draft = $validated['is_draft'] ?? $poll->is_draft;
        $poll->save();

        $poll->options()->delete();
        foreach ($validated['options'] as $option) {
            $poll->options()->create(['label' => $option['label']]);
        }

        return response()->json($poll->load('options'));
    }

    public function remove(Request $request, int $id)
    {
        $poll = Poll::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $poll->delete();
        return response()->json(['message' => 'success'], 200);
    }

    public function vote(Request $request, string $token)
    {
        $poll = Poll::with('options')->where('secret_token', $token)->first();

        if (!$poll || $poll->is_draft) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        if ($poll->ends_at && now()->isAfter($poll->ends_at)) {
            return response()->json(['message' => 'Poll is closed.'], 403);
        }

        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'option_ids' => 'required|array|min:1',
            'option_ids.*' => 'integer|exists:poll_options,id',
        ]);

        $optionIds = $validated['option_ids'];

        $validIds = $poll->options->pluck('id')->toArray();
        foreach ($optionIds as $oid) {
            if (!in_array($oid, $validIds)) {
                return response()->json(['message' => 'Invalid option.'], 422);
            }
        }

        if (!$poll->allow_multiple_choices) {
            $optionIds = [$optionIds[0]];
        }

        PollVote::where('poll_id', $poll->id)
            ->where('user_id', $request->user()->id)
            ->delete();

        foreach ($optionIds as $oid) {
            PollVote::create([
                'poll_id' => $poll->id,
                'user_id' => $request->user()->id,
                'poll_option_id' => $oid,
            ]);
        }

        return response()->json(['message' => 'Vote enregistré.'], 201);
    }
}