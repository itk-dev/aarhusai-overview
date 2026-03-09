import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['btn', 'panel'];

    select(event) {
        const index = this.btnTargets.indexOf(event.currentTarget);

        this.btnTargets.forEach((btn, i) => {
            if (i === index) {
                btn.classList.remove('border-transparent', 'text-gray-500');
                btn.classList.add('border-indigo-500', 'text-indigo-600');
            } else {
                btn.classList.remove('border-indigo-500', 'text-indigo-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            }
        });

        this.panelTargets.forEach((panel, i) => {
            panel.style.display = i === index ? '' : 'none';
        });
    }
}
