import "./bootstrap";
import Alpine from "alpinejs";
import "trix";
import { initTrixAttachmentUpload } from "./trix-upload";
import Chart from "chart.js/auto";

initTrixAttachmentUpload();

Alpine.data("toast", (initialMessage = "", initialType = "success") => ({
    visible: false,
    message: initialMessage,
    type: initialType,
    progress: 100,
    duration: 4000,
    interval: null,

    init() {
        if (this.message) this.show();
        window.addEventListener("notify", (event) => {
            this.message = event.detail.message;
            this.type = event.detail.type || "success";
            this.show();
        });
    },
    show() {
        this.visible = true;
        this.progress = 100;
        if (this.interval) clearInterval(this.interval);
        const step = 100 / (this.duration / 50);
        this.interval = setInterval(() => {
            this.progress -= step;
            if (this.progress <= 0) this.close();
        }, 50);
    },
    close() {
        this.visible = false;
        clearInterval(this.interval);
    },
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
                  icon: "error",
                  title: "Gagal",
              };
    },
}));

Alpine.data("chartHandler", (trendData, statusData) => ({
    trendChart: null,
    statusChart: null,

    init() {
        // Render Chart saat inisialisasi
        this.renderTrend(trendData);
        this.renderStatus(statusData);

        // Opsional: Listener untuk perubahan tema (Dark/Light) agar chart update warna grid
        // Asumsi Anda menggunakan class 'dark' di html tag
        const observer = new MutationObserver(() => {
            this.updateChartsTheme();
        });
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ["class"],
        });
    },

    get isDark() {
        return document.documentElement.classList.contains("dark");
    },

    getGridColor() {
        return this.isDark ? "#334155" : "#e2e8f0"; // Slate-700 vs Slate-200
    },

    getTextColor() {
        return this.isDark ? "#cbd5e1" : "#475569"; // Slate-300 vs Slate-600
    },

    renderTrend(data) {
        const ctx = document.getElementById("trendChart");
        if (!ctx) return;

        const labels = Object.keys(data);
        const values = Object.values(data);

        this.trendChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Tiket Masuk",
                        data: values,
                        borderColor: "#3b82f6", // Primary Blue
                        backgroundColor: (context) => {
                            const bg = context.chart.ctx.createLinearGradient(
                                0,
                                0,
                                0,
                                300,
                            );
                            bg.addColorStop(0, "rgba(59, 130, 246, 0.5)");
                            bg.addColorStop(1, "rgba(59, 130, 246, 0.0)");
                            return bg;
                        },
                        borderWidth: 3,
                        tension: 0.4, // Smooth curve
                        fill: true,
                        pointBackgroundColor: "#ffffff",
                        pointBorderColor: "#3b82f6",
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: "index",
                        intersect: false,
                        backgroundColor: this.isDark ? "#1e293b" : "#ffffff",
                        titleColor: this.isDark ? "#fff" : "#0f172a",
                        bodyColor: this.isDark ? "#cbd5e1" : "#334155",
                        borderColor: this.isDark ? "#334155" : "#e2e8f0",
                        borderWidth: 1,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: this.getGridColor(),
                            borderDash: [5, 5],
                        },
                        ticks: { color: this.getTextColor() },
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: this.getTextColor() },
                    },
                },
                interaction: {
                    mode: "nearest",
                    axis: "x",
                    intersect: false,
                },
            },
        });
    },

    renderStatus(data) {
        const ctx = document.getElementById("statusChart");
        if (!ctx) return;

        this.statusChart = new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: ["Menunggu", "Proses", "Selesai", "Ditolak"], // Label Bahasa Indonesia
                datasets: [
                    {
                        data: [
                            data.waiting,
                            data.progress,
                            data.done,
                            data.reject,
                        ],
                        backgroundColor: [
                            "#ca8a04", // WAITING  (Yellow-600)
                            "#2563eb", // PROGRESS (Blue-600)
                            "#059669", // DONE     (Emerald-600)
                            "#dc2626", // REJECT   (Red-600)
                        ],
                        hoverOffset: 4,
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                // ... opsi lainnya sama ...
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            color: this.getTextColor(),
                            usePointStyle: true,
                            padding: 20,
                            font: { family: "'Inter Variable', sans-serif" },
                        },
                    },
                },
                cutout: "75%",
            },
        });
    },

    updateChartsTheme() {
        if (this.trendChart) {
            this.trendChart.options.scales.y.grid.color = this.getGridColor();
            this.trendChart.options.scales.y.ticks.color = this.getTextColor();
            this.trendChart.options.scales.x.ticks.color = this.getTextColor();
            this.trendChart.update();
        }
        if (this.statusChart) {
            this.statusChart.options.plugins.legend.labels.color =
                this.getTextColor();
            this.statusChart.update();
        }
    },
}));

Alpine.start();
