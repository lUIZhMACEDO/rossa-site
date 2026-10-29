<h3 id="section-6">Section 6: Report</h3>
<p>At the end of the data ingestion process, ROSSA automatically generates a comprehensive report containing critical information about your study. This report is what will be publicly shared with the broader research community, so it's important to review it carefully before finalizing your submission. The report is organized into three tabs: Study Information, Data Exploration, and Download Original Data.</p>

<p></p>

<figure>
    <img src="{{ asset('images/guides/data-ingestion/21_Report.png')}}" alt="" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 21. Information Tab in your Report.</strong> Please double check the information about your study in the information tab for accuracy. The Information tab is broken down into different sections of the form and by clicking on the pencil icon, you can go back to different sections to make edits as necessary.</figcaption>
</figure>

<h4 id="report-information">Tab 1: Study Information</h4>
<p>The Study Information tab (Figure 21) provides a complete overview of your study, organized into the following sections:</p>
<ul>
<li><strong>General Study Details:</strong> Study title, publication information (DOI and PubMed ID), completion date, study summary, funding sources, and conflicts of interest</li>
<li><strong>Subject Areas:</strong> The thematic areas, tissue interactions, cell types, and disease types you annotated during the submission process</li>
<li><strong>Investigators:</strong> Corresponding investigators, plus any contributing investigators</li>
<li><strong>Experimental Design:</strong> Details about the animal models used in your study, including genotype/strain information, and descriptions of any drug treatments or gonadectomy procedures performed</li>
</ul>
<p><strong>Making Edits:</strong> Each section on the Study Information tab includes a pencil icon that allows you to quickly navigate back to the corresponding section of the form to make edits if you notice any errors or want to update information.</p>
<p>This tab serves as the primary reference for researchers wanting to understand the context and design of your study.</p>

<h4 id="report-data-exploration">Tab 2: Data Exploration</h4>
<p>The Data Exploration tab (Figure 22) provides interactive access to your phenotyping data with built-in statistical analysis and visualization tools:</p>
<p><strong>Data Selection Panel (top left):</strong> A list of all datasets you uploaded (e.g., uCT femur, uCT vertebra, mechanical testing), selectable via radio buttons. Selecting a dataset loads its measurements for analysis.</p>
<p><strong>Comparison Groups Panel (top right):</strong> Displays the comparison groups you created during submission, showing which sample groups are being compared in your study. This panel is informational-it shows users how you structured your experimental comparisons.</p>
<p><strong>Statistics Tables and Plots:</strong> ROSSA automatically generates statistical tables and box plots for all comparison groups you created.</p>
<p><strong>Statistics Table Layout:</strong></p>

<ul>
    <li><strong>Comparison Group Tabs (top left of table):</strong> Switch between different comparison groups to view their respective statistics</li>
    <li><strong>Action Buttons (top right of table):</strong>
        <ul>
            <li><strong>CSV Download:</strong> Export the current statistics table</li>
            <li><strong>Statistics:</strong> Return to table view (if currently viewing plots)</li>
            <li><strong>Box Plots:</strong> Switch the entire display to show box plots for all measurements, replacing the table view</li>
        </ul>
    </li>
</ul>

<p><strong>Statistics Table Content:</strong> Displays descriptive statistics (mean and standard deviation) for each sample group within the selected comparison group, along with:</p>

<figure>
    <img src="{{ asset('images/guides/data-ingestion/22_DataExploration.png')}}" alt="" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 22. Data Exploration in your Report.</strong> Inside the exploration tab you can select one or more datasets (1), View the different sample groups (2), and view the statistics and plots of data for different comparison groups you defined earlier in the form (3). You can click on rows within the table to pull up plots and scroll forward or back through data on the plots. You can even view individual mouse data.</figcaption>
</figure>

<ul>
<li><strong>Automatic statistical testing:</strong> When comparing two groups, an independent t-test is performed. When comparing three or more groups, ANOVA with Tukey's post-hoc test is performed to identify significant differences between groups.</li>
</ul>
<p><strong>Viewing Box Plots:</strong> There are two ways to view box plots:</p>
<ol>
<li><strong>Individual measurement view:</strong> Click on any row in the statistics table to open a modal window displaying a box plot for that specific measurement parameter. Use the forward and back navigation buttons within the modal to advance through measurements. Box plots can be downloaded as PNG images.</li>
<li><strong>All plots view:</strong> Click the "Box Plots" button in the top right of the table to replace the table view with box plots for all measurements in the selected comparison group.</li>
</ol>

<h4 id="report-download">Tab 3: Download Original Data</h4>
<p>The Download Original Data tab lists all the individual CSV files you uploaded to ROSSA during the data ingestion process. Users can download these spreadsheets directly to access your raw data in its original form.</p>
<p><strong>Important:</strong> Because these files will be publicly accessible, we strongly encourage you to prepare your spreadsheets for public viewing before upload. Ensure that:</p>
<ul>
<li>Column headers are clear and professional</li>
<li>Sample names are systematic and understandable</li>
<li>No extraneous notes or unprofessional content is included</li>
<li>The data is organized in a way that other researchers can readily interpret</li>
</ul>
<p>Providing access to original data files ensures authenticity and transparency, allowing other researchers to verify your findings and potentially perform additional analyses.</p>

<figure>
    <img src="{{ asset('images/guides/data-ingestion/23_OriginalFiles.png')}}" alt="" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 23. Original Data Download Tab in your Report.</strong> Your original files you provided for upload are publicly accessible for download in this tab. </figcaption>
</figure>