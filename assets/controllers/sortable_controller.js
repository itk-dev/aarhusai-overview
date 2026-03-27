import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["header", "body"];

    connect() {
        // Sort ascending by the first header on load
        if (this.headerTargets.length > 0 && this.bodyTargets.length > 0) {
            this.headerTargets[0].dataset.sortDir = "asc";
            const arrow =
                this.headerTargets[0].querySelector("[data-sort-arrow]");
            if (arrow) arrow.textContent = "\u2191";
            this._sortByColumn(0, true);
        }
    }

    sort(event) {
        const th = event.currentTarget;
        const index = Array.from(th.parentElement.children).indexOf(th);
        const ascending = th.dataset.sortDir !== "asc";

        // Reset all headers
        this.headerTargets.forEach((h) => {
            h.dataset.sortDir = "";
            const arrow = h.querySelector("[data-sort-arrow]");
            if (arrow) arrow.textContent = "\u2195";
        });

        th.dataset.sortDir = ascending ? "asc" : "desc";
        const arrow = th.querySelector("[data-sort-arrow]");
        if (arrow) arrow.textContent = ascending ? "\u2191" : "\u2193";

        this._sortByColumn(index, ascending);
    }

    _sortByColumn(index, ascending) {
        const bodies = this.bodyTargets;
        const sorted = [...bodies].sort((a, b) => {
            const aText = a
                .querySelector("tr td:nth-child(" + (index + 1) + ")")
                .textContent.trim()
                .toLowerCase();
            const bText = b
                .querySelector("tr td:nth-child(" + (index + 1) + ")")
                .textContent.trim()
                .toLowerCase();
            return ascending
                ? aText.localeCompare(bText)
                : bText.localeCompare(aText);
        });

        const table = bodies[0].parentElement;
        sorted.forEach((body) => table.appendChild(body));
    }
}
