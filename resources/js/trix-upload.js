export function initTrixAttachmentUpload() {
    // 1. Event saat file hendak dimasukkan (Validasi Frontend)
    addEventListener("trix-file-accept", function (event) {
        const editor = event.target;

        // Ambil konfigurasi dari data attributes
        // Default ke 2MB jika tidak ada setting
        const maxFileSize = (editor.dataset.maxSize || 2048) * 1024; // Convert KB to Bytes
        const allowedTypes = (editor.dataset.accept || "*").split(",");

        const file = event.file;

        // A. Cek Ukuran File
        if (file.size > maxFileSize) {
            event.preventDefault();
            const sizeInMB = editor.dataset.maxSize / 1024;
            triggerToast(
                `Ukuran file terlalu besar. Maksimal ${sizeInMB}MB.`,
                "error",
            );
            return;
        }

        // B. Cek Tipe File (Jika tidak wildcard *)
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

    // 2. Event saat file mulai diupload
    addEventListener("trix-attachment-add", function (event) {
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
