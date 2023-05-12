<?php

use App\Http\Controllers\ProfileController;
use App\Http\Livewire\IssueManagement;
use App\Http\Livewire\SessionManagement;
use App\Http\Livewire\Voting;
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

Route::middleware('auth')->group(function () {
    // Route for the Session Management component
    Route::get('/dashboard', SessionManagement::class)->name('dashboard');
    // Route for the Issue Management component
    Route::get('/sessions/{inviteCode}/issues', IssueManagement::class)->name('session.issues');
    // Route for the Voting component
    Route::get('/sessions/{inviteCode}/voting', Voting::class)->name('session.voting');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
