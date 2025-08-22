## [v1.1.0] - 2025-08-22

### Added
- Mailto contact button in the sidebar

### Commit history

- :memo: changelog: cut v1.0.9 (b77c484)
- :zap: add contact button (e49344b)


## [v1.0.9] - 2025-08-22

### Fixed
- add proper validation on housing dates in month calendar to avoid null errors 

### Commit history

- :memo: changelog: cut v1.0.8 (fcc5bc7)
- :bug: fix housing with no date in month calendar (80b30b1)


## [v1.0.8] - 2025-08-21

### Fixed
- make release command stop generating empty section in the changelog so everything is managed by the make changelog command

### Commit history

- :memo: changelog: cut v1.0.7 (2f98687)
- :bug: stop changelog generation in release (4ec3682)


## [v1.0.7] - 2025-08-20

### Refactor
- improve html structure with good types, aria labels and descriptions
- improve keyboard navigation
- add all form informations and labels 
- add image alt


### Commit history

- :memo: changelog: cut v1.0.6 (a06b7b0)
- :wheelchair: add aria label and description (009f3e4)
- :wheelchair: improve keyboard navigation (07e21db)
- :wheelchair: improve form informations (09b382d)
- :wheelchair: improve image informations (eef9c55)


## [v1.0.6] - 2025-08-20

### Refactor
- Edit makefile to allow the user to add details in the release
- Use gitmojis in the release commits

### Commit history

- docs(changelog): update for v1.0.5 (433dacc)
- :zap: refactor changelog (a227ae7)


## [v1.0.5] - 2025-08-19

### Security
- Added and hardened HTTP security headers.
- Implemented login rate-limiting.
- Introduced security policies.
- Enabled composer audit and npm audit checks.

### Changed
- Streamlined user email verification flow.

### Commit history

- docs(changelog): update for v1.0.4 (5fb3e9f)
- :lock: add security headers on requests (cd50867)
- :lock: add ratelimiter on login (62a4c57)
- :lock: improve security header (425aa31)
- :lock: add policies (4342b37)
- :lock: add composer and npm audit (1098111)
- :zap: improve user email verification process (a02ea23)
- chore: bump version to v1.0.5 (40aad39)

## [v1.0.4] - 2025-08-13

### CI
- Integrated Larastan into the test workflow.

### Chore
- Dependency bumps (commonmark, Livewire, axios, form-data, vite).

### Commit history

- docs(changelog): update for v1.0.3 (3208fb7)
- :bricks: add larastan in the tests action (96897eb)
- :arrow_up: Bump league/commonmark (d5063b9)
- :arrow_up: Bump livewire/livewire (2efa352)
- :arrow_up: Bump axios in the npm_and_yarn group across 1 directory (71a3217)
- :arrow_up: Bump form-data in the npm_and_yarn group across 1 directory (a521404)
- :arrow_up: Bump vite in the npm_and_yarn group across 1 directory (7655dea)
- chore: bump version to v1.0.4 (3de265d)
- docs(changelog): update for v1.0.4 (7e2393f)

## [v1.0.3] - 2025-08-12

### CI
- Run tests on every pull request.

### Commit history

- docs(changelog): update for v1.0.2 (7ccd72b)
- :bricks: run tests on every pu,ll request (3b233a8)

## [v1.0.2] - 2025-08-12

### CI
- Improved CI/CD pipeline and added code coverage reporting.
- Fixed deployment workflows.

### Refactor
- Introduced Duster and applied style/lint fixes.

### Fixed
- Linter failures.

### Commit history

- docs(changelog): update for v1.0.1 (f9ddfba)
- :bricks: improve the cicd process (bce36b2)
- :zap: add duster and fix errors (d9bfcac)
- :bricks: add code coverage to the cicd (edfda59)
- :bug: fix linter (517b85a)
- :bricks: fix deploy workflows (1223381)

## [v1.0.1] - 2025-08-10

### Added

- Sentry integration for error tracking.

### Commit history

- docs(changelog): update for v1.0.0 (5771bc7)
- :zap: add sentry (343c591)

## [v1.0.0] - 2025-08-10

### Added
- Project bootstrap and cleaned base install.
- Travel management (index, create, update, show, invitations).
- Activities module (create via modal, update, dedicated page, start/end dates, image management).
- Maps & geodata (searchable map on create, global travel map, GeoJSON + lat/long storage, custom markers).
- Calendars (month and week views, map ↔ event interactions like scroll-to-card and marker focus).
- Housings (create, update, map integration).
- Accounts & emails (email approval on registration).
- UI assets & navigation (translations, logo, favicon, navbar, breadcrumbs, loaders).
- Test infrastructure (initial test setup, examples for services/models/Livewire).

### CI
- Deployment actions and foundational test workflows.

### Commit history

- :zap: init repo (7365e71)
- :zap: cleanup base install (8ed9c0b)
- :zap: travel index (b422016)
- :zap: travel create (82742dc)
- :zap: improve travel invitation and add daterange picker (41813dd)
- :zap: add searchable map on travel create (9f2309c)
- :zap: store geojson and lat long in travel (6550217)
- :zap: lint (0a66f03)
- :zap: actions to deploy (2d922b7)
- :zap: replace id by uuids (ff5b531)
- :bug: display travel date when null (414825b)
- :zap: update readme (294e11b)
- :zap: improve travelfactory (efefe5d)
- :bug: display travel date when null (e70d744)
- :bug: throw 404 when travel not found (2b2c813)
- :zap: add activities index on travel page (15b6d2d)
- :lipstick: improve style (0abfe9c)
- :zap: add activity create modal (419973b)
- :zap: improve create activity and add toaster (ac76a21)
- :zap: add map on create activity (9eb8133)
- :zap: add global map on travel (d83bd06)
- :lipstick: improve global style (1e4181a)
- :zap: add update activity (c3e8e46)
- :zap: better management of update activity modal (3cc36f9)
- :zap: fix the search map (7d70eac)
- :zap: error message when nominatim error (8415dd6)
- :zap: auto refresh map when adding travels (03aca81)
- :zap: change map marker picto (153cad3)
- :lipstick: improve design of the travel show (4464b94)
- :zap: manage invitations (896ce1b)
- :lipstick: improve design of the travel show (32784f7)
- :zap: update readme (84316f4)
- :zap: add a loader on search map (fb80f0b)
- :zap: some changes on the maps focus (73745c7)
- :zap: add images management on activities (7f47825)
- :zap: add activities start and end dates (9be1b64)
- :zap: fix upload carrousel (fdf9b96)
- :zap: add a dedicated page for activities (0c13155)
- :lipstick: remove dark theme (39aeca6)
- :hammer: test preprod (253ac3b)
- :zap: fix https prod (e28dc10)
- :zap: add email approval in register (8cd5e60)
- :zap: make activity description textarea nullable (5042c15)
- :zap: improve image on activity page (3b73cb2)
- :zap: update travel invitation mail text (2da404d)
- :zap: cleanup create modal on close (3a58775)
- :zap: cleanup update modal on close (308b414)
- :zap: add translations (0e66bca)
- :zap: add logo and favicon (6388fff)
- :zap: add breadcrumb on travel index (d7dcd6f)
- :zap: add custom marker on search map (7aee337)
- :zap: change name of the page (f514381)
- :bug: remove useless console log (893b411)
- :zap: update travel (c85b657)
- :zap: replace nominatim by mapbox (33c194e)
- :zap: add navbar in travel show (e77dcc1)
- :bug: market not showing when re opening modal (d714c61)
- :zap: add housings (21c77db)
- :zap: add create housing (52a429a)
- :zap: add housings to global map (cba301c)
- :zap: add str limit on url (d4f281f)
- :zap: add housing update (9e005ac)
- :bug: deal with events instead of redirect when updating activity (0de5830)
- :bug: fix image carrousel not being refreshed (08cc127)
- :bug: map refresh on activity when lo location is selected (102beb8)
- :bug: map refresh on housing when no location is selected (27f46cf)
- :bug: item end time on component (368b21b)
- :zap: improve url management (d4d8d14)
- :zap: calendar by month on travel (031a82c)
- :zap: fix uuids (1cd91be)
- :zap: add week calendar (36911a6)
- :lipstick: better style for the month calendar (f7a21ed)
- :zap: scroll to card and show marker when click on event who have a place (2d1a5b6)
- :hammer: setup action for the tests (18bcc9d)
- :zap: add example tests for mapbox service and travel model (86a12da)
- :zap: add tests for housing and activities models (4599594)
- :zap: add tests for livewire weekcalendar (046a4c6)
- :zap: rename project (3cc5675)
- :bug: travel events retrieval when start and end null (a01b941)
- :zap: add locale for the tests (e2b746c)
- :zap: improve activity and housing seeders (19b266e)
- :zap: improve month calendar (start on the travel start month and add better housings visualisation) (f405129)
- :zap: improve week calendar : start on travel start week (183d8a6)
- :zap: mock mapbox api and test the map component (80484c8)
- :bug: tests in CI (0853c24)
- :zap: add all VerifyEmailController tests (ba26037)
- :zap: add all invitationcontroller test (d0a438d)
- :zap: add all livewire activity test (e74ae28)
- :zap: add missings livewire login test (b48608f)
- :zap: add missings livewire password reset test (8d02f2a)
- :zap: add missings livewire password reset test (aa57828)
- :zap: add all livewire housing test (4ed1255)
- :zap: add all livewire travel test (731bdbb)
- :zap: add missing verify email  test (0ba33f2)
- :zap: add missing daterangepicker test (c379dc5)
- :zap: add missing monthcalendar test (4d3d651)
- :zap: add missing models test (b0f64b9)
- :zap: add missing livewire  test (3da38ab)
- :zap: add profile update test (e599e23)
- :zap: add missing test (d838c86)
- :bug: fix ci media test (f0a05ea)
- :zap: delete nominatim (d1d25f6)
- :zap: add larastan (788de3b)
- :zap: changelog and release generator (2ec2968)
- docs(changelog): update for v1.0.0 (2f9514a)
- docs(changelog): update for v1.0.0 (7d560e9)
