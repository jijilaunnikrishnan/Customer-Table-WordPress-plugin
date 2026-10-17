# Syde Users Table

A WordPress plugin that displays users from an external API through a custom frontend endpoint.

The plugin performs all external HTTP requests on the server using the WordPress HTTP API and enhances the frontend experience by loading user details asynchronously with AJAX.

---

## Features

- Custom WordPress endpoint (`/syde-users-table/`)
- Server-side HTTP requests using the WordPress HTTP API
- AJAX-powered user details
- WordPress transient caching
- Configurable API URL
- Configurable user limit
- Automatic fallback to the default JSONPlaceholder API
- Responsive frontend layout
- Unit tests
- PHPCS compliant

---

## Requirements

- PHP 8.1 or later
- WordPress 6.x
- Composer
- Docker (recommended for running the development commands)

---

## Installation

1. Clone the repository.

```bash
git clone <repository-url>
```

2. Install the Composer dependencies.

```bash
composer install
```

3. Activate the plugin from the WordPress administration panel.

4. Save the permalink settings once to register the custom rewrite endpoint.

---

## Usage

Visit:

```
/syde-users-table/
```

The page displays users retrieved from the configured API.

Clicking a user ID, name, or username loads additional information without refreshing the page.

Plugin settings are available under:

```
Settings → Syde Users Table
```

The administrator can configure:

- API base URL
- Maximum number of displayed users

---

## Development

Run all checks:

```bash
composer check
```

Run unit tests:

```bash
composer test
```

Run coding standards:

```bash
composer cs
```

---

## Project Structure

```
src/
    Admin/
    Ajax/
    Api/
    Frontend/

resources/
    css/
    js/

templates/
tests/
docs/
```

---

## Design Decisions

### Server-side API requests

The external API is accessed from PHP using the WordPress HTTP API. JavaScript communicates only with WordPress AJAX.

### Caching

Successful responses are cached using WordPress Transients for 15 minutes to reduce unnecessary HTTP requests.

### API fallback

If the configured API URL is unavailable, returns an error, or returns invalid JSON, the plugin automatically retries using the default JSONPlaceholder API.

### Configurable settings

Administrators can configure:

- API base URL
- User limit

without modifying the plugin code.

---

## Documentation

Additional documentation is available in the `docs/` directory.

---

## License

GPL-2.0-or-later

## Further Reading

- `docs/01-introduction.md`
- `docs/02-implementation.md`
- `docs/03-testing.md`
- `docs/99-faq.md`