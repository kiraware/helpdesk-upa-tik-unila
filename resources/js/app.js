import "./bootstrap";
import Alpine from "alpinejs";
import "trix";
import { initTrixAttachmentUpload } from "./trix-upload";

initTrixAttachmentUpload();

Alpine.data("toast", (initialMessage = "", initialType = "success") => ({
    visible: false,
    message: initialMessage,
    type: initialType, // 'success' or 'error'
    progress: 100,
    duration: 4000,
    interval: null,

    init() {
        // 1. Jika ada pesan bawaan dari Server (Session), tampilkan
        if (this.message) {
            this.show();
        }

        // 2. Event Listener untuk Trigger dari Javascript Lain (Trix, dll)
        window.addEventListener("notify", (event) => {
            this.message = event.detail.message;
            this.type = event.detail.type || "success";
            this.show();
        });
    },

    show() {
        this.visible = true;
        this.progress = 100;

        // Clear interval lama jika ada (kasus spam klik)
        if (this.interval) clearInterval(this.interval);

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

    // 3. Getter untuk Styling Dinamis (Pengganti Logika PHP)
    get isSuccess() {
        return this.type === "success";
    },

    get theme() {
        return this.isSuccess
            ? {
                  bg_icon: "bg-emerald-100 dark:bg-emerald-900/30",
                  text_icon: "text-emerald-600 dark:text-emerald-400",
                  progress_bg: "bg-emerald-200 dark:bg-emerald-900",
                  progress_fill: "bg-emerald-500",
                  icon: "check_circle",
                  title: "Berhasil",
              }
            : {
                  bg_icon: "bg-red-100 dark:bg-red-900/30",
                  text_icon: "text-red-600 dark:text-red-400",
                  progress_bg: "bg-red-200 dark:bg-red-900",
                  progress_fill: "bg-red-500",
                  icon: "error_outline",
                  title: "Gagal",
              };
    },
}));

window.Alpine = Alpine;
Alpine.start();
