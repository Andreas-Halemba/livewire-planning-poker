# Test-Konzept für V2 SessionPage Component

## Übersicht

Dieses Dokument beschreibt die Test-Strategie für die `App\Livewire\V2\SessionPage` Komponente.

## Test-Architektur

### Framework

- **Pest PHP** für moderne, lesbare Tests
- **Livewire Testing** für Component-Tests
- **RefreshDatabase** für isolierte Datenbank-Tests
- **Event Fake** für Event-Verifikation

### Test-Struktur

```
tests/Feature/V2/
├── SessionPageTest.php          # Haupttest-Datei
└── TEST-KONZEPT.md              # Dieses Dokument
```

## Test-Kategorien

### 1. **Presence Tests** (`HandlesPresence` Trait)

- ✅ Component initialisiert mit aktuellem User als online
- ✅ `handleUsersHere` aktualisiert Online-Liste
- ✅ `handleUserJoining` fügt User hinzu
- ✅ `handleUserLeaving` entfernt User

**Weitere Ideen:**

- [ ] Edge Case: User verlässt während Voting
- [ ] Edge Case: Mehrere User joinen gleichzeitig
- [ ] Performance: 100+ Online-User

### 2. **Voting Tests - Owner Actions** (`HandlesVoting` Trait)

- ✅ Owner kann Voting starten
- ✅ Nicht-Owner kann kein Voting starten
- ✅ Start lädt existierende async Votes
- ✅ Owner kann Votes aufdecken
- ✅ Owner kann Votes verdecken
- ✅ Owner kann Voting abbrechen
- ✅ Owner kann Voting neu starten (löscht Votes)
- ✅ Owner kann Schätzung bestätigen

**Weitere Ideen:**

- [ ] Owner kann kein Voting starten wenn bereits eins läuft (wird automatisch beendet)
- [ ] Edge Case: Voting abbrechen während Votes aufgedeckt sind
- [ ] Edge Case: Bestätigen mit ungültigem Wert

### 3. **Voting Tests - Voter Actions** (`HandlesVoting` Trait)

- ✅ Voter kann Vote abgeben
- ✅ Voter kann Vote zurücknehmen
- ✅ Voter kann nicht voten wenn kein Issue aktiv

**Weitere Ideen:**

- [ ] Voter kann Vote ändern bevor aufgedeckt wird
- [ ] Voter kann nicht voten wenn Votes bereits aufgedeckt
- [ ] Edge Case: Mehrere Votes gleichzeitig (Race Condition)

### 4. **Issue Management Tests** (`HandlesIssues` Trait)

- ✅ Owner kann Issue manuell hinzufügen
- ✅ Owner kann Issue löschen
- ✅ Owner kann Issue-Reihenfolge ändern
- ✅ Nicht-Owner kann kein Issue hinzufügen

**Weitere Ideen:**

- [ ] Validierung: Issue-Titel ist required
- [ ] Validierung: Jira-URL muss valide sein
- [ ] Edge Case: Löschen während Drag & Drop
- [ ] Edge Case: Position-Update bei gleichzeitigen Änderungen

### 5. **Jira Import Tests** (`HandlesJiraImport` Trait)

- ✅ `switchTab` lädt Filter wenn Jira-Tab geöffnet wird
- ✅ `hasJiraCredentials` prüft korrekt

**Weitere Ideen:**

- [ ] `loadJiraFilters` cached in Session
- [ ] `loadFromFilter` lädt Tickets korrekt
- [ ] `loadFromInput` parst URL korrekt
- [ ] `loadFromInput` parst JQL korrekt
- [ ] `loadFromInput` parst Issue-Keys korrekt
- [ ] `importSelectedJiraTickets` importiert korrekt
- [ ] Duplikate werden erkannt (`alreadyImported`)
- [ ] Error-Handling bei Jira-API-Fehlern

### 6. **Integration Tests** (User Flows)

- ✅ Kompletter Voting-Flow: Start → Vote → Reveal → Confirm
- ✅ Render gibt korrekte View-Daten zurück

**Weitere Ideen:**

- [ ] Multi-User-Szenario: 5 Voter voten gleichzeitig
- [ ] Owner startet Voting während Voter async voten
- [ ] Issue wird gelöscht während Voting läuft
- [ ] Session wird geschlossen während Voting läuft

## Test-Setup & Helpers

### Helper-Funktionen

```php
// Erstellt Test-Session mit Owner und optionalen Teilnehmern
function createTestSession(array $participants = []): Session

// Erstellt SessionPage Component-Instanz für Tests
function createSessionPageComponent(Session $session, User $user): TestableLivewire
```

### Mocking-Strategien

#### Jira Service Mocking

```php
$jiraServiceMock = Mockery::mock(JiraService::class);
$jiraServiceMock->shouldReceive('getFavoriteFilters')
    ->once()
    ->andReturn([...]);

app()->instance(JiraService::class, $jiraServiceMock);
```

#### Event Fake

```php
Event::fake([IssueSelected::class]);
// ... Test-Code ...
Event::assertDispatched(IssueSelected::class);
```

## Test-Daten

### Factories nutzen

- `User::factory()->create()`
- `Session::factory()->create()`
- `Issue::factory()->create()`
- `Vote::factory()->create()`

### Test-Szenarien

1. **Minimal:** Owner + 1 Issue
2. **Standard:** Owner + 2 Voter + 3 Issues
3. **Komplex:** Owner + 5 Voter + 10 Issues + gemischte Status

## Best Practices

### ✅ DO

- Nutze `RefreshDatabase` für isolierte Tests
- Nutze `Event::fake()` für Event-Tests
- Nutze Factories für Test-Daten
- Teste sowohl Erfolgs- als auch Fehlerfälle
- Teste Authorization (Owner vs. Non-Owner)

### ❌ DON'T

- Keine echten API-Calls (immer mocken)
- Keine globalen State-Änderungen
- Keine Tests die voneinander abhängen
- Keine zu komplexen Setup-Szenarien (max. 5-10 Objekte)

## Coverage-Ziele

| Bereich          | Ziel | Status       |
| ---------------- | ---- | ------------ |
| Presence         | 80%  | 🟡 In Arbeit |
| Voting (Owner)   | 90%  | 🟡 In Arbeit |
| Voting (Voter)   | 80%  | 🟡 In Arbeit |
| Issue Management | 85%  | 🟡 In Arbeit |
| Jira Import      | 70%  | 🔴 Offen     |
| Integration      | 60%  | 🟡 In Arbeit |

## Ausführen der Tests

```bash
# Alle Tests
php artisan test

# Nur V2 Tests
php artisan test tests/Feature/V2

# Mit Coverage
php artisan test --coverage

# Einzelner Test
php artisan test --filter "owner can start voting"
```

## Nächste Schritte

1. ✅ Basis-Test-Struktur erstellt
2. 🔄 Jira Import Tests vervollständigen
3. 🔴 Edge Cases hinzufügen
4. 🔴 Performance-Tests (optional)
5. 🔴 Browser-Tests mit Laravel Dusk (optional)

## Notizen

- **Livewire Testing:** Nutze `Livewire::test()` für Component-Tests
- **Event Broadcasting:** Events werden nicht wirklich gebroadcastet in Tests (nur gefaked)
- **Presence Channels:** WebSocket-Tests sind komplex, fokus auf Handler-Logik
- **Jira API:** Immer mocken, nie echte Calls
