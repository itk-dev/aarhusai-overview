import { Controller } from "@hotwired/stimulus";

const DEBOUNCE_MS = 200;

export default class extends Controller {
    static targets = ["input"];

    connect() {
        this._abortController = null;
        this._debounceId = null;
    }

    disconnect() {
        if (this._abortController) this._abortController.abort();
        if (this._debounceId) clearTimeout(this._debounceId);
    }

    prevent(event) {
        event.preventDefault();
        this.submit();
    }

    submit() {
        clearTimeout(this._debounceId);
        this._debounceId = setTimeout(() => this._fetch(), DEBOUNCE_MS);
    }

    async _fetch() {
        const params = new URLSearchParams(window.location.search);
        const query = this.inputTarget.value.trim();
        if (query) {
            params.set("q", query);
        } else {
            params.delete("q");
        }

        const display = params.toString()
            ? `${window.location.pathname}?${params}`
            : window.location.pathname;
        window.history.replaceState({}, "", display);

        if (this._abortController) this._abortController.abort();
        this._abortController = new AbortController();

        try {
            const response = await fetch(display, {
                signal: this._abortController.signal,
                headers: { Accept: "text/html", "X-Partial": "1" },
            });
            if (!response.ok) return;
            const html = await response.text();
            const section = document.querySelector(".index");
            if (section) section.outerHTML = html;
        } catch (error) {
            if (error.name !== "AbortError") throw error;
        }
    }
}
