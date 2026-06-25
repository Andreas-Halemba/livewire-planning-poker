# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.1.0] - 2026-06-25

### Added

- Display parent epic key and title in issue lists and detail view (#38)

### Changed

- Added Prettier Blade plugin and applied consistent formatting across Blade templates (#39)

## [3.0.0] - 2026-05-22

### Added

- User-specific Jira credentials configurable via the profile page
- Jira ticket selection modal with DaisyUI and improved import for the SAN project
- Jira description rendering across all views, including an attachment proxy
- Story points synchronization with Jira and asynchronous voting screen with owner progress
- Voter/Viewer participant roles with UI toggle, broadcast via Echo
- Readonly archived sessions with reactivation flow
- `IssueStatus` enum and issue ordering within sessions
- Confetti animation when all eligible voters match on reveal (#34)
- Inspector APM integration for enhanced monitoring
- `viewPulse` gate for user access control
- App version surfaced in UI, logs, and Inspector for easier debugging
- Docker support with multi-stage build and GitHub Actions workflow publishing images to GHCR
- GitHub Actions CI workflow for Pint and Pest, plus release-triggered GHCR deploy
- Local-only dev user seeding and Herd configuration for local development

### Changed

- Upgraded to Laravel 13 (via Laravel 11 and 12), migrating to Laravel Reverb
- Migrated to DaisyUI v5 and Tailwind CSS v4
- Refactored component communication and shared session management trait
- Filtered user sessions to exclude owned sessions

### Fixed

- Validate manual issue form and surface field errors (#32)
- Handle empty and fractional vote averages
- Pin builder stages to native platform for multi-arch Docker builds (AND-69)
- Various CI fixes (asset build before tests, broadcast driver / app key on CI, Node 24 runners)

### Removed

- Removed unused `@soketi/soketi` dependency and obsolete Soketi configuration (#31)
- Removed deprecated Livewire components related to Jira import and voting
- Removed PHPInsights and obsolete Mailpit/Laravel Boost configuration

## [2.1.3] - 2025-10-31

### Fixed

- Fixed Telescope authorization gate to properly handle array of allowed emails from environment variable
- Fixed delete user form input ID conflicts for better form handling
- Prevented JiraImport snapshot errors

### Changed

- Updated broadcast events to use `toOthers()` for improved performance
- Enhanced vote retrieval and update logic in SessionParticipants component
- Updated Telescope configuration and routing
- Removed unused EstimationSession route from web.php
- Updated .env.example with Reverb configuration documentation

## [2.1.0] - 2025-10-30

### Added

- New icon libraries (Blade Clarity Icons and Blade Tabler Icons) for enhanced UI
- Enhanced voting functionality with improved real-time updates
- Icon buttons in issue list for better UX
- X-circle icon to cancel voting button

### Fixed

- Livewire DOM tree errors and race conditions resolved
- Issues not loading on first session join
- Participant count display issues
- Cursor pointer missing on cancel voting buttons

### Changed

- Refactored component communication for improved Product Owner UX
- Unified button styling across the application for consistency
- Made voting in progress badge fully rounded
- Consistent clickable links in voting views using `title_html`
- Improved form submit button styling

## [2.0.0] - 2024-XX-XX

### Added

- Major redesign of session screens
- Table view for session lists with DaisyUI
- Enhanced UI/UX improvements

### Changed

- Replaced Alpine clipboard plugin with native browser API

### Fixed

- Various UI and functionality improvements

## [1.0.0] - Initial Release

### Added

- Initial Planning Poker application
- Session management
- Real-time voting functionality
- JIRA integration
