# gatsby-wordpress-admin-theme (Beta)

A WordPress theme for Gatsby sites built with Advanced Custom Fields.

## What does this theme do?

- Connects WordPress templates dropdown with Gatsby templates. Alternatively template names and paths can be specified in an options page repeater.
- Visits a webhook on WP content updates to trigger a build.
- Fixes the ACF GraphQL error `GraphQL Error Unknown field {field} on type {type}`
- Discourages search engines from indexing the WP install
- Redirects all WP pages to the admin login
- Uses TGM plugin activation to require ACF, and other useful plugins
- Page edit permalinks direct to the build site instead of the current WP site.

## Things to come

- Highly reusable frontend Gatsby starter to pair with this theme
- Documentation
- Instant page previews
- Reorganized admin menu
- Git authorization for private gatsby repos
- Hashed webhook
- More intuitive gatsby clone process
- Admin UI skin
