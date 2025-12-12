<?php

use App\Enums\IssueStatus;
use App\Events\IssueCanceled;
use App\Http\Controllers\JiraAttachmentController;
use App\Http\Controllers\ProfileController;
use App\Livewire\ArchivedSessionView;
use App\Livewire\AsyncVotingPage;
use App\Livewire\SessionManagement;
use App\Livewire\V2\SessionPage;
use App\Livewire\Voting;
use App\Models\Session;
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
    Route::get('/sessions/{inviteCode}/archived', ArchivedSessionView::class)->name('session.archived');

    // V2 Routes - Refactored components with improved architecture
    Route::get('/sessions/{inviteCode}/v2', SessionPage::class)->name('session.v2');

    // Async Voting (Voter View + Owner Progress)
    Route::get('/sessions/{inviteCode}/async', AsyncVotingPage::class)->name('session.async');

    // API endpoint to cancel voting when owner leaves (fallback for when PO is alone)
    Route::post('/api/sessions/{inviteCode}/cancel-voting-on-leave', function (string $inviteCode) {
        $session = Session::whereInviteCode($inviteCode)->firstOrFail();

        // Only allow if user is the owner
        if (auth()->id() !== $session->owner_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $currentIssue = $session->currentIssue();
        if ($currentIssue && $currentIssue->status === IssueStatus::VOTING) {
            $currentIssue->status = IssueStatus::NEW;
            $currentIssue->save();
            broadcast(new IssueCanceled($session->invite_code))->toOthers();
        }

        return response()->json(['success' => true]);
    })->middleware('web')->name('api.sessions.cancel-voting-on-leave');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Jira attachment proxy for images in ticket descriptions
    Route::get('/jira/attachment/{attachmentId}', [JiraAttachmentController::class, 'proxy'])
        ->name('jira.attachment.proxy');
});

require __DIR__ . '/auth.php';
