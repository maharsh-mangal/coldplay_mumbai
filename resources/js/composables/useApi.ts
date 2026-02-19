// composables/useApi.ts
export function useApi() {
    // Helper to extract the token directly from Laravel's encrypted cookie
    const getCookie = (name: string) => {
        const match = document.cookie.match(new RegExp('(^|;\\s*)' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[2]) : '';
    };

    async function post<T>(url: string, data: Record<string, any>): Promise<T> {
        // Grab the token from the cookie instead of the DOM
        const xsrfToken = getCookie('XSRF-TOKEN');

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest', // Crucial for Laravel Auth
                'X-XSRF-TOKEN': xsrfToken,            // Note the header name change
            },
            credentials: 'same-origin',               // Ensures laravel_session cookie is sent
            body: JSON.stringify(data),
        });

        if (!response.ok) {
            // Safely attempt to parse validation or auth errors
            const error = await response.json().catch(() => ({}));
            throw new Error(error.message || `Request failed with status ${response.status}`);
        }

        return response.json();
    }

    return { post };
}
