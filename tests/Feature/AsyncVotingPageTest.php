<?php

use App\Enums\IssueStatus;
use App\Livewire\AsyncVotingPage;
use App\Models\Issue;
use App\Models\Session;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('owner sees async voting progress without vote values', function () {
    $owner = User::factory()->create();
    $dev1 = User::factory()->create();
    $dev2 = User::factory()->create();

    $session = Session::factory()->create(['owner_id' => $owner->id]);
    $session->users()->attach([$owner->id, $dev1->id, $dev2->id]);

    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => IssueStatus::NEW,
        'title' => 'Test Issue',
    ]);

    Vote::factory()->create([
        'user_id' => $dev1->id,
        'issue_id' => $issue->id,
        'value' => 5,
    ]);

    $component = Livewire::actingAs($owner)
        ->test(AsyncVotingPage::class, ['inviteCode' => $session->invite_code])
        ->assertStatus(200)
        ->assertSee($dev1->name)
        ->assertDontSee('SP');

    $progress = $component->get('asyncVotersByIssue');
    expect($progress)->toHaveKey($issue->id);
    expect($progress[$issue->id][0])->toHaveKeys(['id', 'name']);
    expect($progress[$issue->id][0])->not->toHaveKey('value');
});

test('voter can open async voting page', function () {
    $owner = User::factory()->create();
    $voter = User::factory()->create();

    $session = Session::factory()->create(['owner_id' => $owner->id]);
    $session->users()->attach([$owner->id, $voter->id]);

    Livewire::actingAs($voter)
        ->test(AsyncVotingPage::class, ['inviteCode' => $session->invite_code])
        ->assertStatus(200)
        ->assertSee('Async Voting');
});
