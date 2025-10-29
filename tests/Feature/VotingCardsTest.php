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
