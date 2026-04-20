export function initTrixAttachmentUpload() {
    // Ambil alih tombol Attach agar menggunakan File Input buatan kita
    document.addEventListener("trix-initialize", function (event) {
        const editorElement = event.target;
        const toolbar = editorElement.toolbarElement;
        if (!toolbar) return;

        // Cari tombol attachment bawaan Trix
        const attachBtn = toolbar.querySelector(
            "[data-trix-action='attachFiles']",
        );
        const accept = editorElement.dataset.accept;

        if (attachBtn && accept) {
            // Hapus aksi default Trix agar tidak membuka explorer bawaannya yang tidak terfilter
            attachBtn.removeAttribute("data-trix-action");

            // Buat elemen input file tersembunyi kita sendiri yang memiliki filter 'accept'
            const customFileInput = document.createElement("input");
            customFileInput.type = "file";
            customFileInput.multiple = true; // Izinkan pilih banyak file sekaligus
            customFileInput.accept = accept;
            customFileInput.style.display = "none";

            // Sisipkan ke dalam editor agar tersimpan di DOM
            editorElement.appendChild(customFileInput);

            // Sambungkan klik tombol Trix ke input file custom kita
            attachBtn.addEventListener("click", function (e) {
                e.preventDefault();
                customFileInput.click();
            });

            // Tangkap file yang dipilih user dari jendela explorer
            customFileInput.addEventListener("change", function () {
                const files = customFileInput.files;
                if (files && files.length > 0) {
                    Array.from(files).forEach((file) => {
                        // Masukkan file ke Trix secara programatis.
                        // Langkah ini akan otomatis memicu validasi 'trix-file-accept' di bawah.
                        editorElement.editor.insertFile(file);
                    });
                }
                // Reset input value agar file yang sama bisa dipilih ulang jika dihapus
                customFileInput.value = "";
            });
        }
    });

    // Event saat file hendak dimasukkan (Validasi saat Drag & Drop atau File Terpilih)
    document.addEventListener("trix-file-accept", function (event) {
        const editor = event.target;

        const maxFileSize = (editor.dataset.maxSize || 2048) * 1024;
        const allowedTypes = (editor.dataset.accept || "*").split(",");
        const file = event.file;

        // Cek Ukuran File
        if (file.size > maxFileSize) {
            event.preventDefault();
            const sizeInMB = editor.dataset.maxSize / 1024;
            triggerToast(
                `Ukuran file terlalu besar. Maksimal ${sizeInMB}MB.`,
                "error",
            );
            return;
        }

        // Cek Tipe File
        if (editor.dataset.accept && editor.dataset.accept !== "*") {
            if (!allowedTypes.includes(file.type)) {
                event.preventDefault();
                triggerToast(
                    `Format file tidak didukung. Hanya diperbolehkan: ${getReadableExtensions(allowedTypes)}`,
                    "error",
                );
                return;
            }
        }
    });

    // Event saat file mulai diupload
    document.addEventListener("trix-attachment-add", function (event) {
        if (event.attachment.file) {
            uploadFileAttachment(event.attachment);
        }
    });
}

// Helper Function untuk memicu Toast
function triggerToast(message, type = "success") {
    window.dispatchEvent(
        new CustomEvent("notify", {
            detail: {
                message: message,
                type: type,
            },
        }),
    );
}

// Helper untuk mengubah mime type jadi tulisan ekstensi yang enak dibaca
function getReadableExtensions(mimeTypes) {
    const map = {
        "image/jpeg": "JPG",
        "image/png": "PNG",
        "application/pdf": "PDF",
        "application/msword": "DOC",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
            "DOCX",
        "application/zip": "ZIP",
    };
    return mimeTypes.map((type) => map[type] || type).join(", ");
}

function uploadFileAttachment(attachment) {
    uploadFile(attachment.file, setProgress, setAttributes);

    function setProgress(progress) {
        attachment.setUploadProgress(progress);
    }

    function setAttributes(attributes) {
        attachment.setAttributes(attributes);
    }
}

function uploadFile(file, progressCallback, successCallback) {
    const editorElement = document.querySelector("trix-editor");
    if (!editorElement) return;

    const uploadUrl = editorElement.dataset.uploadUrl;
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    if (!uploadUrl) return;

    var formData = new FormData();
    formData.append("file", file);

    var xhr = new XMLHttpRequest();
    xhr.open("POST", uploadUrl, true);
    xhr.setRequestHeader("X-CSRF-TOKEN", csrfToken);

    xhr.upload.addEventListener("progress", function (event) {
        var progress = (event.loaded / event.total) * 100;
        progressCallback(progress);
    });

    xhr.addEventListener("load", function (event) {
        if (xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            successCallback({
                url: response.url,
                href: response.url,
            });
        } else {
            // Handle error dari server (misal lolos JS tapi gagal di PHP)
            console.error("Upload failed", xhr.responseText);
            alert("Gagal mengunggah file. Pastikan format dan ukuran sesuai.");
        }
    });

    xhr.send(formData);
}
