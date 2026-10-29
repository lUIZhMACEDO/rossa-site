<h4 id="upload-data">Upload Data</h4>

<p>The Upload Data component accepts spreadsheet data in CSV file format. ROSSA is designed to directly read your spreadsheet files with minimal reformatting, but there is one critical requirement: <strong>you must include "Sample Name" as a column header</strong> in the same row as your measurement parameter headers (see Figure 16). This column should contain your unique sample identifiers (animal IDs) that will be used to link each row of data to the appropriate sample group.</p>

<figure>
    <img src="{{ asset('images/guides/data-ingestion/01_Datasheet.png')}}" alt="Example of a spreadsheet with sample names in the first column and measurement parameters in the first row" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 1. Placing “Sample Name” in your csv spreadsheet data file.</strong> For your personal datafile(s) to be properly read, please change the header column above your sample ids to Sample Name and in the same row as your measurement parameters.</figcaption>
</figure>

<p><strong>Supported Analysis Types:</strong></p>
<p>ROSSA currently accepts the following skeletal phenotyping data types:</p>

<ul>
    <li>Microcomputed Tomography (uCT)</li>
    <li>Dual-Energy X-Ray Absorptiometry (DXA)</li>
    <li>Bone Histomorphometry</li>
    <li>Mechanical Strength Testing</li>
    <li>Clinical Chemistry</li>
</ul>
<p>To begin uploading data, click the "Upload New Analysis" button. This takes you to a page displaying cards for each analysis type. Select the card corresponding to your data type to begin the upload process. Depending on the analysis type selected, you will be asked to provide some minimal essential information (such as anatomical site or testing parameters) in addition to uploading your CSV file.</p>

<figure>
    <img src="{{ asset('images/guides/data-ingestion/16_UploadCards.png')}}" alt="" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 16 Uploading Skeletal Phenotyping Spreadsheet Data.</strong> ROSSA contains five different types of skeletal phenotyping analysis approaches. Select your analysis type and upload your CSV spreadsheet data file.</figcaption>
</figure>

<p><strong>Body Weight Data:</strong> Because body weight is a crucial parameter in skeletal phenotyping, <stron>we request you include animal body weight in every data spreadsheet you upload to ROSSA.</strong> Include body weight as one of the measurement columns in your CSV file.</p>

<h5>The Three-Step Upload Process</h5>
<p>The upload workflow consists of three steps: (1) Map Measurements, (2) Group Samples, and (3) Review Data.</p>

<h5 id="upload-map-measurements">Step 1: Map Measurements</h5>
<p>(Figure 17) To streamline data ingestion, ROSSA automatically attempts to match the column headers in your spreadsheet to preconfigured measurement parameters in the database. Your task is to verify and correct these mappings:</p>
<ul>
<li><strong>Correctly mapped parameters</strong> display a green checkmark () next to them</li>
<li><strong>Unmapped or incorrectly mapped parameters</strong> display a yellow warning triangle (!) next to them</li>
</ul>
<p>For any unmapped measurements, use the dropdown menu to select the correct measurement parameter from ROSSA's standardized list. If your measurement is not available in the dropdown, check the "Custom" box to add it manually (Figure 17).</p>
<p>Note: Your spreadsheet may contain columns that are not measurement parameters (such as notes, dates, or other metadata). These do not need to be mapped for successful data ingestion-simply leave them unmapped.</p>
<p>Once all relevant measurement parameters have been correctly mapped, click the "Save Column Mapping and Continue" button.</p>
<figure>
    <img src="{{ asset('images/guides/data-ingestion/02_MeasurementMapping.png')}}" alt="Example of preconfigured and custom measurements in ROSSA" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 17. Measurement mapping in ROSSA.</strong> Preconfigure measurement parameters are automatically mapped to the user's datasheet measurement parameters. If properly mapped a green checkmark is displayed. If the measurement parameter does not map, a yellow triangle symbol containing (!) is displayed. This may be fixed by either finding the correct measurement parameter in the dropdown, where it says unmapped or by checking custom and inputting the measurement parameter and units of measure.</figcaption>
</figure>

<h5 id="upload-group-samples">Step 2: Group Samples: </h5>
<p>(Figure 18) In this step, you associate each individual sample (Under Sample Name) with its corresponding sample group. The interface displays:</p>
<ul>
    <li><strong>Left column:</strong> Your sample names (animal IDs) from your spreadsheet, listed vertically</li>
    <li><strong>Right columns:</strong> The sample groups you created earlier in the form</li>
</ul>
<p>Work down the list row by row, clicking the appropriate sample group button for each sample. When selected, the button will turn from white to blue (Figure 18). This links each animal's data to the correct experimental conditions (genotype, sex, age, treatment, etc.).</p>
<p>Once all samples have been assigned to their respective sample groups, click the "Save Groups &amp; Review Samples" button.</p>

<figure>
    <img src="{{ asset('images/guides/data-ingestion/18_GroupSamples.png')}}" alt="Assigning samples to sample groups" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 18. Grouping your samples.</strong>  ROSSA displays the Sample Names pulled from your spreadsheet next to the sample groups you generated earlier in the form.  Simply click on the sample group button (which will change from white to blue) to correctly associate your sample with that specific group.  Navigate down the form row by row to complete this for all samples.</figcaption>
</figure>

<h4 id="upload-review">Step 3: Review the Ingested Spreadsheet Data</h4>
<p>(Figure 19)The final step displays your uploaded data in a table format. Scroll both vertically and horizontally to verify that:</p>
<ul>
    <li>All measurements were imported correctly</li>
    <li>Sample assignments are accurate</li>
    <li>Values appear as expected</li>
</ul>
<p>If you notice any problems, use the navigation buttons (Prepare Upload &gt; Map Measurements &gt; Group Samples &gt; Review) to return to previous steps and make corrections.</p>
<p>If everything looks correct, click the "Finalize Upload" button, then confirm by clicking "Confirm &amp; Finalize."</p>
<figure>
    <img src="{{ asset('images/guides/data-ingestion/19_ReviewData.png')}}" alt="" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 19. Review the Spreadsheet Data.</strong> Step 1: Double check that the form you uploaded matches your intended analysis type and appropriate metadata. Step 2: Navigate left and right with the scroll buttons to validate measurement parameters are associated with the correct data. Step 3: Confirm that the samples are associated with the appropriate sample groups.</figcaption>
</figure>

<p><strong>Confirming Upload and Adding Additional Data:</strong></p>
<p>After clicking "Confirm &amp; Finalize," information about your uploaded dataset will appear in the Phenotype Analysis table (Figure 20). This confirms your data has been successfully ingested.</p>
<p>To upload additional phenotyping data (for example, a different analysis type or data from a different anatomical site), click the "Upload New Analysis" button and repeat Steps 1-3. You can upload as many datasets as needed to fully represent the phenotypic analyses performed in your study.</p>
<figure>
    <img src="{{ asset('images/guides/data-ingestion/20_UploadedFiles.png')}}" alt="" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 20. Table displaying your uploaded spreadsheet data along with its corresponding metadata.</strong></figcaption>
</figure>