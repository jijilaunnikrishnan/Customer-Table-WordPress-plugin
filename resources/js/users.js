/**
 * Load user details through AJAX when a user is selected.
 */
document.addEventListener('click', async (event) => {
    // Check whether a user link was clicked.
    const link = event.target.closest('[data-user-id]');

    if (!link) {
        return;
    }

    event.preventDefault();

    const details = document.getElementById('syde-user-details');

    if (!details) {
        return;
    }

    // Show a loading message while the request is in progress.
    details.setAttribute('aria-busy', 'true');
    details.innerHTML = '<p>Loading user details…</p>';

    const body = new URLSearchParams({
        action: 'syde_user_details',
        nonce: sydeUsersTable.nonce,
        user_id: link.dataset.userId
    });

    try {
        // Request the selected user's details from WordPress.
        const response = await fetch(sydeUsersTable.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type':
                    'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: body.toString()
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result?.data?.message || 'Unable to load user details.'
            );
        }

        // Display the HTML returned by the AJAX action.
        details.innerHTML = result.data.html;
    } catch (error) {
        // Show an error message if the request fails.
        details.textContent =
            error instanceof Error
                ? error.message
                : 'Unable to load user details.';
    } finally {
        // Mark the request as complete.
        details.setAttribute('aria-busy', 'false');
    }
});