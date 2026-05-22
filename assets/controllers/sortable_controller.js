import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["header", "body", "status"];

    connect() {
        if (this.headerTargets.length === 0 || this.bodyTargets.length === 0) {
            return;
        }

        const params = new URLSearchParams(window.location.search);
        const sortParam = params.get("sort");
        const dirParam = params.get("dir");

        let target = null;
        if (sortParam) {
            target = this.headerTargets.find(
                (h) => this._slug(h.dataset.sortLabel) === sortParam,
            );
        }
        if (!target) {
            target = this.headerTargets[0];
        }

        const ascending = dirParam ? dirParam !== "desc" : true;
        this._applySort(target, ascending);
    }

    sort(event) {
        const th = event.currentTarget;
        const ascending = th.dataset.sortDir !== "asc";
        this._applySort(th, ascending);
        this._writeUrl(th, ascending);
    }

    _applySort(th, ascending) {
        const index = Array.from(th.parentElement.children).indexOf(th);

        this.headerTargets.forEach((h) => {
            h.dataset.sortDir = "";
            const arrow = h.querySelector("[data-sort-arrow]");
            if (arrow) arrow.textContent = "↕";
        });

        th.dataset.sortDir = ascending ? "asc" : "desc";
        const arrow = th.querySelector("[data-sort-arrow]");
        if (arrow) arrow.textContent = ascending ? "↑" : "↓";

        this._sortByColumn(index, ascending);
        this._updateStatus(th, ascending);
    }

    _writeUrl(th, ascending) {
        const sort = this._slug(th.dataset.sortLabel);
        const dir = ascending ? "asc" : "desc";

        const params = new URLSearchParams(window.location.search);
        params.set("sort", sort);
        params.set("dir", dir);
        const url = `${window.location.pathname}?${params.toString()}`;
        window.history.replaceState({}, "", url);

        document.querySelectorAll("[data-sort-link]").forEach((link) => {
            const linkUrl = new URL(
                link.getAttribute("href"),
                window.location.origin,
            );
            linkUrl.searchParams.set("sort", sort);
            linkUrl.searchParams.set("dir", dir);
            link.setAttribute(
                "href",
                `${linkUrl.pathname}${linkUrl.search}`,
            );
        });
    }

    _slug(text) {
        return (text || "")
            .trim()
            .toLowerCase()
            .replace(/\s+/g, "-");
    }

    _updateStatus(th, ascending) {
        if (!this.hasStatusTarget) return;
        const direction = ascending ? "A→Z" : "Z→A";
        const column = (th.dataset.sortLabel || th.textContent || "")
            .replace(/[↑↓↕]/g, "")
            .trim();
        this.statusTarget.textContent = column
            ? `Sorted by ${column} · ${direction}`
            : `Sorted ${direction}`;
    }

    _sortByColumn(index, ascending) {
        const bodies = this.bodyTargets;
        const collator = new Intl.Collator(undefined, {
            numeric: true,
            sensitivity: "base",
        });
        const sorted = [...bodies].sort((a, b) => {
            const aText = a
                .querySelector("tr td:nth-child(" + (index + 1) + ")")
                .textContent.trim();
            const bText = b
                .querySelector("tr td:nth-child(" + (index + 1) + ")")
                .textContent.trim();
            return ascending
                ? collator.compare(aText, bText)
                : collator.compare(bText, aText);
        });

        const table = bodies[0].parentElement;
        sorted.forEach((body) => table.appendChild(body));
    }
}
