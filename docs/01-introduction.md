# Syde Users Table

Syde Users Table is a WordPress plugin that displays users from an external API through a custom frontend endpoint.

The plugin performs all external HTTP requests on the server using the WordPress HTTP API. Visitors can browse the users table and load additional information for each user without refreshing the page.

The plugin also provides a settings page where administrators can configure:

- the API base URL
- the maximum number of displayed users

When the configured API is unavailable or returns an invalid response, the plugin automatically falls back to the default JSONPlaceholder API to keep the frontend working.

## Features

- Custom WordPress endpoint
- Server-side API requests
- AJAX user details
- WordPress transient caching
- Configurable API URL
- Configurable user limit
- Responsive frontend
- Unit tests
- PHPCS compliant