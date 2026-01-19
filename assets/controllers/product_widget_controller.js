import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["details"]

    toggleDetails() {
        this.detailsTarget.classList.toggle("hidden");
    }
}


