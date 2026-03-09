import { Controller } from '@hotwired/stimulus';

const ACTIVE = ['bg-white', 'shadow', 'text-indigo-600', 'border-b-2', 'border-indigo-500'];
const INACTIVE = ['text-slate-500', 'border-b-2', 'border-transparent'];

export default class extends Controller {
    static targets = ['btn', 'panel'];

    select(event) {
        const index = this.btnTargets.indexOf(event.currentTarget);

        this.btnTargets.forEach((btn, i) => {
            const isActive = i === index;
            btn.setAttribute('aria-selected', isActive);

            if (isActive) {
                btn.classList.add(...ACTIVE);
                btn.classList.remove(...INACTIVE);
            } else {
                btn.classList.remove(...ACTIVE);
                btn.classList.add(...INACTIVE);
            }
        });

        this.panelTargets.forEach((panel, i) => {
            panel.style.display = i === index ? '' : 'none';
        });
    }
}
