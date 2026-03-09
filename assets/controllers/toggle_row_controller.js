import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['content'];

    toggle() {
        const el = this.contentTarget;
        el.style.display = el.style.display === 'none' ? '' : 'none';
    }
}
