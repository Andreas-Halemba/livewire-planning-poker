<?php

use App\Events\IssueCanceled;
use App\Livewire\EstimationSession;
use App\Models\Issue;
use App\Models\Session;
use App\Livewire\Voting;
use App\Livewire\SessionManagement;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

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
    Route::get('/sessions/{session:invite_code}/estimate', EstimationSession::class)->name('session.estimate');


    // API endpoint to cancel voting when owner leaves (fallback for when PO is alone)
    Route::post('/api/sessions/{inviteCode}/cancel-voting-on-leave', function (string $inviteCode) {
        $session = Session::whereInviteCode($inviteCode)->firstOrFail();

        // Only allow if user is the owner
        if (auth()->id() !== $session->owner_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $currentIssue = $session->currentIssue();
        if ($currentIssue && $currentIssue->status === Issue::STATUS_VOTING) {
            $currentIssue->status = Issue::STATUS_NEW;
            $currentIssue->save();
            broadcast(new IssueCanceled($currentIssue))->toOthers();
        }

        return response()->json(['success' => true]);
    })->middleware('web')->name('api.sessions.cancel-voting-on-leave');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
