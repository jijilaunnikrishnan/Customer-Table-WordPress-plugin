# Implementation

The plugin is divided into small classes, each with a single responsibility.

## Plugin

Bootstraps the plugin and connects all components.

## Endpoint

Registers the custom frontend endpoint and renders the users page.

## UsersClient

Communicates with the external API.

Responsibilities include:

- requesting users
- requesting a single user
- transient caching
- fallback to the default API
- applying the configured user limit

## UserDetailsAction

Handles AJAX requests and returns HTML fragments for the selected user.

## Settings

Creates the administration page and stores the plugin configuration.

## Frontend

The users table is rendered by PHP.

JavaScript enhances the page by loading user details asynchronously without refreshing the page.