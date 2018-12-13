# Wordsby Admin (WIP)

A WordPress theme to make Gatsby development and content creation easier.
This theme is 100% intended to be used with my Gatsby plugin Wordsby.
Check out the Wordsby readme for more info.

## What does this theme do?

- Tighter integration with Gatsby
- Instant page previews from page/post edit screens with live rest api data.
- Built in rest api endpoints tailored for Gatsby
- Built in ACF support
- Connects WordPress template picker to Gatsby templates.
- Schema Builder post type so your gatsby builds don't fail when a client deletes all posts or flexible content sections
- Adds the ability to use WP functionality in gatsby using the [PsychicWindow](https://github.com/TylerBarnes/PsychicWindow) component. Useful for displaying a contact form or WP gated content.
- BetterAdmin UI skin / reorganized admin menu
- Triggers a remote CI build on content updates via webhooks.
- Fixes the ACF GraphQL error `GraphQL Error Unknown field {field} on type {type}`
- Discourages search engines from indexing the WP install
- Redirects all WP pages to the admin login
- Uses TGM plugin activation to require mandatory plugins, and other useful plugins
- Admin edit page links go to the build site instead of the current WP site.
- Users get avatars downloaded from unsplash automatically.
