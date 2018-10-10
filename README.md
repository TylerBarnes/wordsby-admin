# gatsby-wordpress-admin-theme

A WordPress theme for Gatsby sites built with Advanced Custom Fields.

## What does this theme do?

- Synchronizes the page/post edit page template dropdown with the Gatsby filesystem templates
- Allows for manually specifying page template names and paths.
- Runs a webhook on content updates to trigger a build.
- Fixes the ACF GraphQL error `GraphQL Error Unknown field {field} on type {type}`
- Discourages search engines from indexing the WP install
- Redirects all WP pages to the admin login
- Uses TGM plugin activation to require ACF, and other useful plugins

## For the future

- Easy sync of WP templates and Gatsby templates to allow template switching in WP
- Instant page previews
- Admin UI skin
