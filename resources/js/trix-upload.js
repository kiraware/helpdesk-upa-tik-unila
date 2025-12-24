export function initTrixAttachmentUpload() {
    addEventListener("trix-attachment-add", function (event) {
        if (event.attachment.file) {
            uploadFileAttachment(event.attachment);
        }
    });
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
    // AMBIL URL DARI DATA ATTRIBUTE ELEMENT EDITOR
    // Kita asumsikan element <trix-editor> punya attribute data-upload-url
    const editorElement = document.querySelector("trix-editor");
    if (!editorElement) return;

    const uploadUrl = editorElement.dataset.uploadUrl;
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    if (!uploadUrl) {
        console.error("Upload URL not found in trix-editor data attribute");
        return;
    }

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
        }
    });

    xhr.send(formData);
}
