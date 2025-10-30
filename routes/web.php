<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\SessionManagement;
use App\Livewire\Voting;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect(route('dashboard'));
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Route for the Session Management component
    Route::get('/dashboard', SessionManagement::class)->name('dashboard');
    // Route for the Voting component
    Route::get('/sessions/{inviteCode}/voting', Voting::class)->name('session.voting');

    // API endpoint to cancel voting when owner leaves (fallback for when PO is alone)
    Route::post('/api/sessions/{inviteCode}/cancel-voting-on-leave', function (string $inviteCode) {
        $session = \App\Models\Session::whereInviteCode($inviteCode)->firstOrFail();

        // Only allow if user is the owner
        if (auth()->id() !== $session->owner_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $currentIssue = $session->currentIssue();
        if ($currentIssue && $currentIssue->status === \App\Models\Issue::STATUS_VOTING) {
            $currentIssue->status = \App\Models\Issue::STATUS_NEW;
            $currentIssue->save();
            broadcast(new \App\Events\IssueCanceled($currentIssue));
        }

        return response()->json(['success' => true]);
    })->middleware('web')->name('api.sessions.cancel-voting-on-leave');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
