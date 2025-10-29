# Scrum Poker Tool - Feature Specification

## Overview
Ein webbasiertes Scrum Poker Tool für agile Teams zur Schätzung von Jira Issues. Das Tool bietet zwei spezialisierte Views: **Product Owner View** (Issue Management + Estimation Control) und **Developer View** (fokussiertes Voting Interface).

## Technischer Stack
- Frontend: Vanilla HTML/CSS/JavaScript
- Design System: Custom CSS mit Tailwind-ähnlichen Utilities
- Responsive: Mobile-First Approach
- Backend Integration: REST API (zu implementieren)

---

## 1. Product Owner View

### 1.1 Session Header
**Komponente:** Session Info & Participants

#### Features:
- **Session Titel** anzeigen (z.B. "Session: jira import")
- **Teilnehmer-Liste** in kompaktem Grid-Layout
  - 2-3 Spalten auf Desktop, 1 Spalte auf Mobile
  - Avatar (Initialen, 36px rund)
  - Name mit text-overflow: ellipsis bei langen Namen
  - Status-Indikator:
    - `✓` (grün) = hat gestimmt
    - `⏱` (grau) = wartend
  - Visual States:
    - Grüner Border wenn voted
    - Blauer Border für current user

#### Technische Details:
```html
<div class="participant-item voted">
  <div class="avatar-small">JS</div>
  <div class="participant-info">
    <div class="participant-name">John Smith</div>
  </div>
  <div class="participant-status">✓</div>
</div>
```

#### Backend Requirements:
- `GET /api/session/{id}/participants` - Liste aller Teilnehmer
- WebSocket für Live-Updates bei neuen Votes

---

### 1.2 Product Owner Panel (Estimation Control)

**Komponente:** Zentrales Control Panel für PO

#### Features:

##### 1.2.1 Current Issue Display
- **Gelber Info-Block** mit aktuellem Issue
- Zeigt:
  - Issue-ID (z.B. "SAN-4585")
  - Issue-Beschreibung
- Immer sichtbar während Voting-Phase

##### 1.2.2 Reveal Votes Button
- **Grüner Button** mit Eye-Icon
- States:
  - Enabled: "Reveal votes"
  - Disabled nach Click: "Votes revealed"
- Position: Direkt unter Current Issue
- Aktion: Triggert Reveal aller Schätzungen

##### 1.2.3 Grouped Estimates Display (nach Reveal)
- **Grid mit Estimate Cards**
  - Responsive: 4 Spalten auf Desktop, 2-3 auf Tablet, 1-2 auf Mobile
  - Jede Karte zeigt:
    - **Story Points** (große Zahl, z.B. "5")
    - **Anzahl Stimmen** (z.B. "3 Stimmen")
    - **Namen der Voter** (z.B. "John Smith, Sarah Jones, Anna Brown")
  - Interaktivität:
    - Klickbar zur Auswahl
    - Hover-State (blauer Border)
    - Selected-State (grüner Border + grüner Hintergrund)
  - Sortierung: Aufsteigend nach Story Points (3, 5, 8, 13, 21, 100)

##### 1.2.4 Custom Estimate Input
- **Input-Feld** für manuelle Eingabe
- Label: "Oder manuell eingeben:"
- Type: number, min="0"
- Use Case: Wenn PO einen Konsens-Wert wählt (z.B. 6 SP zwischen 5 und 8)

##### 1.2.5 Confirm Button
- **Grüner Primary Button**: "Schätzung übernehmen"
- Aktion:
  - Validiert, dass eine Schätzung ausgewählt wurde
  - Speichert finale Story Points
  - Verschiebt Issue zu "Geschätzte Issues"
  - Updated Badge-Counter
  - Reset des PO Panels für nächstes Issue

#### Technische Details:
```javascript
// Gruppierung der Votes
const groupedVotes = {
  "5": { count: 3, participants: ["John", "Sarah", "Anna"] },
  "8": { count: 1, participants: ["Mike"] },
  "13": { count: 1, participants: ["Tom"] }
};
```

#### Backend Requirements:
- `POST /api/session/{id}/reveal` - Reveal alle Votes
- `POST /api/session/{id}/issue/{issueId}/estimate` - Finale Schätzung speichern
  ```json
  {
    "storyPoints": 5,
    "votedBy": ["user1", "user2"],
    "decidedBy": "po_user_id"
  }
  ```

---

### 1.3 Issue Management

**Komponente:** Zwei-Tab System für offene und geschätzte Issues

#### Features:

##### 1.3.1 Tab Navigation
- **Tab 1:** "Offene Issues" mit Badge (Anzahl)
- **Tab 2:** "Geschätzte Issues" mit Badge (Anzahl)
- Active State: Blauer Hintergrund + weiße Schrift
- Inactive State: Grauer Text + transparenter Hintergrund

##### 1.3.2 Issues Liste (Offene Issues)
- **Kompakte Listen-Darstellung** (keine Cards!)
- Jedes Issue-Item zeigt:
  - Issue-ID (z.B. "SAN-4585", blauer Badge)
  - Issue-Beschreibung (truncated mit ellipsis)
  - Action Buttons:
    - "Vote now" (grüner Button) - Startet Voting für dieses Issue
    - "×" (roter Button) - Entfernt Issue aus Session
- **Active Issue Highlight:**
  - Gelber Hintergrund
  - Oranger Left-Border (3px)
  - Zeigt aktuell zu schätzendes Issue

##### 1.3.3 Issues Liste (Geschätzte Issues)
- Gleiche Listen-Darstellung
- Zeigt statt "Vote now":
  - Story Points Display (z.B. "5 SP", grün)
  - "×" Button bleibt

##### 1.3.4 Issue States
```
Workflow: Offene Issues → [Voting] → Geschätzte Issues
```

#### Technische Details:
```javascript
// Issue Item Structure
{
  id: "SAN-4585",
  title: "API Integration für Zahlungsabwicklung",
  description: "Implementierung einer RESTful API...",
  status: "open" | "voting" | "estimated",
  storyPoints: null | number
}
```

#### Backend Requirements:
- `GET /api/session/{id}/issues?status=open` - Offene Issues
- `GET /api/session/{id}/issues?status=estimated` - Geschätzte Issues
- `POST /api/session/{id}/issue/{issueId}/start-voting` - Issue zum Voting freigeben
- `DELETE /api/session/{id}/issue/{issueId}` - Issue aus Session entfernen

---

### 1.4 Sidebar Actions

**Komponente:** Import & Manual Add Panels

#### Features:

##### 1.4.1 Jira Import Panel
- **Card mit Icon** (Cloud-Download)
- Button: "Issues aus Jira importieren"
- Aktion: Öffnet Jira-Import Dialog/Modal
- Position: Links auf Desktop, unten auf Mobile (order: 2)

##### 1.4.2 Manual Add Panel
- **Card mit Icon** (Edit)
- Input-Felder:
  - Issue-Titel (text input)
  - Beschreibung (textarea, optional)
- Button: "Issue hinzufügen"
- Validierung: Mindestens Titel erforderlich

#### Backend Requirements:
- `POST /api/session/{id}/issues/import` - Jira Issues importieren
  ```json
  {
    "jiraProject": "SAN",
    "jql": "project = SAN AND status = 'To Do'",
    "issueIds": ["SAN-4585", "SAN-4586"]
  }
  ```
- `POST /api/session/{id}/issues` - Manuelles Issue erstellen
  ```json
  {
    "title": "New Issue",
    "description": "Optional description"
  }
  ```

---

### 1.5 Responsive Behavior (PO View)

#### Desktop (>1024px)
- Main Grid: `grid-template-columns: 380px 1fr;`
- Sidebar links, Issues rechts
- Participants: 2-3 Spalten
- Estimate Cards: 4 Spalten

#### Tablet (640px - 1024px)
- Main Grid: 1 Spalte
- Sidebar unter Issues (`order: 2`)
- Participants: 2 Spalten
- Estimate Cards: 2-3 Spalten

#### Mobile (<640px)
- Alles 1 Spalte
- Reduzierte Paddings (24px → 16px)
- Kleinere Fonts
- Participants: 1 Spalte
- Estimate Cards: 1-2 Spalten

---

## 2. Developer View

### 2.1 Session Header

**Komponente:** Kompakte Session Info

#### Features:
- **Session Titel** + **Progress Counter**
  - z.B. "Session: jira import"
  - z.B. "6 Teilnehmer • 1 von 9 Issues geschätzt"
- Responsive: Stack auf Mobile (flex-direction: column)

---

### 2.2 Participants Section

**Komponente:** Gleiche Teilnehmer-Liste wie PO View

#### Features:
- Section Header: "TEILNEHMER" (uppercase, grau)
- Identische Darstellung wie PO View
- Zeigt Voting-Status aller Teilnehmer
- Updates live via WebSocket

---

### 2.3 Current Issue Display

**Komponente:** Hero Section mit aktuellem Issue

#### Features:
- **Prominenter blauer Border** (2px)
- **Issue Label:** "AKTUELL ZU SCHÄTZEN" (uppercase, blau)
- **Issue-ID:** Fett, groß (z.B. "SAN-4585")
- **Issue-Titel:** Groß, prominent (20px)
- **Issue-Beschreibung:** 
  - Vollständiger Text (kein Truncate)
  - 2-3 Sätze Context
  - Font-size: 15px, line-height: 1.6
- **Meta-Informationen:**
  - Epic Tag (mit Icon)
  - Assignee (mit Icon)
  - Weitere custom fields möglich

#### Technische Details:
```html
<div class="current-issue">
  <div class="issue-label">Aktuell zu schätzen</div>
  <div class="issue-id">SAN-4585</div>
  <div class="issue-title">API Integration für Zahlungsabwicklung implementieren</div>
  <div class="issue-description">
    Implementierung einer RESTful API zur Anbindung externer Zahlungsdienstleister...
  </div>
  <div class="issue-meta">
    <div class="issue-meta-item">
      <svg>...</svg>
      Epic: Payment System
    </div>
  </div>
</div>
```

---

### 2.4 Voting Cards

**Komponente:** Hauptinteraktionselement

#### Features:

##### 2.4.1 Card Grid
- **9 große Voting Cards** in responsive Grid
- Werte: `1, 2, 3, 5, 8, 13, 21, 100, ?`
- Grid: `repeat(auto-fit, minmax(80px, 1fr))`
- Mobile: 4 Spalten (3x3 Grid)

##### 2.4.2 Card States
- **Default:**
  - Weißer Hintergrund
  - Grauer Border (3px)
  - Große Zahl (32px, bold)
- **Hover:**
  - Blauer Border
  - Hellblauer Hintergrund
  - Transform: translateY(-4px)
  - Box-Shadow
- **Selected:**
  - Blauer Hintergrund (#4f46e5)
  - Weiße Schrift
  - Transform: translateY(-4px)
  - Stärkere Box-Shadow

##### 2.4.3 Special Cards
- **"100":** Signalisiert "Zu groß, muss gesplittet werden"
- **"?":** "Keine Ahnung / kann nicht schätzen"

##### 2.4.4 Card Aspect Ratio
- `aspect-ratio: 2/3` (Playing Card Format)
- Verhindert Layout-Shift

#### Technische Details:
```javascript
let selectedValue = null;

function selectVote(value, element) {
  // Deselect all
  document.querySelectorAll('.vote-card').forEach(card => {
    card.classList.remove('selected');
  });
  
  // Select clicked
  element.classList.add('selected');
  selectedValue = value;
}
```

---

### 2.5 Vote Actions

**Komponente:** Action Buttons unter Voting Cards

#### Features:

##### 2.5.1 Button: "Schätzung ändern"
- Secondary Button (grauer Hintergrund)
- Use Case: Developer hat bereits gestimmt, will ändern
- Aktion: Re-enables Voting (entfernt "voted" Status temporär)

##### 2.5.2 Button: "Schätzung abgeben"
- Primary Button (grüner Hintergrund)
- Disabled wenn keine Card selected
- Aktion:
  - Sendet Vote an Backend
  - Zeigt "Vote Status" Message
  - Updated eigenen Participant Status zu "✓"
  - Disabled Voting Cards

##### 2.5.3 Vote Status Message (nach Voting)
- Ersetzt Voting Section komplett
- Hellblauer Hintergrund
- Text: "✓ Deine Schätzung (X SP) wurde abgegeben. Warte auf andere Teilnehmer..."
- Bleibt bis PO das nächste Issue startet

#### Backend Requirements:
- `POST /api/session/{id}/vote`
  ```json
  {
    "issueId": "SAN-4585",
    "userId": "user_123",
    "storyPoints": 5
  }
  ```
- `PUT /api/session/{id}/vote` - Vote ändern
- WebSocket: `vote.submitted` Event für Live-Updates

---

### 2.6 Upcoming Issues (Collapsible)

**Komponente:** Collapsed Liste zukünftiger Issues

#### Features:

##### 2.6.1 Collapsible Header
- **Klickbarer Header** (ganzer Bereich)
- Icon + Titel: "Noch zu schätzen"
- **Counter:** "(8 Issues)"
- **Chevron Icon:** Dreht sich bei Expand (transform: rotate(180deg))
- Hover: Text wird blau

##### 2.6.2 Content (wenn expanded)
- Liste aller offenen Issues
- Jedes Issue zeigt:
  - Issue-ID
  - Issue-Titel
  - Badge:
    - "NÄCHSTES" (gelb) für das nächste Issue
    - "WARTEND" (grau) für alle anderen

##### 2.6.3 Animation
- `max-height: 0` → `max-height: 500px`
- Transition: 0.3s ease
- Smooth expand/collapse

#### Technische Details:
```javascript
function toggleUpcoming() {
  const content = document.getElementById('upcomingContent');
  const icon = document.getElementById('collapseIcon');
  
  content.classList.toggle('expanded');
  icon.classList.toggle('expanded');
}
```

---

### 2.7 Estimated Issues History (Collapsible)

**Komponente:** Collapsed Liste geschätzter Issues

#### Features:

##### 2.7.1 Collapsible Header
- Gleiche Interaktion wie "Upcoming Issues"
- Icon + Titel: "Bereits geschätzt"
- Counter: "(X Issues)"

##### 2.7.2 Content (wenn expanded)
- Liste aller geschätzten Issues der Session
- Jedes Issue zeigt:
  - Issue-ID
  - Issue-Titel
  - **Story Points** (grün, rechts aligned)

##### 2.7.3 Default State
- **Eingeklappt** (max-height: 0)
- Minimiert Ablenkung
- Developer kann bei Bedarf expandieren

---

### 2.8 Responsive Behavior (Developer View)

#### Desktop (>1024px)
- Max-width: 1000px (zentriert)
- Participants: 2-3 Spalten
- Voting Cards: 9 Spalten (3x3 Raster)

#### Tablet (640px - 1024px)
- Full width mit Padding
- Participants: 2 Spalten
- Voting Cards: 5-6 Spalten

#### Mobile (<640px)
- Reduzierte Paddings (32px → 20px → 16px)
- Participants: 1 Spalte
- Voting Cards: 4 Spalten (besser für Touch)
- Kleinere Card-Fonts (32px → 24px)
- Vote Actions: Stack vertikal (flex-direction: column)
- Issue Meta: Stack vertikal

---

## 3. Shared Features & States

### 3.1 Issue Workflow States

```
States:
1. "queued" - In Session, nicht aktiv
2. "voting" - Aktuell wird geschätzt
3. "estimated" - Schätzung abgeschlossen
```

### 3.2 User Roles

```
- "product_owner" - Kann Issues managen, Voting starten/stoppen, finale Estimates setzen
- "developer" - Kann nur voten
- "observer" - Kann nur zuschauen (nice-to-have)
```

### 3.3 Real-time Updates (WebSocket Events)

#### Events die Frontend empfangen sollte:
```javascript
// Neuer Teilnehmer joined
socket.on('participant.joined', (data) => {
  // { userId, userName, avatar }
});

// Vote wurde abgegeben
socket.on('vote.submitted', (data) => {
  // { userId, issueId } - OHNE Story Points (hidden)
});

// PO hat Votes revealed
socket.on('votes.revealed', (data) => {
  // { issueId, votes: [{ userId, storyPoints }] }
});

// Neues Issue zum Voting gestartet
socket.on('voting.started', (data) => {
  // { issueId, issueTitle, issueDescription }
});

// Issue wurde final geschätzt
socket.on('issue.estimated', (data) => {
  // { issueId, storyPoints }
});

// Session geschlossen
socket.on('session.closed', (data) => {
  // { sessionId }
});
```

---

## 4. API Endpoints Summary

### Session Management
```
GET    /api/sessions
POST   /api/sessions
GET    /api/session/{id}
DELETE /api/session/{id}
PATCH  /api/session/{id}
```

### Participants
```
GET    /api/session/{id}/participants
POST   /api/session/{id}/participants
DELETE /api/session/{id}/participants/{userId}
```

### Issues
```
GET    /api/session/{id}/issues
POST   /api/session/{id}/issues
POST   /api/session/{id}/issues/import
DELETE /api/session/{id}/issue/{issueId}
PATCH  /api/session/{id}/issue/{issueId}
```

### Voting
```
POST   /api/session/{id}/issue/{issueId}/start-voting
POST   /api/session/{id}/vote
PUT    /api/session/{id}/vote
POST   /api/session/{id}/reveal
POST   /api/session/{id}/issue/{issueId}/estimate
```

---

## 5. Data Models

### Session
```typescript
interface Session {
  id: string;
  title: string;
  createdBy: string;
  createdAt: Date;
  status: 'active' | 'closed';
  participants: Participant[];
  issues: Issue[];
  currentIssue?: string; // issueId
}
```

### Participant
```typescript
interface Participant {
  userId: string;
  userName: string;
  avatar: string; // Initials or URL
  role: 'product_owner' | 'developer' | 'observer';
  hasVoted: boolean;
  joinedAt: Date;
}
```

### Issue
```typescript
interface Issue {
  id: string; // Jira Issue Key
  title: string;
  description: string;
  status: 'queued' | 'voting' | 'estimated';
  storyPoints?: number;
  votes?: Vote[];
  estimatedAt?: Date;
  estimatedBy?: string; // userId of PO
  meta?: {
    epic?: string;
    assignee?: string;
    labels?: string[];
  };
}
```

### Vote
```typescript
interface Vote {
  userId: string;
  userName: string;
  storyPoints: number | '?';
  votedAt: Date;
  revealed: boolean;
}
```

---

## 6. Implementation Notes

### Performance Considerations
- Lazy load issue descriptions (nur laden wenn expanded)
- Virtualize long issue lists (>50 items)
- Debounce WebSocket reconnects
- Cache participant avatars

### Accessibility
- Keyboard navigation für Voting Cards (Tab + Enter)
- ARIA labels für Icons
- Focus management bei Modals
- Screen reader announcements für Live-Updates

### Security
- Validate user roles server-side
- Rate limiting für Vote submissions
- CSRF protection
- WebSocket authentication via JWT

### Testing Checklist
- [ ] Vote submission ohne Selection zeigt Error
- [ ] Concurrent votes werden korrekt gruppiert
- [ ] Reveal funktioniert mit allen Teilnehmern
- [ ] Collapsible Sections behalten State beim Tab-Switch
- [ ] Mobile Touch-Targets sind groß genug (min 44x44px)
- [ ] WebSocket reconnect funktioniert nach Verbindungsabbruch
- [ ] Session Close benachrichtigt alle Teilnehmer
- [ ] Lange Issue-Titel brechen korrekt um

---

## 7. Future Enhancements

### Phase 2 Features
- [ ] Session History & Analytics
- [ ] CSV/Excel Export der Schätzungen
- [ ] Custom Voting Scales (T-Shirt Sizes, Powers of 2)
- [ ] Timer für Voting-Runden
- [ ] Anonymous Voting Mode
- [ ] Session Templates
- [ ] Jira Sync (bidirektional)
- [ ] Team Velocity Tracking
- [ ] Estimation Confidence Scores

### Nice-to-have
- [ ] Dark Mode
- [ ] Custom Themes
- [ ] Participant Reactions/Emojis
- [ ] Voice Chat Integration
- [ ] Mobile Apps (iOS/Android)
- [ ] Browser Extensions
- [ ] Slack/Teams Integration

---

## 8. Design Tokens

### Colors
```css
/* Primary */
--color-primary: #4f46e5;
--color-primary-hover: #4338ca;
--color-primary-light: #f0f4ff;

/* Success */
--color-success: #10b981;
--color-success-hover: #059669;
--color-success-light: #ecfdf5;

/* Warning */
--color-warning: #f59e0b;
--color-warning-light: #fef3c7;

/* Neutral */
--color-gray-50: #f9fafb;
--color-gray-100: #f3f4f6;
--color-gray-200: #e5e7eb;
--color-gray-300: #d1d5db;
--color-gray-400: #9ca3af;
--color-gray-500: #6b7280;
--color-gray-900: #1a1a1a;
```

### Typography
```css
--font-sans: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
--font-size-xs: 11px;
--font-size-sm: 13px;
--font-size-base: 14px;
--font-size-lg: 16px;
--font-size-xl: 20px;
--font-size-2xl: 24px;
--font-size-3xl: 32px;
```

### Spacing
```css
--spacing-xs: 4px;
--spacing-sm: 8px;
--spacing-md: 12px;
--spacing-lg: 16px;
--spacing-xl: 24px;
--spacing-2xl: 32px;
```

### Border Radius
```css
--radius-sm: 6px;
--radius-md: 8px;
--radius-lg: 12px;
--radius-full: 9999px;
```

---

## Changelog

### v1.0.0 (Initial Release)
- Product Owner View mit Issue Management
- Developer View mit Voting Interface
- Real-time Updates via WebSocket
- Responsive Design für Mobile/Tablet/Desktop
- Collapsible Sections für Upcoming/History

---

**Document Version:** 1.0.0  
**Last Updated:** 2025-10-29  
**Author:** Development Team
