<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileResetStatsRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\FlashcardEvent;
use App\Models\FlashcardUserProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(): Response
    {
        return Inertia::render('settings/profile');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());
        $request->user()->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('profile.edit');
    }

    /**
     * Reset the user's learning statistics.
     */
    public function resetStats(ProfileResetStatsRequest $request): RedirectResponse
    {
        $userId = $request->user()->id;

        DB::transaction(function () use ($userId): void {
            FlashcardEvent::query()->where('user_id', $userId)->delete();
            FlashcardUserProgress::query()->where('user_id', $userId)->delete();
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Статистика сброшена.')]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
