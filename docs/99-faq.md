# FAQ

## Why is the external API called from PHP?

The assignment requires all external requests to be made on the server side. JavaScript only communicates with WordPress through AJAX.

## Why are transients used?

Caching reduces unnecessary requests to the external API and improves page performance.

## Why is there a fallback API?

The fallback ensures that the plugin continues to work when a custom API URL is unavailable or returns invalid data.

## Why is the user limit configurable?

It allows administrators to control how much data is displayed without modifying the code.

## Why use a custom endpoint instead of a page?

The assignment specifically requires a custom WordPress endpoint that is independent of posts, pages, taxonomies, or the REST API.