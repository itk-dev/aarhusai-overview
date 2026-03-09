import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['content', 'label'];

    toggle() {
        const el = this.contentTarget;
        const isHidden = el.style.display === 'none';
        el.style.display = isHidden ? '' : 'none';

        if (this.hasLabelTarget) {
            const label = this.labelTarget;
            const swap = label.dataset.swap;
            label.dataset.swap = label.textContent;
            label.textContent = swap;
        }
    }
}
