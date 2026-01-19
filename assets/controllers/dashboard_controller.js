import { Controller } from "@hotwired/stimulus"
import Sortable from "sortablejs"

export default class extends Controller {
  connect() {
    this.sortable = Sortable.create(this.element, {
      animation: 150,
      handle: ".widget-card",
      onEnd: (evt) => this.saveOrder(evt)
    })
  }

  saveOrder(evt) {
    const order = Array.from(this.element.children).map(
      (el, index) => ({ id: el.dataset.widgetId, position: index })
    )

    fetch("/dashboard/widgets/order", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(order)
    })
  }

  editWidget(event) {
    const widgetId = event.currentTarget.closest("[data-widget-id]").dataset.widgetId
    alert("Modifier widget " + widgetId)
    // Implémente la modal / formulaire de modification
  }

  deleteWidget(event) {
    const widgetId = event.currentTarget.closest("[data-widget-id]").dataset.widgetId
    alert("Supprimer widget " + widgetId)
    // Implémente la suppression via fetch/AJAX
  }
}
