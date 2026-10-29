<div class="guide-headers">

<h1>ROSSA Data Ingestion Guide</h1>
<p class="mb-1">Version 1</p>
<p class="text-muted">2/16/2026</p>


<h2 id="current-experimental-approaches">Current Experimental Approaches Being Accepted</h2>

<h3 id="genetic-animal-models">Genetic Animal Models</h3>
<p>Skeletal phenotyping studies using genetically modified animal models are currently being accepted. The submission process seeks to collect data on a wide array of genetic mutants including global knockouts, conditional knockouts, spontaneous, and disease models.</p>

<h3 id="preclinical-drug-testing">Preclinical Drug Testing</h3>
<p>Skeletal phenotyping studies that test and compare individual drug treatments, drug cocktails, or even drug treatments performed in a sequential manner are currently being accepted.</p>

<h3 id="gonadectomy">Gonadectomy</h3>
<p>Skeletal phenotyping studies where a gonadectomy was performed on wild type animals or in conjunction with a genetic mutant or a drug treatment.</p>

<h3 id="complex-combined">Complex Combined Experimental Studies</h3>
<p>We understand how complicated animal studies can get and have designed the ingestion process in a manner that allows investigators to combine genetic, drug, and gonadectomy variables in desired animal groupings.</p>

<h3 id="future-approaches">Future Experimental Approaches in the Pipeline</h3>
<p>Our group is working hard to design the data submission process to be as comprehensive as possible. Future plans include the experimental categories detailed below. If you desire an experimental category that currently is not available, please contact us (<a href="mailto:rossa@uchc.edu">rossa@uchc.edu</a>). We are here to support the skeletal community.</p>
<ul>
    <li>Mechanical Loading/Unloading Studies</li>
    <li>Comparing Animal Diets (Testing Nutrients, Phytochemicals, Probiotics)</li>
    <li>Circadian Rhythm Studies (Comparing different light/dark cycles)</li>
</ul>

<h2 id="accepted-analyses">Currently Accepted Skeletal Phenotyping Analyses Approaches</h2>
<p>We have focused on collecting phenotyping analysis approaches that are widely and routinely used by members of the scientific community (detailed below). To view specific measurement parameters for each type of analysis please visit the <a href="{{ route('measurement-parameters')}}">Measurement Parameters page</a>. Please contact us (rossa@uchc.edu), if you believe we should include other analysis types.</p>
<ul>
    <li>Dual X-Ray Absorptiometry</li>
    <li>Microcomputed Tomography</li>
    <li>Bone Histomorphometry</li>
    <li>Mechanical Strength Testing</li>
    <li>Clinical Chemistry Analytes</li>
</ul>

<h2 id="preparing-data">Preparing Your Data for Upload</h2>

<h3 id="data-file-types">Data File Types</h3>
<p>Currently ROSSA only accepts spreadsheet data in CSV file format (further described below). Future plans will include the ability to upload image files from microcomputed tomography and bone histomorphometry studies.</p>

<h3 id="preparing-spreadsheet">Preparing Your Personal Spreadsheet Files</h3>
<p>ROSSA currently accepts CSV formatted spreadsheet files of <em>individual</em> rodent datasets. For database security purposes, we cannot accept spreadsheet data saved in Excel file formats, so please save each sheet of data in your Excel file to separate CSV files before submission. The data ingestion process has been designed to directly accept your spreadsheet data files, however, <em>to accurately read your spreadsheet it is important to specifically write "Sample Name" in the column header where your sample id's exist and in the same row as your measurement parameters</em> as demonstrated in Figure 1.</p>

<figure>
    <img src="{{ asset('images/guides/data-ingestion/01_Datasheet.png')}}" alt="Example of a spreadsheet with sample names in the first column and measurement parameters in the first row" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 1. Placing “Sample Name” in your csv spreadsheet data file.</strong> For your personal datafile(s) to be properly read, please change the header column above your sample ids to Sample Name and in the same row as your measurement parameters.</figcaption>
</figure>


<h3 id="preconfigured-measurements">ROSSA Accepts Preconfigured and Custom Measurements</h3>
<p>To ensure your data is accurately read and annotated, after uploading the spreadsheet enters a viewer that provides human-in-the-loop validation to confirm or correctly match measurement parameters (Figure 2). Correctly mapped measurement parameters contain a green checkbox next to them. Incorrectly mapped or "unmapped" measurement parameters contain a yellow triangle contain a (!) next to them. Unmapped measurement parameters can occur for two reasons. One, while the measurement parameter is properly configured in ROSSA, our list of synonyms did not match what is being used to represent the measurement parameter in the user's spreadsheet. (To learn what measurement parameters ROSSA has been preconfigured with please visit the <a href="{{ route('measurement-parameters')}}">Measurement Parameters page</a>.) For this, we ask users to first look for the measurement parameter in the dropdown and select the appropriate measurement. The other reason for an unmapped measurement is that ROSSA is not preconfigured with this measurement parameter. If this is the case and you want this measurement to be part of a viewable dataset, we ask you to use the custom checkbox and input the measurement parameter and its measurement unit. Note that it is not necessary to map all the information in your spreadsheet. We understand that your spreadsheet may contain content that is not of interest to the research community.</p>

<figure>
    <img src="{{ asset('images/guides/data-ingestion/02_MeasurementMapping.png')}}" alt="Example of preconfigured and custom measurements in ROSSA" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 2. Measurement mapping in ROSSA.</strong> Preconfigure measurement parameters are automatically mapped to the user’s datasheet measurement parameters. If properly mapped a green checkmark is displayed. If the measurement parameter does not map, a yellow triangle symbol containing (!) is displayed. This may be fixed by either finding the correct measurement parameter in the dropdown, where it says unmapped or by checking custom and inputting the measurement parameter and units of measure.</figcaption>
</figure>

<h3 id="data-templates">Optional Use of Data Template Spreadsheet Files</h3>
<p>As an alternative to your personal data files, we have also generated <a href="{{ route('download.index') }}">spreadsheet template files</a> for different phenotyping analysis including DEXA, Microcomputed Tomography, Bone Histomorphometry, and Mechanical Strength Testing. The use of these template files is NOT essential for data upload but certainly can be used if for some reason your own spreadsheets are not working or your core facility has adopted the use of these data templates in their workflow. All data template files can be downloaded as excel files. The contents of template files vary with the corresponding analysis type. Figure 3 provides an example of the microcomputed tomography data template file, which contains three sheets. Sheet 1 is an instruction sheet, sheet 2 contains a layout with trabecular bone measurement parameters, and sheet 3 contains a layout to input cortical bone data. Note, that while the datasheet can be downloaded as an excel file, for upload into ROSSA individual Excel data sheets should be saved as separate CSV files.</p>
<figure class="d-flex flex-column flex-md-row gap-5">
    <img src="{{ asset('images/guides/data-ingestion/03_TemplateFiles.png')}}" alt="Example of a data template file for microcomputed tomography" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 3. Example Data Template File.</strong> Spreadsheet template files for different analysis types are provided as an optional approach to upload data. These files are in excel format and may provide multiple sheets dependent on the analysis approach. For example, For the microcomputed tomography template, sheets for instructions, trabecular measurement parameters, and cortical measurement parameters are provided. Save individual sheets as CSV files prior to upload.</figcaption>
</figure>

<h2 id="data-submission">Data Submission into ROSSA</h2>

<h3 id="user-account">User Account/Login</h3>
<p>To submit data into ROSSA, we require you to create a user account. For this, go to the top right corner of the webpage and select "Create Account." Provide your name, email address, and password to register. Upon submission, you should receive an email confirming your registration. Clicking the link within this email will take you to the Data Submission Portal quick guide to data submission (described below). While we don't anticipate lengthy submission times, don't be concerned if you cannot complete your submission in one session. The progress of your study is tracked and progressively saved. To complete your submission at another time, simply Login, go to the submission portal dropdown (Figure 4) and select "My Submissions", which will take you to a webpage showing the progress of your study. Here simply select "continue study" to continue filling out the submission form.</p>

<h3 id="submission-portal">Data Submission Portal</h3>
<p>If not already here from registering an account, select the "Submission Portal" dropdown in the top navbar (Figure 4). Upon selection, a drop-down menu will show four items related to the data submission process. We recommend starting with the "Submit Your Data" page.</p>

<figure>
    <img src="{{ asset('images/guides/data-ingestion/04_DataSubmissionDropdown.png')}}" alt="Data submission portal dropdown menu" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 4. Data Submission Dropdown and Contents:</strong> Various components of data submission can be found in the “Submission Portal” dropdown on the homepage. Brief instructions are provided under “Submit Your Data.” Details of measurement parameters that ROSSA is designed to ingest are provided under “Supported Measurement Parameters.” A detailed guide to data submission is provided in the “Help Guide.” Also, to continue your submission at a different time select the “My Submissions.”</figcaption>
</figure>

<h4>Submit Your Data</h4>
<p>The “Submit Your Data” page provides a variety of content related to the submission process including, (1) scope of submission, (2) publication and embargo policy, (3) access to data submission help guides, (4) experimental and (5) phenotyping approaches accepted, and (6) tips for filling out the form. This page also includes a “Quick Guide to Data Submission,” which provides you with a brief set of instructions and an overview of the submission process.</p>

<h4>Depositor Agreement</h4>
<p>At the bottom of the “Submit Your Data” page is a button that will take you to the Depositor Agreement. Here we ask you in good faith to be as accurate, honest, and complete as possible in your submission. We also provide extreme transparency of our policies and require you to acknowledge reading, understanding, and agreeing to our policies before being able to create a new study. Once the box is checked, an “initiate study submission” button will appear (Figure 5). Clicking on this button will take you to the start of the data ingestion form.</p>
<p><strong>Note:</strong> If you have not registered for an account or logged in, clicking this button will take you to a Login page. You are required to have an account to submit data and need to be logged in to fill out the submission form.</p>

<figure class="d-flex flex-column flex-md-row gap-4">
    <img src="{{ asset('images/guides/data-ingestion/05_DepositorAgreement.png')}}" alt="Create new study page with agreement statement and checkbox" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 5. Depositor Agreement.</strong> Checking this box is required to enter the submission form.</figcaption>
</figure>

<h4>Sidebar Navigation and Tracking</h4>
<p>Upon entering the data submission form you will notice a sidebar on the left detailing different sections of the form. The sidebar serves two main functions. First, provide a simple way to navigate from one part of the form to another by simply clicking on a section title. Second, providing feedback to you based on the use of symbols (Figure 6). Yellow triangles with containing an! indicate incomplete sections, while green circles with a check indicate complete sections. The form also contains the typical red asterisk * for required input fields.</p>

<figure>
    <img src="{{ asset('images/guides/data-ingestion/06_SidebarIndicators.png')}}" alt="Sidebar navigation and tracking indicators" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 6. Sidebar Indicators Provide Feedback.</strong> To assist in the submission process, the sidebar contains symbols to direct you to finished (green check) or incomplete sections of the form.</figcaption>
</figure>

<h4>Guidance During the Submission Process</h4>
<p>To support your data submission efforts, at the top right corner of every page in the submission form is a “Help Guide” button that will take you directly to instructions related to the page you are on.</p>

<figure>
    <img src="{{ asset('images/guides/data-ingestion/062_HelpButton.png')}}" alt="Help button" class="img-fluid mb-3 border">
</figure>

<h2 id="form-sections">Sections of the Data Submission Form</h2>
<p>For the best user experience, we ask that you fill out the form in the order provided. <span class="fw-bold fst-italic">At the bottom of each section, please remember to click save and continue.</span></p>


@include('guides.data-ingestion._01_study-info')
@include('guides.data-ingestion._02_subject-areas')
@include('guides.data-ingestion._03_investigators')
@include('guides.data-ingestion._04_1animalexp-models')
@include('guides.data-ingestion._04_2animalexp-drug')
@include('guides.data-ingestion._04_3animalexp-gonadectomy')
@include('guides.data-ingestion._05_1upload-groups')
@include('guides.data-ingestion._05_2upload-steps')
@include('guides.data-ingestion._05_3image-upload')


@include('guides.data-ingestion._06_report')

@include('guides.data-ingestion._07_submission')

</div>
