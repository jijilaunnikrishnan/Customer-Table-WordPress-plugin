# Testing

The project includes unit tests for the core functionality.

The test suite verifies:

- endpoint query variable registration
- cached responses
- API requests
- transient caching
- configurable user limit
- fallback API behaviour

Brain Monkey is used to mock WordPress functions, allowing the tests to run without a complete WordPress installation.

## Commands

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