import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["content", "label", "trigger"];

    toggle(event) {
        const el = this.contentTarget;
        const isHidden = el.style.display === "none";
        el.style.display = isHidden ? "" : "none";

        const trigger = this.hasTriggerTarget
            ? this.triggerTarget
            : event?.currentTarget;
        if (trigger) {
            trigger.setAttribute("aria-expanded", isHidden ? "true" : "false");
        }

        if (this.hasLabelTarget) {
            const label = this.labelTarget;
            const swap = label.dataset.swap;
            label.dataset.swap = label.textContent;
            label.textContent = swap;
        }
    }
}
