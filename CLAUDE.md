# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

CurtainCallWP is a WordPress plugin for theatre companies to manage and display productions, cast members, 
and crew members. It provides custom post types, metaboxes, REST API endpoints, and Gutenberg blocks. 

CurtainCallWP creates 2 new post types "Productions" and "CastAndCrew". These post types are linked through a 
pivot table. The pivot table's name is `ccwp_castandcrew_production`. 

- **Version**: 0.7.0
- **Requirements**: WordPress 6.5+, PHP 8.1+
- **Text Domain**: `curtain-call-wp`
- **License**: MIT

## Development Commands

### Local Development Setup
```bash
# Initial setup (Docker-based)
cp .env.example .env
# Edit .env to configure DB_DATABASE, DB_USER, DB_PASSWORD
# Add `127.0.0.1 wpsite.test` to /etc/hosts
./scripts/setup_local.sh
# Visit http://wpsite.test to complete WordPress setup
# Activate the CurtainCallWP plugin
```

### Frontend Assets (JavaScript/CSS)
```bash
npm run dev          # Watch mode - rebuilds on file changes
npm run build        # Production build - compiles all assets
npm run typecheck    # TypeScript type checking (both node and web configs)
npm run lint         # ESLint code checking
```

### PHP Code Quality
```bash
composer lint        # Lint PHP code using Mago
composer format      # Auto-format PHP code using Mago
composer analyse     # Static analysis using Mago
composer guard       # Run all Mago checks
```

### Plugin Dependency Management
```bash
composer plugin-install    # Install plugin dependencies
composer plugin-update     # Update plugin dependencies
composer build            # Install plugin deps optimized for production
```

### Build Plugin ZIP
```bash
# Build distributable plugin zip
bash scripts/build.sh           # Output: curtaincallwp.zip
bash scripts/build.sh 1.0.0     # Output: curtaincallwp-1.0.0.zip
```

### Docker Utilities
```bash
./scripts/clearcache.sh    # Clear WordPress cache
./scripts/update.sh        # Update Docker containers
```

**phpMyAdmin**: Access at `http://localhost:${PHP_MYADMIN_PORT}` (port in .env)

## Architecture

### Directory Structure

```
plugin/                      # Main plugin code
├── CurtainCall/             # PHP namespace (PSR-4 autoloaded)
│   ├── Blocks/              # Gutenberg block definitions
│   ├── Data/                # Data Transfer Objects (DTOs)
│   ├── Exceptions/          # Custom exception classes
│   ├── Hooks/               # WordPress hook handlers
│   ├── LifeCycle/           # Activation/deactivation/uninstall
│   ├── Models/              # Post type models (Production, CastAndCrew)
│   │   └── Traits/          # Model traits (HasAttributes, HasMeta, relationships)
│   ├── Rest/                # REST API controllers
│   └── Support/             # Utilities (View, Date, Str, Query)
├── assets/                  # COMPILED output (do not edit directly)
│   ├── admin/               # Admin JS/CSS bundles
│   └── frontend/            # Frontend JS/CSS bundles
├── views/                   # PHP templates
│   ├── admin/               # Admin metabox templates
│   └── frontend/            # Public-facing templates
├── CurtainCallWP.php        # Main plugin file (entry point)
└── functions.php            # Global helper functions

resources/                   # SOURCE files (edit these)
├── js/                      # TypeScript/React source
│   ├── admin/               # Admin-side React apps
│   ├── components/          # Shared React components
│   ├── types/               # TypeScript definitions
│   └── utils/               # JavaScript utilities
└── styles/                  # CSS/PostCSS source
    ├── admin/               # Admin styles
    ├── frontend/            # Frontend styles
    └── shared/              # Shared CSS (variables, mixins, grid)

theme/                       # Basic WordPress theme used only for development and testing
```

### Plugin Bootstrap Flow

```
CurtainCallWP.php (main file)
  → registerLifeCycleHooks()
  → run()
  → boot()
  → loadAllHooks() → Registers all hook handlers
```

**Key Constants** (defined in `plugin/CurtainCallWP.php`):
- `CCWP_PLUGIN_NAME`: "CurtainCallWP"
- `CCWP_PLUGIN_VERSION`: "0.7.0"
- `CCWP_PLUGIN_PATH`: Absolute path to plugin directory
- `CCWP_TEXT_DOMAIN`: "curtain-call-wp"
- `CCWP_DEBUG`: Development flag

### Hook Architecture

The plugin uses specialized hook handler classes in `plugin/CurtainCall/Hooks/`:

- **GlobalHooks**: Post type/taxonomy/meta registration, blocks, REST API
- **AdminHooks**: Admin JS/CSS enqueuing, settings page, title auto-generation
- **AdminCastCrewMetaboxHooks**: Cast & Crew metabox registration and saving
- **AdminProductionMetaboxHooks**: Production metabox registration and saving
- **FrontendHooks**: Frontend asset enqueuing, template loading

All hooks are registered via `addHooks()` method called during plugin initialization.

### Custom Post Types

**Production** (`ccwp_production`)
- Model: `plugin/CurtainCall/Models/Production.php`
- Hierarchical: Yes
- Has Archive: Yes
- Supports: title, editor, thumbnail, custom-fields
- Taxonomy: Production Seasons (`ccwp_production_seasons`)

**Meta Fields**:
- `ccwp_meta_name`: Production title
- `ccwp_meta_date_start`: Start date
- `ccwp_meta_date_end`: End date
- `ccwp_meta_show_times`: Performance times
- `ccwp_meta_ticket_url`: Ticket purchase URL
- `ccwp_meta_venue`: Location

**Key Methods**:
- `getPastPosts()`, `getCurrentPosts()`, `getFuturePosts()`: Filter by date
- `getChronologicalState()`: Returns 'past', 'current', or 'future'
- `getFormattedShowDates()`: Smart date formatting
- `getCastAndCrew(type)`: Get related cast/crew members

**Cast and Crew** (`ccwp_cast_and_crew`)
- Model: `plugin/CurtainCall/Models/CastAndCrew.php`
- Hierarchical: No
- Has Archive: Yes
- Supports: title, editor, thumbnail, custom-fields

**Meta Fields**:
- `ccwp_meta_name_first`: First name
- `ccwp_meta_name_last`: Last name
- `ccwp_meta_self_title`: Title. Usually what the person is most known for. (e.g. "Actor", "Director", "Scenic Designer", etc.)
- `ccwp_meta_birthday`: Birth date
- `ccwp_meta_hometown`: Hometown
- `ccwp_meta_website_link`: Portfolio URL
- `ccwp_meta_facebook_link`, `ccwp_meta_twitter_link`, `ccwp_meta_instagram_link`: Social media
- `ccwp_meta_fun_fact`: Short 1 line bio info that people might want to know about them

**Key Methods**:
- `getAlphaIndexes(query)`: Get alphabetical index letters for directory
- `getFullName()`: Formatted full name
- `getProductions()`: Get related productions (chronologically sorted)
- `rolesByProductionId(productions)`: Map roles to production IDs

### Pivot Table Relationship

**Table**: `ccwp_castandcrew_production`

The table that links productions posts to cast/crew member posts.

Note: it might be prefixed with `wp_` by WordPress.

Many-to-many relationship between Productions and Cast/Crew posts:
- `production_id`: BIGINT (The production post ID)
- `cast_and_crew_id`: BIGINT (The cast/crew post ID)
- `type`: VARCHAR (values: 'cast' or 'crew')
- `role`: VARCHAR (character name or crew position)
- `custom_order`: SMALLINT (display ordering)

**Model**: `plugin/CurtainCall/Models/CurtainCallPivot.php`

Created during plugin activation in `plugin/CurtainCall/LifeCycle/Activator.php:createPluginTables()`.

### Model Architecture Pattern

All models extend `CurtainCallPost` (abstract base class) which:
- Implements `Arrayable` interface
- Uses trait composition:
  - `HasAttributes`: Magic property access with validation
  - `HasMeta`: WordPress post meta handling with `ccwp_meta_*` prefix
  - `HasWordPressPost`: WP_Post object integration
- Provides factory methods: `find(id)`, `make(WP_Post)`
- Caches featured images for performance

**Relationship Traits**:
- `HasCastAndCrew`: Production → Cast/Crew (insert/update/delete)
- `HasProductions`: Cast/Crew → Production

### Frontend Build System

**Bundler**: Rollup with TypeScript (config: `rollup.config.ts`)

**Entry Points** (in `resources/js/admin/`):
- `castcrew-metaboxes.tsx` → `plugin/assets/admin/curtain-call-wp-castcrew-metaboxes.js`
- `production-metaboxes.tsx` → `plugin/assets/admin/curtain-call-wp-production-metaboxes.js`
- `sidebar.tsx` → `plugin/assets/admin/curtain-call-wp-sidebar.js`
- `archive-metaboxes.tsx` → Archive block components

**Tech Stack**:
- TypeScript for type safety
- React via @wordpress packages (element, components, etc.)
- PostCSS with custom media queries, nesting, imports
- Babel for JSX transformation
- @maskito for input masking

**Output**: Compiled to `plugin/assets/admin/` and `plugin/assets/frontend/`

### REST API

**Namespace**: `ccwp/v1`
**Controller**: `plugin/CurtainCall/Rest/RelationsController.php`

**Endpoints**:
- `GET /ccwp/v1/relations` - Query cast/crew-production relationships
- `POST /ccwp/v1/relations` - Attach cast/crew to production (upsert)
- `DELETE /ccwp/v1/relations` - Detach cast/crew from production

**Parameters**: `production_id`, `cast_and_crew_id`, `type`, `role`, `custom_order`
**Permission**: Requires `edit_posts` capability

### Gutenberg Blocks

Defined in `plugin/CurtainCall/Blocks/ArchiveBlocks.php`:

1. **ccwp/productions-archive**: Display productions in Current/Future/Past sections
2. **ccwp/cast-crew-directory**: Display all cast/crew with alphabetical index

### Template System

**Location**: `plugin/views/`

Templates use the `View` helper class (`plugin/CurtainCall/Support/View.php`):
- `View::make('admin.settings-page', $data)` - Renders PHP template with data
- `View::path('frontend.single-ccwp_production')` - Resolves template path
- Data is extracted to local scope in templates

**Admin Templates**: React mount points (metaboxes render via React)
**Frontend Templates**: PHP templates for single/archive views

### Helper Functions

Global functions in `plugin/functions.php`:
```php
ccwp_plugin_path(string $path = ''): string  // Get plugin directory path
ccwp_plugin_url(string $path = ''): string   // Get plugin URL
ccwp_strip_short_code_gallery(string $content): string
ccwp_get_custom_field(string $field_name, ?int $post_id = null): mixed
```

## Development Workflow

### Adding New Functionality

1. **Register hooks**: Add to appropriate class in `plugin/CurtainCall/Hooks/`
2. **Define meta fields**: Add to model's `$ccwp_meta` array (auto-registered)
3. **Database changes**: Update `Activator::createPluginTables()` if needed
4. **Admin UI**: Create React component in `resources/js/`, add Rollup entry
5. **Frontend display**: Add template in `plugin/views/frontend/`
6. **Settings**: Register in `GlobalHooks::addPluginSettings()`
7. **Build**: Run `npm run build` to compile assets

### Modifying Frontend Assets

1. Edit source files in `resources/js/` or `resources/styles/`
2. Run `npm run dev` for watch mode during development
3. Run `npm run build` before committing
4. Never edit files in `plugin/assets/` directly (they're overwritten)

### PHP Code Standards

- Uses Mago (Carthage Software) for linting, formatting, analysis
- Configuration: `mago.toml`
- PHP 8.1+ features allowed
- WordPress coding standards via Mago WordPress integration
- PSR-4 autoloading: `CurtainCall\` namespace → `plugin/CurtainCall/`

### TypeScript Configuration

- `tsconfig.json`: Base configuration
- `tsconfig.node.json`: Node/build scripts
- `tsconfig.web.json`: Browser/frontend code

Type checking is split to handle different environments correctly.

## Key Architectural Patterns

- **Hook-based registration**: All WordPress integration via hooks in dedicated classes
- **Trait composition**: Models use traits for relationships and meta handling
- **Factory pattern**: `Model::find()`, `Model::make()` for instantiation
- **Data Transfer Objects**: Immutable DTOs in `plugin/CurtainCall/Data/`
- **Template rendering**: View helper with data extraction
- **Magic methods**: `__get`, `__set`, `__isset` for flexible property access
- **Pivot table**: Junction table for many-to-many relationships
- **REST API**: Decoupled frontend-backend via WordPress REST API
- **Asset bundling**: Modern JS toolchain (Rollup + TypeScript + React)

## Important Notes

- **Plugin slug**: Must remain `CurtainCallWP` (folder name)
- **Text domain**: Always use `curtain-call-wp` for i18n
- **Meta prefix**: All custom fields use `ccwp_meta_` prefix
- **Post meta registration**: Defined in model's `$ccwp_meta` array, auto-registered
- **Debug mode**: Controlled by `CCWP_DEBUG` constant
- **Template hierarchy**: Plugin provides fallback templates if theme doesn't override
- **Pivot operations**: Use model relationship methods, not direct DB queries
- **Asset enqueuing**: All handled via hook classes, never directly in templates
- **TypeScript**: Avoid `any` types, prefer explicit types. Avoid type casting if at all possible.
