# GatsbyPress Admin (Pre-alpha)

A WordPress theme to make Gatsby development and content creation easier.

## What does this theme do?
- Adds the ability to use WP functionality in gatsby using the [PsychicWindow](https://github.com/TylerBarnes/PsychicWindow) component. Useful for displaying a contact form or WP gated content.
- Connects WordPress templates dropdown with Gatsby templates. Alternatively template names and paths can be specified in an options page repeater.
- Visits a webhook on WP content updates to trigger a build.
- Fixes the ACF GraphQL error `GraphQL Error Unknown field {field} on type {type}`
- Discourages search engines from indexing the WP install
- Redirects all WP pages to the admin login
- Uses TGM plugin activation to require ACF, and other useful plugins
- Admin edit page links go to the build site instead of the current WP site.

## Things to come

- Highly reusable frontend Gatsby starter to pair with this theme
- Documentation
- Instant page previews from page/post edit screens
- Reorganized admin menu
- Git authorization for private gatsby repos
- Hashed webhook
- More intuitive gatsby clone process
- Admin UI skin
- Simple admin UI customizer for tailoring your backend to the project
