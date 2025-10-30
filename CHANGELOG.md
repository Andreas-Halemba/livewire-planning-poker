# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
