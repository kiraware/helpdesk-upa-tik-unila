export function initTrixAttachmentUpload() {
    document.addEventListener("trix-initialize", function (event) {
        const editorElement = event.target;
        const toolbar = editorElement.toolbarElement;
        if (!toolbar) return;

        const attachBtn = toolbar.querySelector(
            "[data-trix-action='attachFiles']",
        );
        const accept = editorElement.dataset.accept;

        if (attachBtn && accept) {
            attachBtn.removeAttribute("data-trix-action");

            const customFileInput = document.createElement("input");
            customFileInput.type = "file";
            customFileInput.multiple = true; // Izinkan pilih banyak file sekaligus
            customFileInput.accept = accept;
            customFileInput.style.display = "none";

            editorElement.appendChild(customFileInput);

            attachBtn.addEventListener("click", function (e) {
                e.preventDefault();
                customFileInput.click();
            });

            customFileInput.addEventListener("change", function () {
                const files = customFileInput.files;
                if (files && files.length > 0) {
                    Array.from(files).forEach((file) => {
                        editorElement.editor.insertFile(file);
                    });
                }
                customFileInput.value = "";
            });
        }
    });

    document.addEventListener("trix-file-accept", function (event) {
        const editor = event.target;

        const maxFileSize = (editor.dataset.maxSize || 2048) * 1024;
        const allowedTypes = (editor.dataset.accept || "*").split(",");
        const file = event.file;

        if (file.size > maxFileSize) {
            event.preventDefault();
            const sizeInMB = editor.dataset.maxSize / 1024;
            triggerToast(
                `Ukuran file terlalu besar. Maksimal ${sizeInMB}MB.`,
                "error",
            );
            return;
        }

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

    document.addEventListener("trix-attachment-add", function (event) {
        if (event.attachment.file) {
            const editorElement = event.target;
            const uploadUrl = editorElement.dataset.uploadUrl;
            if (!uploadUrl) return;
            uploadFileAttachment(event.attachment, uploadUrl);
        }
    });
}

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

function uploadFileAttachment(attachment, uploadUrl) {
    uploadFile(attachment.file, uploadUrl, setProgress, setAttributes);

    function setProgress(progress) {
        attachment.setUploadProgress(progress);
    }

    function setAttributes(attributes) {
        attachment.setAttributes(attributes);
    }
}

function uploadFile(file, uploadUrl, progressCallback, successCallback) {
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
            console.error("Upload failed", xhr.responseText);
            alert("Gagal mengunggah file. Pastikan format dan ukuran sesuai.");
        }
    });

    xhr.send(formData);
}
