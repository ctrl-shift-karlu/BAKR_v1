{{-- File preview for scholars' uploaded documents (PDF in a frame, JPG / PNG as an image), so the
     provider does not have to download a file just to look at it. Opened by any [data-file-preview]
     (public/js/bakr-provider.js); the file is served inline with a fixed type and nosniff. --}}
<dialog class="dash-dialog pv-preview" id="pv-file-preview" aria-labelledby="pv-preview-name">
    <div class="pv-preview-head">
        <span class="pv-preview-icon" aria-hidden="true"><i class="bi bi-file-earmark"></i></span>
        <div class="pv-preview-title"><strong id="pv-preview-name">Preview</strong><small id="pv-preview-meta"></small></div>
        <button type="button" class="dash-dialog-close" data-close-dialog aria-label="Close preview"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>
    <div class="pv-preview-stage" id="pv-preview-stage"></div>
    <div class="dash-dialog-foot">
        <a class="dash-btn is-soft" id="pv-preview-open" href="#" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>Open in new tab</a>
        <a class="dash-btn is-soft" id="pv-preview-download" href="#" download><i class="bi bi-download" aria-hidden="true"></i>Download</a>
        <button type="button" class="dash-btn" data-close-dialog>Close</button>
    </div>
</dialog>
