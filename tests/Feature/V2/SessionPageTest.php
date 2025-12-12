<?php

use App\Actions\Jira\SyncStoryPointsToJira;
use App\Enums\IssueStatus;
use App\Events\AddVote;
use App\Events\HideVotes;
use App\Events\IssueAdded;
use App\Events\IssueCanceled;
use App\Events\IssueDeleted;
use App\Events\IssueOrderChanged;
use App\Events\IssueSelected;
use App\Events\RevealVotes;
use App\Livewire\V2\SessionPage;
use App\Models\Issue;
use App\Models\Session;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * Test-Konzept für V2 SessionPage Component
 *
 * Struktur:
 * 1. Setup & Helpers
 * 2. Presence Tests (Online-Status)
 * 3. Voting Tests (Owner Actions)
 * 4. Voting Tests (Voter Actions)
 * 5. Issue Management Tests (CRUD)
 * 6. Jira Import Tests
 * 7. Integration Tests (User Flows)
 */

// ============================================================================
// 1. SETUP & HELPERS
// ============================================================================

/**
 * Erstellt eine Test-Session mit Owner und optionalen Teilnehmern.
 */
function createTestSession(array $participants = []): Session
{
    $owner = User::factory()->create();
    $session = Session::factory()->create(['owner_id' => $owner->id]);

    foreach ($participants as $participant) {
        $session->users()->attach($participant->id);
    }

    return $session->load('users', 'owner');
}

/**
 * Erstellt eine SessionPage Component-Instanz für Tests.
 */
function createSessionPageComponent(Session $session, User $user)
{
    return Livewire::actingAs($user)
        ->test(SessionPage::class, ['inviteCode' => $session->invite_code]);
}

// ============================================================================
// 2. PRESENCE TESTS (HandlesPresence Trait)
// ============================================================================

test('component initializes with current user as online', function () {
    $user = User::factory()->create();
    $session = createTestSession();

    $component = createSessionPageComponent($session, $user);

    expect($component->get('onlineUserIds'))->toContain($user->id);
});

test('handleUsersHere updates online user list', function () {
    $user = User::factory()->create();
    $session = createTestSession();

    $component = createSessionPageComponent($session, $user);

    $component->call('handleUsersHere', [
        ['id' => $user->id],
        ['id' => 999], // Another user
    ]);

    expect($component->get('onlineUserIds'))->toHaveCount(2)
        ->toContain($user->id)
        ->toContain(999);
});

test('handleUserJoining adds user to online list', function () {
    $user = User::factory()->create();
    $session = createTestSession();

    $component = createSessionPageComponent($session, $user);

    $component->call('handleUserJoining', ['id' => 999]);

    expect($component->get('onlineUserIds'))->toContain(999);
});

test('handleUserLeaving removes user from online list', function () {
    $user = User::factory()->create();
    $session = createTestSession();

    $component = createSessionPageComponent($session, $user);
    $component->set('onlineUserIds', [$user->id, 999]);

    $component->call('handleUserLeaving', ['id' => 999]);

    expect($component->get('onlineUserIds'))->not->toContain(999)
        ->toContain($user->id);
});

// ============================================================================
// 3. VOTING TESTS - OWNER ACTIONS (HandlesVoting Trait)
// ============================================================================

test('owner can start voting for an issue', function () {
    $session = createTestSession();
    $owner = $session->owner; // Owner aus der Session verwenden
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => IssueStatus::NEW,
    ]);

    Event::fake([IssueSelected::class]);

    $component = createSessionPageComponent($session, $owner);
    $component->call('startVoting', $issue->id);

    $issue->refresh();
    expect($issue->status)->toBe(IssueStatus::VOTING);
    expect($component->get('currentIssue'))->not->toBeNull();
    expect($component->get('currentIssue')->id)->toBe($issue->id);
    expect($component->get('votesRevealed'))->toBeFalse();

    Event::assertDispatched(IssueSelected::class);
});

test('owner cannot start voting if not owner', function () {
    $session = createTestSession();
    $nonOwner = User::factory()->create();
    $session->users()->attach($nonOwner->id);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => IssueStatus::NEW,
    ]);

    $component = createSessionPageComponent($session, $nonOwner);
    $component->call('startVoting', $issue->id);

    $issue->refresh();
    expect($issue->status)->toBe(IssueStatus::NEW);
    expect($component->get('currentIssue'))->toBeNull();
});

test('starting voting loads existing async votes', function () {
    $session = createTestSession();
    $owner = $session->owner;
    $voter = User::factory()->create();
    $session->users()->attach($voter->id);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => IssueStatus::NEW,
    ]);

    // Voter votes async before owner starts voting
    Vote::factory()->create([
        'user_id' => $voter->id,
        'issue_id' => $issue->id,
        'value' => 8,
    ]);

    $component = createSessionPageComponent($session, $owner);
    $component->call('startVoting', $issue->id);

    expect($component->get('votedUserIds'))->toContain($voter->id);
    expect($component->get('votesByUser')[$voter->id])->toBe(8);
});

test('owner can reveal votes when at least one vote exists', function () {
    $session = createTestSession();
    $owner = $session->owner;
    $voter = User::factory()->create();
    $session->users()->attach($voter->id);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => IssueStatus::VOTING,
    ]);

    Vote::factory()->create([
        'user_id' => $voter->id,
        'issue_id' => $issue->id,
        'value' => 5,
    ]);

    Event::fake([RevealVotes::class]);

    $component = createSessionPageComponent($session, $owner);
    $component->set('currentIssue', $issue);
    $component->set('votedUserIds', [$voter->id]);
    $component->set('votesByUser', [$voter->id => 5]);

    $component->call('revealVotes');

    expect($component->get('votesRevealed'))->toBeTrue();
    Event::assertDispatched(RevealVotes::class);
});

test('owner can hide votes after revealing', function () {
    $session = createTestSession();
    $owner = $session->owner;
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => IssueStatus::VOTING,
    ]);

    Event::fake([HideVotes::class]);

    $component = createSessionPageComponent($session, $owner);
    $component->set('currentIssue', $issue);
    $component->set('votesRevealed', true);

    $component->call('hideVotes');

    expect($component->get('votesRevealed'))->toBeFalse();
    Event::assertDispatched(HideVotes::class);
});

test('owner can cancel voting', function () {
    $session = createTestSession();
    $owner = $session->owner;
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => IssueStatus::VOTING,
    ]);

    Event::fake([IssueCanceled::class]);

    $component = createSessionPageComponent($session, $owner);
    $component->set('currentIssue', $issue);

    $component->call('cancelVoting');

    $issue->refresh();
    expect($issue->status)->toBe(IssueStatus::NEW);
    expect($component->get('currentIssue'))->toBeNull();
    expect($component->get('votesRevealed'))->toBeFalse();

    Event::assertDispatched(IssueCanceled::class);
});

test('owner can restart voting (clears all votes)', function () {
    $session = createTestSession();
    $owner = $session->owner;
    $voter = User::factory()->create();
    $session->users()->attach($voter->id);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => IssueStatus::VOTING,
    ]);

    Vote::factory()->create([
        'user_id' => $voter->id,
        'issue_id' => $issue->id,
        'value' => 5,
    ]);

    Event::fake([HideVotes::class]);

    $component = createSessionPageComponent($session, $owner);
    $component->set('currentIssue', $issue);
    $component->set('votesRevealed', true);

    $component->call('restartVoting');

    expect(Vote::where('issue_id', $issue->id)->count())->toBe(0);
    expect($component->get('votesRevealed'))->toBeFalse();
    expect($component->get('votedUserIds'))->toBeEmpty();
    expect($component->get('myVote'))->toBeNull();

    Event::assertDispatched(HideVotes::class);
});

test('owner can confirm estimate and finish issue', function () {
    $session = createTestSession();
    $owner = $session->owner;
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => IssueStatus::VOTING,
    ]);

    Event::fake([IssueCanceled::class]);

    $component = createSessionPageComponent($session, $owner);
    $component->set('currentIssue', $issue);

    $component->call('confirmEstimate', 8);

    $issue->refresh();
    expect($issue->status)->toBe(IssueStatus::FINISHED);
    expect($issue->storypoints)->toBe(8);
    expect($component->get('currentIssue'))->toBeNull();

    Event::assertDispatched(IssueCanceled::class);
});

test('confirm estimate triggers Jira sync when issue has Jira link (browse url)', function () {
    $session = createTestSession();
    $owner = $session->owner;
    $owner->update([
        'jira_url' => 'https://jira.example.com',
        'jira_user' => 'user@example.com',
        'jira_api_key' => 'token',
    ]);

    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => IssueStatus::VOTING,
        'jira_key' => null,
        'jira_url' => 'https://jira.example.com/browse/ABC-123',
    ]);

    $mock = \Mockery::mock(SyncStoryPointsToJira::class);
    $mock->shouldReceive('sync')
        ->once()
        ->with(
            \Mockery::on(fn($u) => $u instanceof User && $u->id === $owner->id),
            \Mockery::on(fn($i) => $i instanceof Issue && $i->id === $issue->id && $i->storypoints === 8),
        );
    app()->instance(SyncStoryPointsToJira::class, $mock);

    $component = createSessionPageComponent($session, $owner);
    $component->set('currentIssue', $issue);

    $component->call('confirmEstimate', 8);
});

test('legacy v2 url redirects to voting url', function () {
    $session = createTestSession();
    $user = $session->owner;

    $this->actingAs($user)
        ->get(route('session.v2', $session->invite_code))
        ->assertRedirect(route('session.voting', $session->invite_code));
});

test('session root url redirects to voting url', function () {
    $session = createTestSession();
    $user = $session->owner;

    $this->actingAs($user)
        ->get(route('session.show', $session->invite_code))
        ->assertRedirect(route('session.voting', $session->invite_code));
});

test('v2 voting page attaches user to session on first visit', function () {
    $session = createTestSession();
    $user = User::factory()->create();

    expect($session->users()->whereKey($user->id)->exists())->toBeFalse();

    Livewire::actingAs($user)
        ->test(SessionPage::class, ['inviteCode' => $session->invite_code]);

    $session->refresh();
    expect($session->users()->whereKey($user->id)->exists())->toBeTrue();
});

test('voting url redirects to archived view when session is archived', function () {
    $session = createTestSession();
    $user = $session->owner;
    $session->update(['archived_at' => now()]);

    $this->actingAs($user)
        ->get(route('session.voting', $session->invite_code))
        ->assertRedirect(route('session.archived', $session->invite_code));
});

// ============================================================================
// 4. VOTING TESTS - VOTER ACTIONS (HandlesVoting Trait)
// ============================================================================

test('voter can submit vote', function () {
    $session = createTestSession();
    $voter = User::factory()->create();
    $session->users()->attach($voter->id);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => IssueStatus::VOTING,
    ]);

    Event::fake([AddVote::class]);

    $component = createSessionPageComponent($session, $voter);
    $component->set('currentIssue', $issue);

    $component->call('submitVote', 5);

    expect(Vote::where('user_id', $voter->id)->where('issue_id', $issue->id)->exists())->toBeTrue();
    $vote = Vote::where('user_id', $voter->id)->where('issue_id', $issue->id)->first();
    expect($vote->value)->toBe(5);
    expect($component->get('myVote'))->toBe(5);

    Event::assertDispatched(AddVote::class);
});

test('voter can remove their vote', function () {
    $session = createTestSession();
    $voter = User::factory()->create();
    $session->users()->attach($voter->id);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => IssueStatus::VOTING,
    ]);

    Vote::factory()->create([
        'user_id' => $voter->id,
        'issue_id' => $issue->id,
        'value' => 5,
    ]);

    Event::fake([AddVote::class]);

    $component = createSessionPageComponent($session, $voter);
    $component->set('currentIssue', $issue);
    $component->set('myVote', 5);

    $component->call('removeVote');

    expect(Vote::where('user_id', $voter->id)->where('issue_id', $issue->id)->exists())->toBeFalse();
    expect($component->get('myVote'))->toBeNull();

    Event::assertDispatched(AddVote::class);
});

test('voter cannot vote if no current issue', function () {
    $session = createTestSession();
    $voter = User::factory()->create();
    $session->users()->attach($voter->id);

    $component = createSessionPageComponent($session, $voter);
    $component->set('currentIssue', null);

    $component->call('submitVote', 5);

    expect(Vote::where('user_id', $voter->id)->count())->toBe(0);
});

// ============================================================================
// 5. ISSUE MANAGEMENT TESTS (HandlesIssues Trait)
// ============================================================================

test('owner can add issue manually', function () {
    $session = createTestSession();
    $owner = $session->owner;

    Event::fake([IssueAdded::class]);

    $component = createSessionPageComponent($session, $owner);
    $component->set('drawerTab', 'manual');
    $component->set('newIssueTitle', 'Test Issue');
    $component->set('newIssueDescription', 'Test Description');
    $component->set('newIssueJiraKey', 'SAN-123');
    $component->set('newIssueJiraUrl', 'https://jira.example.com/browse/SAN-123');

    $component->call('addIssue');

    $issue = Issue::where('session_id', $session->id)->where('title', 'Test Issue')->first();
    expect($issue)->not->toBeNull();
    expect($issue->description)->toBe('Test Description');
    expect($issue->jira_key)->toBe('SAN-123');
    expect($issue->jira_url)->toBe('https://jira.example.com/browse/SAN-123');
    expect($issue->status)->toBe(IssueStatus::NEW);
    expect($component->get('drawerOpen'))->toBeFalse();

    Event::assertDispatched(IssueAdded::class);
});

test('owner can delete issue', function () {
    $session = createTestSession();
    $owner = $session->owner;
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => IssueStatus::NEW,
    ]);

    Event::fake([IssueDeleted::class]);

    $component = createSessionPageComponent($session, $owner);
    $component->call('deleteIssue', $issue->id);

    expect(Issue::find($issue->id))->toBeNull();
    Event::assertDispatched(IssueDeleted::class);
});

test('owner cannot delete issue that is currently being voted on', function () {
    $session = createTestSession();
    $owner = $session->owner;
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => IssueStatus::VOTING,
    ]);

    $component = createSessionPageComponent($session, $owner);
    $component->call('deleteIssue', $issue->id);

    expect(Issue::find($issue->id))->not->toBeNull();
});

test('owner can update issue order', function () {
    $session = createTestSession();
    $owner = $session->owner;
    $issue1 = Issue::factory()->create(['session_id' => $session->id, 'position' => 0]);
    $issue2 = Issue::factory()->create(['session_id' => $session->id, 'position' => 1]);
    $issue3 = Issue::factory()->create(['session_id' => $session->id, 'position' => 2]);

    Event::fake([IssueOrderChanged::class]);

    $component = createSessionPageComponent($session, $owner);
    $component->call('updateIssueOrder', [$issue3->id, $issue1->id, $issue2->id]);

    expect(Issue::find($issue3->id)->position)->toBe(0);
    expect(Issue::find($issue1->id)->position)->toBe(1);
    expect(Issue::find($issue2->id)->position)->toBe(2);

    Event::assertDispatched(IssueOrderChanged::class);
});

test('non-owner cannot add issue', function () {
    $session = createTestSession();
    $nonOwner = User::factory()->create();
    $session->users()->attach($nonOwner->id);

    $component = createSessionPageComponent($session, $nonOwner);
    $component->set('newIssueTitle', 'Test Issue');

    $component->call('addIssue');

    expect(Issue::where('title', 'Test Issue')->exists())->toBeFalse();
});

// ============================================================================
// 6. JIRA IMPORT TESTS (HandlesJiraImport Trait)
// ============================================================================

test('switchTab changes drawer tab and triggers jira filter loading', function () {
    $session = createTestSession();
    $owner = $session->owner;
    $owner->update([
        'jira_url' => 'https://jira.example.com',
        'jira_user' => 'test@example.com',
        'jira_api_key' => 'test-key',
    ]);

    $component = createSessionPageComponent($session, $owner);
    $component->set('drawerTab', 'manual');
    $component->set('jiraFiltersLoaded', false);

    $component->call('switchTab', 'jira');

    // Tab sollte geändert werden
    expect($component->get('drawerTab'))->toBe('jira');

    // onJiraTabOpened wird aufgerufen, aber ohne echte Jira-API wird nichts geladen
    // Das eigentliche Laden wird in einem separaten Integration-Test getestet
});

test('hasJiraCredentials returns true when credentials exist', function () {
    $session = createTestSession();
    $owner = $session->owner;
    $owner->update([
        'jira_url' => 'https://jira.example.com',
        'jira_user' => 'test@example.com',
        'jira_api_key' => 'test-key',
    ]);

    $component = createSessionPageComponent($session, $owner);

    // hasJiraCredentials ist eine public Methode, kann direkt aufgerufen werden
    $result = $component->instance()->hasJiraCredentials();
    expect($result)->toBeTrue();
});

test('hasJiraCredentials returns false when credentials missing', function () {
    $session = createTestSession();
    $owner = $session->owner;

    $component = createSessionPageComponent($session, $owner);

    // hasJiraCredentials ist eine public Methode, kann direkt aufgerufen werden
    $result = $component->instance()->hasJiraCredentials();
    expect($result)->toBeFalse();
});

// ============================================================================
// 7. INTEGRATION TESTS (User Flows)
// ============================================================================

test('complete voting flow: start -> vote -> reveal -> confirm', function () {
    $session = createTestSession();
    $owner = $session->owner;
    $voter1 = User::factory()->create();
    $voter2 = User::factory()->create();
    $session->users()->attach($voter1->id);
    $session->users()->attach($voter2->id);
    $issue = Issue::factory()->create([
        'session_id' => $session->id,
        'status' => IssueStatus::NEW,
    ]);

    Event::fake();

    // Owner starts voting
    $ownerComponent = createSessionPageComponent($session, $owner);
    $ownerComponent->call('startVoting', $issue->id);

    // Issue sollte jetzt VOTING sein
    $issue->refresh();
    expect($issue->status)->toBe(IssueStatus::VOTING);

    // Voter 1 votes (manuell, da Broadcasting in Tests nicht funktioniert)
    Vote::factory()->create([
        'user_id' => $voter1->id,
        'issue_id' => $issue->id,
        'value' => 5,
    ]);

    // Voter 2 votes
    Vote::factory()->create([
        'user_id' => $voter2->id,
        'issue_id' => $issue->id,
        'value' => 8,
    ]);

    // Owner lädt Votes neu (durch render oder direkt setzen)
    $ownerComponent->set('currentIssue', $issue);
    // Votes werden durch loadCurrentIssue() geladen, aber das ist protected
    // Stattdessen setzen wir die Votes direkt für den Test
    $ownerComponent->set('votedUserIds', [$voter1->id, $voter2->id]);
    $ownerComponent->set('votesByUser', [$voter1->id => 5, $voter2->id => 8]);
    $ownerComponent->call('revealVotes');

    expect($ownerComponent->get('votesRevealed'))->toBeTrue();
    expect($ownerComponent->get('votedUserIds'))->toHaveCount(2);

    // Owner confirms estimate
    $ownerComponent->call('confirmEstimate', 8);

    $issue->refresh();
    expect($issue->status)->toBe(IssueStatus::FINISHED);
    expect($issue->storypoints)->toBe(8);
});

test('render returns correct view data', function () {
    $session = createTestSession();
    $owner = $session->owner;
    $voter = User::factory()->create();
    $session->users()->attach($voter->id);
    $session->users()->attach($owner->id); // Owner muss auch zur users-Relation
    $session->load('users'); // Reload für korrekte Zählung
    Issue::factory()->count(3)->create([
        'session_id' => $session->id,
        'status' => IssueStatus::NEW,
    ]);
    Issue::factory()->count(2)->create([
        'session_id' => $session->id,
        'status' => IssueStatus::FINISHED,
    ]);

    $component = createSessionPageComponent($session, $owner);
    $view = $component->instance()->render();

    expect($view->getData()['isOwner'])->toBeTrue();
    expect($view->getData()['issueCount'])->toBe(5);
    expect($view->getData()['finishedCount'])->toBe(2);
    expect($view->getData()['participantCount'])->toBe(2); // Owner + Voter
});
