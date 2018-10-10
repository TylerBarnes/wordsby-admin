# gatsby-wordpress-admin-theme

A WordPress theme for Gatsby sites built with Advanced Custom Fields.

## What does this theme do?

- Synchronizes WordPress templates dropdown with Gatsby templates using git
- Also allows for manually specifying page template names and paths in WordPress.
- Visits a webhook on WP content updates to trigger a build.
- Fixes the ACF GraphQL error `GraphQL Error Unknown field {field} on type {type}`
- Discourages search engines from indexing the WP install
- Redirects all WP pages to the admin login
- Uses TGM plugin activation to require ACF, and other useful plugins

## For the future

- Instant page previews
- Git authorization for private gatsby repos
- Admin UI skin
