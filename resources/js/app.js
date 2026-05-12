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
    get theme() {
        switch (this.type) {
            case "warning":
                return {
                    bg_icon: "bg-amber-100 dark:bg-amber-900/30",
                    text_icon: "text-amber-600 dark:text-amber-400",
                    progress_bg: "bg-amber-200 dark:bg-amber-900",
                    progress_fill: "bg-amber-500",
                    icon: "warning",
                    title: "Peringatan",
                };
            case "error":
                return {
                    bg_icon: "bg-red-100 dark:bg-red-900/30",
                    text_icon: "text-red-600 dark:text-red-400",
                    progress_bg: "bg-red-200 dark:bg-red-900",
                    progress_fill: "bg-red-500",
                    icon: "error",
                    title: "Gagal",
                };
            case "success":
            default:
                return {
                    bg_icon: "bg-emerald-100 dark:bg-emerald-900/30",
                    text_icon: "text-emerald-600 dark:text-emerald-400",
                    progress_bg: "bg-emerald-200 dark:bg-emerald-900",
                    progress_fill: "bg-emerald-500",
                    icon: "check_circle",
                    title: "Berhasil",
                };
        }
    },
}));

Alpine.data("chartHandler", (trendData, statusData, chartData) => ({
    trendChart: null,
    statusChart: null,
    serviceChart: null,
    entityChart: null,

    init() {
        // Render Chart saat inisialisasi
        if (trendData) this.renderTrend(trendData);
        if (statusData) this.renderStatus(statusData);
        if (chartData) {
            this.renderService(chartData);
            this.renderEntity(chartData);
        }

        // Listener untuk perubahan tema (Dark/Light) agar chart update warna grid & teks
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
                        tension: 0.4,
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
                        ticks: {
                            color: this.getTextColor(),
                            stepSize: 1,
                            precision: 0,
                            callback: function (value) {
                                return Number.isInteger(value) ? value : null;
                            },
                        },
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
                labels: ["Menunggu", "Proses", "Selesai", "Ditolak"],
                datasets: [
                    {
                        data: [
                            data.waiting,
                            data.progress,
                            data.done,
                            data.reject,
                        ],
                        backgroundColor: [
                            "#ca8a04", // WAITING
                            "#2563eb", // PROGRESS
                            "#059669", // DONE
                            "#dc2626", // REJECT
                        ],
                        hoverOffset: 4,
                        borderWidth: 0,
                    },
                ],
            },
            options: {
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

    // LAYANAN
    renderService(data) {
        const ctx = document.getElementById("serviceBarChart");
        if (!ctx) return;

        this.serviceChart = new Chart(ctx, {
            type: "bar",
            data: {
                labels: data.services_labels,
                datasets: [
                    {
                        label: "Total Tiket",
                        data: data.services_totals,
                        backgroundColor: "#8b5cf6", // Purple
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: "y", // Menjadi Horizontal Bar
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: this.getGridColor(),
                            borderDash: [5, 5],
                        },
                        ticks: { color: this.getTextColor(), stepSize: 1 },
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: this.getTextColor() },
                    },
                },
            },
        });
    },

    // --- CHART ENTITAS PENGGUNA ---
    renderEntity(data) {
        const ctx = document.getElementById("entityPieChart");
        if (!ctx) return;

        // Warna dibaca dari chartData.entity_colors yang dikirim Blade,
        // sehingga konsisten dengan dot/bar pada legend di bawah chart.
        const colors = data.entity_colors ?? [
            "#3b82f6", // Mahasiswa
            "#8b5cf6", // Dosen
            "#10b981", // Tendik
            "#14b8a6", // Karyawan
            "#fb923c", // Superuser
            "#facc15", // Tamu
            "#9ca3af", // Lainnya
        ];

        this.entityChart = new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: data.entity_labels,
                datasets: [
                    {
                        data: data.entity_totals,
                        backgroundColor: colors,
                        borderWidth: 0,
                        hoverOffset: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    // Legend dimatikan karena sudah ada legend custom di Blade
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: this.isDark ? "#1e293b" : "#ffffff",
                        titleColor: this.isDark ? "#fff" : "#0f172a",
                        bodyColor: this.isDark ? "#cbd5e1" : "#334155",
                        borderColor: this.isDark ? "#334155" : "#e2e8f0",
                        borderWidth: 1,
                    },
                },
                cutout: "60%",
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
        if (this.serviceChart) {
            this.serviceChart.options.scales.x.grid.color = this.getGridColor();
            this.serviceChart.options.scales.x.ticks.color =
                this.getTextColor();
            this.serviceChart.options.scales.y.ticks.color =
                this.getTextColor();
            this.serviceChart.update();
        }
        if (this.entityChart) {
            this.entityChart.options.plugins.legend.labels.color =
                this.getTextColor();
            this.entityChart.update();
        }
    },
}));

Alpine.data(
    "sidebarCounter",
    (initialWaiting = 0, initialAssigned = 0, fetchUrl = "") => ({
        waitingCount: initialWaiting,
        assignedProgressCount: initialAssigned,

        init() {
            if (!fetchUrl) return;

            // Lakukan polling setiap 15 detik (15000 ms)
            setInterval(() => {
                fetch(fetchUrl)
                    .then((res) => res.json())
                    .then((data) => {
                        this.waitingCount = data.waitingCount;
                        this.assignedProgressCount = data.assignedProgressCount;
                    })
                    .catch((err) =>
                        console.error("Gagal mengambil data tiket:", err),
                    );
            }, 15000);
        },
    }),
);

Alpine.start();
