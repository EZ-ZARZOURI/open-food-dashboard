import { Application } from '@hotwired/stimulus';
import ProductWidgetController from './controllers/product_widget_controller.js';

const app = Application.start();
app.register('product-widget', ProductWidgetController);
