<h4 id="section-5-image-upload">Image Upload</h4>

<h5>What to upload</h5>
<p>MicroCT data typically exists along a processing hierarchy — from the original reconstructed volume straight off the scanner, to a cropped region of interest, to a fully segmented compartment (e.g., isolated trabecular or cortical bone). We're happy to accept a file from any point in that hierarchy, but where possible, we prefer the original, unprocessed scan volume.</p>
<p>The reason: the full scan volume retains everything a future researcher might want to do with it — a different region of interest, a different segmentation approach, a reanalysis with new methods — while a cropped or segmented sub-volume only preserves what was already decided at the time it was cropped. The further along the processing hierarchy a file is, the less flexible it is for someone else to reuse later. So even though a segmented ROI may be exactly what you needed for your own analysis, the raw scan is usually of greater long-term value to the broader research community.</p>
<p class="mb-1 fw-semibold">In order of preference:</p>
<ol>
    <li><strong>Original, unprocessed scan volume (preferred)</strong> — the full reconstructed output as produced by the scanner, before any cropping or segmentation.</li>
    <li><strong>Cropped region of interest</strong> — a sub-volume selected from the full scan, but not yet segmented.</li>
    <li><strong>Segmented/processed volume</strong> — a specific compartment already isolated (e.g., trabecular-only or cortical-only).</li>
</ol>
<p>If you're only able to share a file further down this hierarchy, that's still useful and welcome — just note at upload which level it represents, so we can describe it accurately.</p>
<p class="mb-1 fw-semibold">What the original scan looks like depends on your scanner:</p>
<ul>
    <li><strong>Scanco systems:</strong> the original <code>.ISQ</code> file.</li>
    <li><strong>Bruker/SkyScan systems:</strong> the complete set of reconstructed slice images (TIFF stack) together with the acquisition <code>.log</code> file, compressed into a single <code>.zip</code>.</li>
    <li><strong>Other systems / DICOM export:</strong> the DICOM series for the scan. Note that DICOM exports are sometimes cropped or processed before export — if you know whether yours is the full acquisition or a subset, letting us know helps us describe the file accurately.</li>
</ul>
