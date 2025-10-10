gitattributes, protected branches, validation, phpstan, linters. lint-staged. Protected files?

## General Packages
0) Before / After
1) Symfony Router Adapter + #[Route] attribute
2) Symfony Validator Adapter + #[Validate] attribute
3) Config loader
4) Open API + GUZZLE?
5) ORM and custom tables
6) AOP?
7) WP Logger (CLI + Memory + File + DB)?

## WordPress packages
1) WP Node API (as CRUD wrapper) https://github.com/johnbillion/extended-cpts
2) WP plugin boilerplate 
3) CRON wrapper (+ true CRON) https://github.com/humanmade/Cavalcade
4) Db wrapper. Different drawers (definitely Mongo, MySQL + Postgress / Maria, Microsoft SQL Server - TBD); vector DB for assets - TBD. Doctrine. Migrations. Hyper DB (master/slave)?
5) CLI Wrapper. Symfony CLI + WP CLI. wp-cli-regex
6) Open API + Guzzle + WP REST Rest caching

## WordPress plugins (definitely)
1) Core plugin
    * Set default theme (custom)
    * Editor type management for each
    * Feature flags
    * 
2) Taxonomy page optimization (additional fields + image + Video, Gutenberg zone (other plugin?)), TBD
3) Auto-logout (?)
4) Wordfence principles
5) Table content (by headings)
6) Groups for users (Groups plugin?)
7) Graphql API extend
8) Network connections as an idea
9) Network of networks + status table
10) Network Security settings (extensions, limits, banning - users, domains, etc)
11) Gravity Forms
12) Dropins. 
13) SEO (Yoast? Rank Math? TBD)
14) URL Automatic map (like Drupal)
15) Custom meta plugin (and tables?)
16) Network of network filesystem access, db access... TBD
17) Domain mapping (for multisite)
18) Different databases for Network
19) Mongo for Gutenberg, Apollo * React Query, rxjs  for Gutenberg; Gutenberg boilerplate plugin. Elastic for search. Import / Export
20) Perks + Quick Edit perks, Formats, Featured, Advanced Search (by ID also)
21) PublishPress (?) Autopublishment
22) Media management plugin (3d-party video like Vistia, Youtube - TBD). Media cropping optimize (on the fly by cron and store in cloud)
23) Admin pages speed optimization plugin (with Elastic?)
24) https://docs.wpvip.com/vip-go-mu-plugins/
25) MU Filter by active themes and plugins

## Optimization packages (TBD)
1) Webpp optimization + analogue for video
2) Database optimization... By CRON?
3) Fork https://github.com/humanmade/hm-the-cached-content
4) True Node.js microserver for true SSR https://github.com/humanmade/react-wp-ssr

## Access
1) User system auto-access
2) User system temporary access
3) https://github.com/humanmade/Security-White-Paper
4) http://github.com/humanmade/two-factor
5) https://ru.wordpress.org/plugins/wp-health/
6) Web minifest

## Other
1) ADA Tests ?
2) Elasticsearch adapter + implementation
3) md validation (?)
4) Tina CMS Bridge
5) AI bots integrations (seo?) https://github.com/humanmade/ai-plugin
6) https://github.com/humanmade/remote-admin-bar
7) https://github.com/humanmade/shared-media-library
8) https://github.com/humanmade/wp-icon-picker
9) Slots https://github.com/humanmade/react-slot-fill?tab=readme-ov-file and https://developer.wordpress.org/block-editor/reference-guides/components/slot-fill/
10) Own gRPC server and gateway?
11) https://wordpress.stackexchange.com/questions/251116/how-to-use-wordpress-multisite-with-different-domain-names, https://florianbrinkmann.com/en/wordpress-multisite-mix-of-subdomains-and-subdirectories-3446/
12) Output Buffer (security) https://www.opensourceprojects.dev/post/1947505385260056911