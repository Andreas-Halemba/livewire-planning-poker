# 🎉 Planning Poker v2.0 - Release Notes

## Willkommen zur größten Aktualisierung unserer Planning Poker App!

Wir freuen uns, euch Version 2.0 präsentieren zu können – ein umfassendes Update mit verbesserter Performance, modernem Design und vielen neuen Features, die eure Planning-Sessions noch effizienter machen.

---

## 🎨 Komplett überarbeitetes Design

### Modernes, Theme-fähiges Interface

- **Multi-Theme Support**: Wählt zwischen verschiedenen Farbschemata (Light, Dark, Cupcake, Fantasy und mehr)
- **Einheitliches Design**: Alle Seiten nutzen jetzt durchgehend moderne DaisyUI-Komponenten
- **Bessere Lesbarkeit**: Optimierte Kontraste und Abstände für längere Sessions ohne Augenermüdung
- **Neues Logo**: Frisches SVG-Logo für ein zeitgemäßes Erscheinungsbild

### Übersichtlichere Session-Verwaltung

- **Tabellenansicht statt Karten**: Alle Sessions werden jetzt in übersichtlichen Tabellen dargestellt
- **Bessere Aktionen**: Schneller Zugriff auf Join, Copy Link und Delete/Leave Funktionen
- **Copy-Link-Feedback**: Visuelles Feedback beim Kopieren von Einladungslinks ("Copied!" Bestätigung)

---

## 🎯 Verbesserte Voting-Experience

### Für Entwickler (Developer View)

- **Klarere Kartenauswahl**: Ausgewählte Voting-Karten haben jetzt einen deutlichen farbigen Hintergrund
- **Live-Updates**: Sofortige Aktualisierung der Ansicht, wenn der Product Owner Aktionen durchführt
- **Status-Feedback**: "Schätzung abgegeben" Status wird prominent angezeigt

### Für Product Owner

- **Übersichtliches Control Panel**: Alle wichtigen Funktionen zentral und klar strukturiert
- **Bessere Voting-Übersicht**: Gruppierte Anzeige der abgegebenen Stimmen mit visuellen Indikatoren
- **Estimation History**: Klare Trennung zwischen offenen und geschätzten Issues

### Teilnehmer-Ansicht

- **Owner immer zuerst**: Der Session-Owner wird immer als erstes angezeigt
- **Status-basierte Farben**:
    - 🔴 **Rot**: Voting wurde aufgedeckt, aber User hat nicht abgestimmt
    - 🟢 **Grün**: User hat seine Stimme abgegeben
    - 🟡 **Gelb**: Voting läuft, User hat noch nicht abgestimmt
    - ⚪ **Grau**: Kein aktives Voting
- **PO-Badge**: Product Owner wird mit "PO" Badge im Avatar gekennzeichnet
- **Status-Icons**: ✓ für abgestimmte, ? für nicht abgestimmte Teilnehmer
- **Bessere Namensanzeige**: Namen werden nicht mehr zu früh abgeschnitten

---

## 🔗 Jira Integration

### Benutzerfreundliche Ticket-Auswahl

- **Individuelles Jira-Konfiguration**: Jeder User kann seine eigenen Jira-Zugangsdaten im Profil hinterlegen
- **Ticket-Auswahl-Modal**: Wählt einzelne Tickets aus einer gefilterten Liste aus
- **Status-Filter**: Filtert nach Projekt und Status (z.B. "In Estimation", "To Do", "In Progress")
- **Smart Import**: Bereits importierte Tickets werden visuell hervorgehoben
- **Live-Updates**: Alle Teilnehmer sehen automatisch neu importierte Tickets

### Bessere Jira-Darstellung

- **Formatierte Beschreibungen**: Jira-Markup wird korrekt als HTML dargestellt
- **Kollabierbare Sections**: Lange Beschreibungen können ein- und ausgeklappt werden
- **Bilder & Formatierungen**: Unterstützung für Überschriften, Listen, Code-Blöcke und Bilder
- **Direkte Links**: Klickt direkt auf Issue-Titel, um zum Jira-Ticket zu gelangen
- **Automatische Story Points**: Bestätigte Schätzungen werden automatisch in Jira gespeichert

---

## ⚡ Technische Verbesserungen

### Laravel & Livewire Upgrades

- **Laravel 12**: Upgrade auf die neueste Laravel-Version für bessere Performance
- **Livewire 3**: Schnellere Reaktionszeiten und optimierte Datenübertragung
- **PHP 8.2**: Modernste PHP-Version für maximale Effizienz

### WebSocket-Infrastruktur

- **Laravel Reverb**: Ersetzt die alte WebSocket-Lösung durch Laravels offizielle Reverb-Technologie
- **Zuverlässigere Echtzeit-Updates**: Stabilere Verbindungen und schnellere Benachrichtigungen
- **Einfacheres Deployment**: Keine Docker-Abhängigkeit mehr notwendig

### Modern Stack

- **Tailwind CSS v4**: Neueste CSS-Framework-Version mit verbesserter Performance
- **DaisyUI v5**: Moderne UI-Komponenten mit erweiterten Theme-Optionen
- **Native Browser APIs**: Verwendung moderner Browser-Features (z.B. Clipboard API)

---

## 🐛 Bug Fixes & Stabilität

### Kritische Fixes

- ✅ Problem mit nicht initialisierten Properties in SessionParticipants behoben
- ✅ Vote-Änderung nach Reveal setzt jetzt korrekt den Status zurück zu "Voting"
- ✅ Echtzeit-Updates funktionieren jetzt zuverlässig für alle Teilnehmer
- ✅ Voting-Karten-Auswahl funktioniert korrekt mit numerischen und "?" Werten
- ✅ Sessions-Filter: Eigene Sessions erscheinen nicht mehr in "Your voting Sessions"

### UI/UX Verbesserungen

- ✅ Konsistente Abstände in allen Formularen
- ✅ Bessere Lesbarkeit bei deaktivierten Elementen
- ✅ Improved hover-Effekte und Übergänge
- ✅ Responsive Design für alle Bildschirmgrößen optimiert

---

## 📚 Weitere Verbesserungen

### Profile & Einstellungen

- **Minimalistischer Profile-Button**: Aufgeräumter Header mit Avatar-Initialen
- **Jira-Credentials im Profil**: Sichere Verwaltung eurer Jira-Zugangsdaten
- **Maskierte API-Keys**: API-Schlüssel werden aus Sicherheitsgründen verschleiert angezeigt

### Qualität & Wartbarkeit

- **Umfassende Tests**: Erweiterte Test-Suite für stabilere Releases
- **Code-Qualität**: PHPStan Level 9 für maximale Type-Safety
- **Dokumentation**: Verbesserte README mit detaillierten Setup-Anweisungen

---

## 🚀 Wie teste ich die neuen Features?

### Theme-Switcher

1. Klickt auf den Theme-Switcher in der Navigation (Palette-Icon)
2. Wählt eines der verfügbaren Themes aus
3. Das Theme wird sofort übernommen und im Browser gespeichert

### Jira-Integration

1. Geht zu **Profil → Jira Credentials**
2. Tragt eure Jira-URL, Username und API-Token ein
3. Speichert die Einstellungen
4. Erstellt oder öffnet eine Session als Product Owner
5. Klickt auf **Import from Jira**
6. Wählt Projekt und Status aus
7. Wählt einzelne Tickets aus der Liste
8. Die Tickets erscheinen sofort in der Session

### Session-Tabellen

1. Geht zum Dashboard
2. Eure Sessions werden jetzt in übersichtlichen Tabellen angezeigt
3. Klickt auf "Copy Link" um den Einladungslink zu kopieren
4. Ihr seht eine "Copied!" Bestätigung für 2 Sekunden

### Voting-Status in Echtzeit

1. Als Developer: Joined eine Session
2. Der Product Owner startet ein Voting mit "Vote Now"
3. Eure Ansicht aktualisiert sich automatisch
4. Wählt eine Karte aus – der Status wechselt zu grün
5. Nach Reveal seht ihr alle Stimmen
6. Wenn ihr euren Vote ändert, wechselt die Session automatisch zurück zu "Voting"

---

## 💡 Migration von v1.0

Keine speziellen Schritte erforderlich! Alle Änderungen sind abwärtskompatibel. Eure bestehenden Sessions und Daten bleiben erhalten.

**Hinweis für Jira-Nutzer**: Hinterlegt eure Jira-Credentials im Profil, um die neuen Import-Features zu nutzen.

---

## 🙏 Feedback & Support

Habt ihr Fragen oder Feedback zu den neuen Features?

- Meldet Bugs über GitHub Issues
- Teilt eure Feature-Wünsche im Discussions-Bereich

Viel Spaß mit Planning Poker v2.0! 🎯✨
