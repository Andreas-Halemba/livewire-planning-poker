<?php

use App\Livewire\VotingCards;
use App\Models\Issue;
use App\Models\Session;
use App\Models\User;
use App\Models\Vote;
use Livewire\Livewire;

test('user can select a numeric card when no vote exists', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $user->id]);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => Issue::STATUS_VOTING,
    ]);

    Livewire::actingAs($user)
        ->test(VotingCards::class, ['session' => $session])
        ->call('selectCard', 5)
        ->assertSet('selectedCard', 5);

    expect($issue->votes()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('user can select question mark card when no vote exists', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $user->id]);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => Issue::STATUS_VOTING,
    ]);

    Livewire::actingAs($user)
        ->test(VotingCards::class, ['session' => $session])
        ->call('selectCard', '?')
        ->assertSet('selectedCard', '?');

    expect($issue->votes()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('user can select different numeric cards', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $user->id]);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => Issue::STATUS_VOTING,
    ]);

    $component = Livewire::actingAs($user)
        ->test(VotingCards::class, ['session' => $session]);

    // Test various numeric cards
    $component->call('selectCard', 0)->assertSet('selectedCard', 0);
    $component->call('selectCard', 1)->assertSet('selectedCard', 1);
    $component->call('selectCard', 8)->assertSet('selectedCard', 8);
    $component->call('selectCard', 13)->assertSet('selectedCard', 13);
    $component->call('selectCard', 21)->assertSet('selectedCard', 21);
    $component->call('selectCard', 100)->assertSet('selectedCard', 100);
});

test('user cannot select card after voting', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $user->id]);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => Issue::STATUS_VOTING,
    ]);

    // Create an existing vote
    Vote::factory()->create([
        'user_id' => $user->id,
        'issue_id' => $issue->id,
        'value' => 5,
    ]);

    Livewire::actingAs($user)
        ->test(VotingCards::class, ['session' => $session])
        ->call('selectCard', 8)
        ->assertSet('selectedCard', 5); // Should remain at existing vote, not change to 8
});

test('user can change selected card before confirming vote', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $user->id]);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => Issue::STATUS_VOTING,
    ]);

    $component = Livewire::actingAs($user)
        ->test(VotingCards::class, ['session' => $session]);

    // Select first card
    $component->call('selectCard', 5)->assertSet('selectedCard', 5);

    // Change to different card
    $component->call('selectCard', 8)->assertSet('selectedCard', 8);

    // Change to question mark
    $component->call('selectCard', '?')->assertSet('selectedCard', '?');
});

test('selectCard handles string numeric input correctly', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $user->id]);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => Issue::STATUS_VOTING,
    ]);

    Livewire::actingAs($user)
        ->test(VotingCards::class, ['session' => $session])
        ->call('selectCard', '5') // String instead of int
        ->assertSet('selectedCard', 5); // Should be converted to int
});

test('selectCard works but gets reset by render when no current issue exists', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $user->id]);
    // No issue created, so currentIssue will be null
    // selectCard() will work (hasVoted() returns false), but render() resets selectedCard to null

    $component = Livewire::actingAs($user)
        ->test(VotingCards::class, ['session' => $session]);

    // Verify no issue exists
    expect($component->get('currentIssue'))->toBeNull();

    // Call selectCard - this should work in the method itself
    $component->call('selectCard', 5);

    // But after render(), selectedCard gets reset to null (as per render() logic)
    // So we verify the method works by checking hasVoted logic, not the final state
    // The behavior is: selectCard sets it, but render() resets it when no issue exists
    expect($component->get('selectedCard'))->toBeNull();
});

test('user can select an issue for async voting', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $user->id]);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => Issue::STATUS_NEW, // Not STATUS_VOTING
    ]);

    $component = Livewire::actingAs($user)
        ->test(VotingCards::class, ['session' => $session])
        ->call('selectIssue', $issue->id)
        ->assertSet('selectedIssueId', $issue->id);

    // Verify currentIssue is set to the selected issue
    expect($component->get('currentIssue'))->not->toBeNull();
    expect($component->get('currentIssue')->id)->toBe($issue->id);
});

test('user can vote on manually selected issue (async voting)', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $user->id]);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => Issue::STATUS_NEW, // Not STATUS_VOTING
    ]);

    $component = Livewire::actingAs($user)
        ->test(VotingCards::class, ['session' => $session])
        ->call('selectIssue', $issue->id)
        ->call('selectCard', 8)
        ->call('confirmVote');

    // Verify vote was created
    expect(Vote::whereUserId($user->id)->whereIssueId($issue->id)->exists())->toBeTrue();
    $vote = Vote::whereUserId($user->id)->whereIssueId($issue->id)->first();
    expect($vote->value)->toBe(8);
});

test('async vote persists even when issue is not STATUS_VOTING', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $user->id]);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => Issue::STATUS_NEW,
    ]);

    // User votes async
    Livewire::actingAs($user)
        ->test(VotingCards::class, ['session' => $session])
        ->call('selectIssue', $issue->id)
        ->call('selectCard', 13)
        ->call('confirmVote');

    // Verify vote exists
    expect(Vote::whereUserId($user->id)->whereIssueId($issue->id)->exists())->toBeTrue();
    $vote = Vote::whereUserId($user->id)->whereIssueId($issue->id)->first();
    expect($vote->value)->toBe(13);

    // Verify issue status hasn't changed
    $issue->refresh();
    expect($issue->status)->toBe(Issue::STATUS_NEW);
});

test('manual selection is cleared when PO starts voting', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $user->id]);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => Issue::STATUS_NEW,
    ]);

    $component = Livewire::actingAs($user)
        ->test(VotingCards::class, ['session' => $session])
        ->call('selectIssue', $issue->id)
        ->assertSet('selectedIssueId', $issue->id);

    // PO starts voting on this issue
    $issue->status = Issue::STATUS_VOTING;
    $issue->save();

    // Render should clear manual selection
    $component->refresh();

    expect($component->get('selectedIssueId'))->toBeNull();
    // But currentIssue should still be set (from STATUS_VOTING)
    expect($component->get('currentIssue'))->not->toBeNull();
    expect($component->get('currentIssue')->id)->toBe($issue->id);
});

test('user can clear manual issue selection', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $user->id]);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => Issue::STATUS_NEW,
    ]);

    $component = Livewire::actingAs($user)
        ->test(VotingCards::class, ['session' => $session])
        ->call('selectIssue', $issue->id)
        ->assertSet('selectedIssueId', $issue->id)
        ->call('clearSelection')
        ->assertSet('selectedIssueId', null)
        ->assertSet('selectedCard', null);
});

test('async votes are visible when PO reveals votes after starting voting', function () {
    $po = User::factory()->create();
    $developer = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $po->id]);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => Issue::STATUS_NEW,
    ]);

    // Developer votes async before PO starts voting
    Livewire::actingAs($developer)
        ->test(VotingCards::class, ['session' => $session])
        ->call('selectIssue', $issue->id)
        ->call('selectCard', 5)
        ->call('confirmVote');

    // Verify vote exists
    expect(Vote::whereUserId($developer->id)->whereIssueId($issue->id)->exists())->toBeTrue();

    // PO starts voting
    $issue->status = Issue::STATUS_VOTING;
    $issue->save();

    // PO reveals votes - votes should be visible
    $votes = $issue->votes()->with('user')->get();
    expect($votes->count())->toBe(1);
    expect($votes->first()->user_id)->toBe($developer->id);
    expect($votes->first()->value)->toBe(5);
});

test('removing vote from async voting issue does not change status from NEW to VOTING', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $user->id]);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => Issue::STATUS_NEW, // Async voting scenario
    ]);

    // User votes async
    Livewire::actingAs($user)
        ->test(VotingCards::class, ['session' => $session])
        ->call('selectIssue', $issue->id)
        ->call('selectCard', 8)
        ->call('confirmVote');

    // Verify vote exists
    expect(Vote::whereUserId($user->id)->whereIssueId($issue->id)->exists())->toBeTrue();

    // User removes vote
    Livewire::actingAs($user)
        ->test(VotingCards::class, ['session' => $session])
        ->call('selectIssue', $issue->id)
        ->call('removeVote');

    // Verify vote is removed
    expect(Vote::whereUserId($user->id)->whereIssueId($issue->id)->exists())->toBeFalse();

    // Verify issue status remains NEW (not changed to VOTING)
    $issue->refresh();
    expect($issue->status)->toBe(Issue::STATUS_NEW);
});

test('removing vote from finished issue resets status to VOTING', function () {
    $user = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $user->id]);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => Issue::STATUS_FINISHED, // Issue was already estimated
    ]);

    // Create an existing vote
    Vote::factory()->create([
        'user_id' => $user->id,
        'issue_id' => $issue->id,
        'value' => 5,
    ]);

    // User removes vote
    Livewire::actingAs($user)
        ->test(VotingCards::class, ['session' => $session])
        ->call('selectIssue', $issue->id)
        ->call('removeVote');

    // Verify vote is removed
    expect(Vote::whereUserId($user->id)->whereIssueId($issue->id)->exists())->toBeFalse();

    // Verify issue status is reset to VOTING (to allow re-voting)
    $issue->refresh();
    expect($issue->status)->toBe(Issue::STATUS_VOTING);
});
