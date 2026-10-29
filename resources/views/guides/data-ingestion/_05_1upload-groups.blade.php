<h3 id="section-5">Section 5: Phenotype Analysis</h3>
<p>The Phenotype Analysis section (1) defines your sample groups, (2) determines what groups will be compared to each other, and (3) uploads your spreadsheet data for those animal groups.</p>

<ol>
    <li><strong>Sample Groups:</strong> Define distinct experimental groups by combining your variables (genotype, sex, age, drug treatment, surgical intervention)</li>
    <li><strong>Comparison Groups:</strong> Organize sample groups into comparisons that reflect your experimental design and statistical analysis plan</li>
    <li><strong>Data Upload:</strong> Associate individual animal measurements from your spreadsheet with their corresponding sample groups</li>
</ol>
<p class="fst-italic">The sample groups and comparison groups you create here form the foundation for statistical analysis and data visualization in the Report section of the form.</p>

<h4 id="sample-groups">Sample Groups</h4>
<p>Sample groups represent the distinct experimental cohorts in your study. Each sample group is defined by a unique combination of:</p>
<ul>
<li>Animal model (genotype/strain)</li>
<li>Sex</li>
<li>Age at phenotyping (in weeks)</li>
<li>Drug treatment plan (if applicable)</li>
<li>Gonadectomy procedure (if applicable)</li>
</ul>
<p>For example, your study might include sample groups such as "8-week-old male wild-type mice treated with vehicle" and "8-week-old male knockout mice treated with PTH." By creating well-defined sample groups, you ensure that individual animal data from your spreadsheets can be accurately linked to the correct experimental conditions during data upload.</p>


<h5>Creating Sample Groups</h5>
<p>The Sample Group Builder (Figure 14) combines a short form which exists below a tree diagram that visually tracks your selections as you build each sample group. This tree structure helps you:</p>
<ul>
<li>Navigate through the required variables systematically</li>
<li>See all created sample groups at a glance</li>
<li>Edit existing sample groups if you need to correct any information</li>
<li>Ensure you've created all necessary groups before proceeding to data upload</li>
</ul>

<figure>
    <img src="{{ asset('images/guides/data-ingestion/14_SampleGroups.png')}}" alt="" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 14 Creating Sample Groups.</strong> Step1: Create an appropriate name for your sample group. (For example, Gene X Knockout Males, or Gene X Wild Type Males.) Step2: Fill out the remainder of the form, including the age the animal was phenotyped, and their sex.  The checkbox for “Control Group” is simply there to help you organize sample groups in the tree diagram above and otherwise has no significance for upload. Step3: Once the information for the sample group is completed, click on the “Create Sample Group” button, which will show the above group in the tree diagram. Step4: Verify the information and edit if necessary.</figcaption>
</figure>


<h5 id="comparison-groups">Comparison Groups</h5>
<p>After defining your sample groups, you need to specify which groups should be compared to each other in your analysis. Comparison groups define the structure of your statistical comparisons and determine how data will be displayed in your study report.</p>

<h5>Creating a Comparison Group</h5>
<p>To create a comparison group, click the "+ Create New Comparison Group" button. This opens an interface with two panels:</p>

<figure>
    <img src="{{ asset('images/guides/data-ingestion/15_ComparisonGroups.png')}}" alt="" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 15. Creating Comparison Groups.</strong> Based on your input, ROSSA will generate your desired “Comparison Groups” from the sample groups you created in the step before. The plot shown on the left, provides an example of how the comparison groups are used. Step 1: Select the sample groups you desire to compare by simply clicking on the row. Step 2: Order the groups as desired by dragging up or down.  Finally, click “Create Comparison Group” button. Repeat for other samples as desired. </figcaption>
</figure>


<p><strong>Left Panel - (Figure 15, Step 1) Select two or more groups you would like to compare</strong>: All sample groups you've created are listed here. Review the list and click on each sample group you want to include in this comparison. For example, you might select both wild-type male and knockout male as one group and wild-type female, and knockout female as a second group.</p>

<p><strong>Right Panel - Order Selected Groups</strong>: The subset of sample groups you selected from the left panel will now appear in the right panel. You can define the specific order of these groups by clicking and dragging up or down. The order of groups determines the left-to-right order in which groups will appear in plots and the sequence used for statistical comparisons (see Figure 15 for an example). Arrange them in the order that makes the most sense for your analysis. For instance, you might order them as: wild-type vehicle, wild-type drug, knockout vehicle, knockout drug-grouping by genotype first, then treatment.</p>
<p><strong>Finalizing the Comparison</strong>: Once you've selected your sample groups and arranged them in your desired order, click the blue "Create Comparison Group" button. This saves your comparison and returns you to the Comparison Groups table.</p>
<p><strong>Creating Multiple Comparisons</strong>: You can create as many comparison groups as needed for your study. Comparison groups are not limited to two sample groups-you can include as many groups as necessary to represent your experimental design. For example, you might create separate comparisons for:</p>

<ul>
    <li><strong>Males only:</strong> Wild-type male, knockout male</li>
    <li><strong>Females only:</strong> Wild-type female, knockout female</li>
    <li><strong>Drug treatment across genotypes:</strong> Wild-type vehicle, wild-type drug, knockout vehicle, knockout drug (four groups in one comparison)</li>
    <li><strong>Dose response:</strong> Wild-type control, wild-type low dose, wild-type medium dose, wild-type high dose (four groups in one comparison)</li>
</ul>
<p>The flexibility to include multiple groups in a single comparison allows you to capture complex experimental designs and visualize all relevant conditions side-by-side in your plots and statistical analyses.</p>
<p>To add another comparison group, click the "+ Create New Comparison Group" button again and repeat the process. Each comparison group you create will be available for statistical analysis and visualization in the Report section.</p>