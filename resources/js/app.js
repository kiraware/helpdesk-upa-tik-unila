import "./bootstrap";
import Alpine from "alpinejs";
import "trix";
import { initTrixAttachmentUpload } from "./trix-upload";

initTrixAttachmentUpload();

Alpine.data("toast", () => ({
    visible: false,
    progress: 100,
    duration: 4000,
    interval: null,

    init() {
        this.show();
    },

    show() {
        this.visible = true;
        this.progress = 100;

        const step = 100 / (this.duration / 50);

        this.interval = setInterval(() => {
            this.progress -= step;
            if (this.progress <= 0) {
                this.close();
            }
        }, 50);
    },

    close() {
        this.visible = false;
        clearInterval(this.interval);
    },
}));

window.Alpine = Alpine;
Alpine.start();
