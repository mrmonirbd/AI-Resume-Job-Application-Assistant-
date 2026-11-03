export default function applicationBoard(applications, csrfToken) {
    return {
        applications,
        dragging: null,
        over: null,
        pending: null,
        message: '',
        error: '',

        inStage(stage) {
            return this.applications.filter((application) => application.status === stage);
        },

        async move(id, stage) {
            this.dragging = null;
            this.over = null;
            const application = this.applications.find((item) => item.id === id);
            if (!application || this.pending !== null || application.status === stage) return;

            this.pending = id;
            this.error = '';
            this.message = '';
            try {
                const response = await fetch(application.moveUrl, {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ status: stage }),
                });
                if (!response.ok) {
                    if (response.status === 419 || response.status === 401) {
                        throw new Error('Your session has expired. Refresh the page and sign in again.');
                    }
                    throw new Error('Could not move the application. Please try again.');
                }
                const result = await response.json();
                if (result.id !== id || result.status !== stage) {
                    throw new Error('Could not confirm the update. Please refresh the board.');
                }
                application.status = result.status;
                this.message = `${application.company} moved to ${stage}.`;
            } catch (error) {
                this.error = error.message === 'Failed to fetch'
                    ? 'Connection lost. Check your internet and try again.'
                    : error.message;
            } finally {
                this.pending = null;
            }
        },
    };
}
