<?php

use App\Livewire\ArchivedSessionView;
use App\Livewire\ArchivedSessions;
use App\Livewire\OwnerSessions;
use App\Livewire\Voting;
use App\Models\Issue;
use App\Models\Session;
use App\Models\User;
use Livewire\Livewire;

test('owner can archive a session and see it on the archived list', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create([
        'owner_id' => $user->id,
    ]);

    Livewire::actingAs($user)
        ->test(OwnerSessions::class)
        ->call('archiveSession', (string) $session->id);

    $session->refresh();

    expect($session->archived_at)->not->toBeNull();

    Livewire::actingAs($user)
        ->test(OwnerSessions::class)
        ->assertSee('No upcoming sessions available.');

    Livewire::actingAs($user)
        ->test(ArchivedSessions::class)
        ->assertSee($session->name)
        ->assertSee($session->archived_at->toFormattedDateString());
});

test('archived session voting route redirects to readonly view', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create([
        'owner_id' => $user->id,
        'archived_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(Voting::class, ['inviteCode' => $session->invite_code])
        ->assertRedirect(route('session.archived', $session->invite_code));
});

test('archived session view shows issue overview', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create([
        'owner_id' => $user->id,
        'archived_at' => now(),
    ]);

    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'storypoints' => 8,
        'description' => '<p>Beschreibung</p>',
        'jira_key' => 'SAN-123',
        'jira_url' => 'https://example.com/browse/SAN-123',
        'status' => Issue::STATUS_FINISHED,
    ]);

    Livewire::actingAs($user)
        ->test(ArchivedSessionView::class, ['inviteCode' => $session->invite_code])
        ->assertSee('Archivierte Session', false)
        ->assertSee($issue->jira_key, false)
        ->assertSee('8 SP', false)
        ->assertSee('Beschreibung', false);
});

test('owner can unarchive session from archived view', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create([
        'owner_id' => $user->id,
        'archived_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(ArchivedSessionView::class, ['inviteCode' => $session->invite_code])
        ->call('unarchiveSession')
        ->assertRedirect(route('session.voting', $session->invite_code));

    expect($session->fresh()->archived_at)->toBeNull();
});

test('owner can unarchive session from dashboard list', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create([
        'owner_id' => $user->id,
        'archived_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(ArchivedSessions::class)
        ->call('unarchiveSession', (string) $session->id)
        ->assertDontSee($session->name);

    expect($session->fresh()->archived_at)->toBeNull();
});
