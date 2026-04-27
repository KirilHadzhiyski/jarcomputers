# Admin Pricing MVP Design

**Date:** 2026-04-27

## Goal

Rebuild the current admin area into a structured internal backoffice that preserves the existing operational modules and adds a new admin-only pricing intelligence MVP based on the meeting summary document for JAR Computers.

The new module must support:

- gaming PC configuration management
- target market and VAT management
- benchmark source management
- manual competitor price entry
- scraper-ready competitor data ingestion
- pricing analysis with VAT-aware margin breakdown

The pricing module is internal only. Public visitors must not see any of its data or results.

## Source Document

This design implements the requirements from:

- `JAR_Computers_MVP_Meeting_Summary.docx`

Key business requirements taken from the document:

- focus on cross-border pricing opportunities
- initial target markets: Greece and Romania
- prices shown with and without VAT
- initial product scope: gaming PC configurations only
- primary benchmark sources: `bestprice.gr`, `Skroutz`, `eMAG`
- output: viability and pricing recommendation per configuration and market

## Existing Admin Capabilities To Preserve

The current admin already supports:

- overview dashboard
- launch readiness checks
- ticket management
- user management

These capabilities remain functional and visible after the rebuild. The new pricing module extends the admin; it does not replace these workflows.

## Selected Product Scope

The implementation will deliver two layers in one release.

### Layer A: usable internal pricing MVP

This includes:

- rebuilt admin shell and navigation
- overview dashboard with operational and pricing KPIs
- CRUD for pricing configurations
- CRUD for markets and VAT
- CRUD for benchmark sources
- CRUD for benchmark records
- analysis screen with pricing breakdown and viability result

### Layer B: scraper-ready foundation

This includes:

- a service contract for scraper ingestion
- storage for scraper-originated benchmark records
- admin-triggered sync entry point
- data structure that accepts direct active scraper prices

This release does **not** require production-grade live scraping for all sources on day one. It requires the backend and admin to be ready for it.

## Access Control

- Only authenticated admins can access the pricing module.
- Regular users cannot access routes, pages, or data from the pricing module.
- No public-facing route or blade view will expose pricing intelligence results in this version.

## Information Architecture

The admin will be restructured into a backoffice-style navigation.

### Top-level admin sections

- `Overview`
- `Pricing Configurations`
- `Markets & VAT`
- `Sources`
- `Benchmarks`
- `Analysis`
- `Tickets`
- `Users`

### Section responsibilities

#### Overview

Operational landing page for admins. It shows:

- current ticket KPIs
- user count
- launch readiness summary
- pricing KPIs such as:
  - total configurations
  - active markets
  - active sources
  - recent benchmark updates
  - viable opportunities count

#### Pricing Configurations

Management of gaming PC configurations. Each configuration acts as the base unit for all pricing analysis.

#### Markets & VAT

Management of target countries and their VAT rates. Markets must not be hardcoded in analysis logic.

#### Sources

Management of benchmark sources such as `bestprice.gr`, `Skroutz`, and `eMAG`, linked to a market.

#### Benchmarks

Management of observed competitor prices. Admins can enter them manually now, and scrapers can add active records directly later.

#### Analysis

The calculation and review surface for a configuration in a given market. This is where admin users see pricing breakdown, benchmark comparison, and viability verdict.

#### Tickets / Users

Existing modules remain available and keep their current responsibilities.

## Data Model

The pricing module introduces five core entities.

### 1. `pricing_configurations`

Represents gaming PC configurations.

Fields:

- `id`
- `name`
- `sku`
- `base_price_bgn`
- `description`
- `component_summary`
- `status`
- `notes`
- `created_by`
- `updated_by`
- timestamps

`status` values:

- `draft`
- `reviewed`
- `approved`
- `archived`

### 2. `pricing_markets`

Represents target countries.

Fields:

- `id`
- `name`
- `code`
- `currency_code`
- `vat_rate`
- `is_active`
- `notes`
- timestamps

Examples:

- Greece / `GR` / `EUR`
- Romania / `RO` / `RON` or `EUR` depending on business choice later

### 3. `pricing_sources`

Represents benchmark platforms by market.

Fields:

- `id`
- `pricing_market_id`
- `name`
- `source_key`
- `base_url`
- `input_type`
- `is_active`
- `notes`
- timestamps

`input_type` values:

- `manual`
- `scraper`
- `hybrid`

Examples:

- `bestprice.gr`
- `Skroutz`
- `eMAG`

### 4. `pricing_benchmarks`

Represents individual competitor price observations.

Fields:

- `id`
- `pricing_configuration_id`
- `pricing_market_id`
- `pricing_source_id`
- `observed_price`
- `currency_code`
- `price_includes_vat`
- `price_excluding_vat`
- `price_including_vat`
- `availability_text`
- `competitor_name`
- `product_title`
- `product_url`
- `input_method`
- `is_active`
- `collected_at`
- `created_by`
- timestamps

`input_method` values:

- `manual`
- `scraper`

Scraper records become active immediately when written. There is no approval queue in this version.

### 5. `pricing_analysis_results`

Stores calculated outputs for configuration + market combinations.

Fields:

- `id`
- `pricing_configuration_id`
- `pricing_market_id`
- `reference_benchmark_count`
- `avg_benchmark_price`
- `min_benchmark_price`
- `max_benchmark_price`
- `suggested_price_excluding_vat`
- `suggested_price_including_vat`
- `target_margin_amount`
- `target_margin_percent`
- `viability_status`
- `competition_note`
- `analysis_summary`
- `calculated_at`
- timestamps

`viability_status` values:

- `viable`
- `borderline`
- `not_viable`

## Relationships

- One configuration has many benchmarks.
- One market has many sources.
- One market has many benchmarks.
- One source has many benchmarks.
- One configuration and one market can have many historical analysis results.

## Analysis Logic

The analysis logic must live in a dedicated service layer, not inside Blade templates and not directly inside controllers.

### Inputs

- base local price in BGN from the configuration
- VAT rate of target market
- active benchmark records for the selected configuration and market
- benchmark source distribution

### Outputs

The system should produce:

- price excluding VAT
- price including VAT
- average competitor reference
- min/max competitor reference
- suggested selling price
- margin amount
- margin percentage
- viability verdict
- short narrative explanation

### Logic Principles

- Market VAT is variable per country.
- Manual and scraper benchmarks are treated as active benchmark data.
- Analysis should work even if only manual data exists.
- If benchmark coverage is too small, the UI should show a warning rather than hide results.

## Scraper Integration Design

The scraping layer is a future connector, not a required live dependency for the admin MVP.

### Required now

- a backend service interface that accepts normalized benchmark payloads
- ability to store scraper-originated benchmark data directly as active records
- admin trigger point for sync actions
- tracking of `input_method=scraper` and `collected_at`

### Not required now

- complete production scraper implementation for every source
- autonomous scheduling system
- approval workflow for scraper prices

### Sync behavior

When scraper data is written:

- it is saved as active benchmark data immediately
- it can be used by analysis immediately
- it remains distinguishable from manual entries through `input_method`

## Admin UX Design

### Shell

The admin shell should be rebuilt as a more structured backoffice. The current `admin-nav` pattern is not enough for the expanded scope.

The new shell should:

- keep the current site styling language where practical
- feel more structured and operational
- visually separate navigation, page title, filters, actions, and content panels

### Overview page

The new overview page combines:

- current operational KPI cards
- launch readiness panel
- latest tickets block
- pricing intelligence summary cards
- recent benchmark activity
- recent analysis outcomes

### List/detail patterns

For the pricing module, use list/detail or table/form layouts rather than dense card grids for everything. Admin users need scanning speed more than marketing-style presentation.

### Analysis page

The analysis view should make the business question obvious:

> Can this configuration be sold competitively in market X, and at what price?

The page should show:

- configuration summary
- market VAT summary
- benchmark records table
- computed breakdown
- verdict badge
- notes/explanation block

## Technical Architecture

### Routing

New admin routes will live under the existing admin middleware group.

Expected route groups:

- `admin.pricing.configurations.*`
- `admin.pricing.markets.*`
- `admin.pricing.sources.*`
- `admin.pricing.benchmarks.*`
- `admin.pricing.analysis.*`
- `admin.pricing.sync.*`

### Controllers

The pricing module should be split into focused controllers instead of one oversized controller.

Recommended controllers:

- `AdminPricingDashboardController`
- `AdminPricingConfigurationController`
- `AdminPricingMarketController`
- `AdminPricingSourceController`
- `AdminPricingBenchmarkController`
- `AdminPricingAnalysisController`
- `AdminPricingSyncController`

### Services

Recommended service layer:

- `PricingAnalysisService`
- `PricingBenchmarkIngestionService`
- `PricingOpportunitySummaryService`
- `PricingSyncService` or source-specific sync adapters later

### Views

The existing admin views stay, but the pricing area should use a shared admin layout structure so pages remain consistent.

Recommended new view structure:

- `resources/views/admin/pricing/overview.blade.php`
- `resources/views/admin/pricing/configurations/*`
- `resources/views/admin/pricing/markets/*`
- `resources/views/admin/pricing/sources/*`
- `resources/views/admin/pricing/benchmarks/*`
- `resources/views/admin/pricing/analysis/*`

## Testing Requirements

The implementation should include:

- migration coverage through feature tests
- admin authorization tests
- CRUD tests for new pricing entities
- analysis service tests for VAT-aware calculations
- sync ingestion tests for scraper payload handling
- dashboard rendering tests for new admin pages

## Non-Goals For This Version

These are explicitly out of scope for the first implementation:

- public display of pricing recommendations
- multi-role internal permissions beyond current admin/user split
- full autonomous scraping infrastructure for all reference markets
- advanced BI charts or forecasting models
- individual components pricing support

## Success Criteria

The work is successful when:

- the admin area is rebuilt into a clearer backoffice structure
- current `Tickets`, `Users`, and `Launch readiness` remain usable
- admins can manage gaming PC configurations, markets, sources, and benchmarks
- the system can calculate and display internal pricing viability per market
- scraper-originated benchmark records can be accepted directly as active data
- all pricing results remain admin-only
