<h4 id="drug-treatments">Drug Treatments</h4>
<p>If your study involves the use of one or more drug treatments, check the box "Investigates the impact of a drug treatment on the skeleton" in the Setup questions. This will activate the Drug Treatments component within the form.</p>
<p>Drug treatments in ROSSA are documented as individual drug administration plans. Each plan represents a complete treatment regimen used in your study and can include one or more administration events. An "event" is a single drug with its specific dosing parameters (dose, route, frequency, duration). This structure allows you to capture complex treatment protocols within a single plan, including:</p>

<ul>
    <li><strong>Single drug treatments:</strong> One plan with one administration event</li>
    <li><strong>Combination therapies:</strong> One plan with multiple simultaneous administration events (e.g., Drug A + Drug B given together)</li>
    <li><strong>Sequential treatments:</strong> One plan with multiple administration events given at different timepoints (e.g., pre-treatment with Drug A, followed by Drug B)</li>
</ul>

<p>At minimum, most studies will have two plans: (1) an experimental drug treatment plan and (2) a matching control plan. For vehicle controls, you do not need to build the second plan by hand—after creating your treatment plan, ROSSA can generate a matching vehicle control plan for you automatically (see "Generating a Vehicle Control Plan" below). Other types of controls, such as an isotype-matched antibody or a sham procedure, are created as their own plans using the same steps as any treatment plan. If your study includes multiple distinct treatment regimens (such as different dose levels of the same drug, or entirely different drug protocols), create a separate plan for each regimen.</p>

<h5>Using the ChEMBL Database to Identify Drugs</h5>
<p>The Drug Treatments section includes an integrated search feature that connects to the ChEMBL database-a comprehensive, manually curated repository of bioactive drug-like compounds. ROSSA uses ChEMBL for two important reasons:</p>
<ol>
    <li><strong>Standardized nomenclature:</strong> ChEMBL provides consistent drug names and identifiers, ensuring that the same compound is always annotated the same way across different studies</li>
    <li><strong>Rich drug information:</strong> ChEMBL entries include detailed information about each compound (chemical structure, targets, mechanisms of action), which helps users better understand and contextualize your experimental treatments</li>
</ol>

<h5>If Your Drug Is Not in ChEMBL</h5>
<p>While ChEMBL is relatively comprehensive, it does not contain every compound used in research. If you cannot find your drug using the ChEMBL search:</p>

<ol>
    <li>Check the box labeled "I cannot find my drug in ChEMBL"</li>
    <li>Manually enter your drug information in the fields that appear</li>
</ol>

<p>This ensures you can still document your drug treatments even when working with novel compounds, custom formulations, or specialized research chemicals not yet cataloged in ChEMBL.</p>

<h4 id="drug-administration-plan">Creating a Drug Administration Plan</h4>

<h5>Step 1: Initiate a New Plan (Figure 11)</h5>
<p>Click the blue "+ Create New Drug Administration Plan" button. This opens a form with two main sections: "Plan Information" and "Administration Events."</p>

<h5>Step 2: Complete Plan Information</h5>
<p>In the Plan Information section:</p>
<ul>
    <li><strong>Plan Name</strong>: Create a descriptive name that clearly identifies this treatment regimen. For example, if treating with parathyroid hormone (PTH), you might name your plan "PTH Treatment." The drug plan names you create will be used in future components of the form to help define sample groups.</li>
    <li><strong>Treatment Start Age</strong>: Enter the age of the animals (in weeks) when this treatment regimen began.</li>
    <li><strong>Description (Optional)</strong>: Use this text box to provide additional context about your treatment protocol, such as the rationale for the regimen, special handling procedures, or any relevant details about the overall treatment approach.</li>
</ul>

<h5>Step 3: Add Administration Events</h5>
<p>The Administration Events section is where you define each individual drug and its administration parameters within this treatment plan. Each event represents a single drug with its specific dosing details, but a plan can have more than one "event" if multiple drugs are used in the treatment plan.</p>
<ul>
    <li><strong>Drug Search:</strong> Enter the full drug name (not abbreviations) in the search box to query the ChEMBL database. If your drug appears in the search results, select it to auto-populate drug identifiers and information.</li>
    <li><strong>Manual Entry:</strong> If your drug does not appear in ChEMBL search results, check the box "I cannot find my drug in ChEMBL" and manually enter the drug information in the fields provided.</li>
    <li><strong>Administration Details:</strong> Complete the required fields describing the drug source, dose, route of administration, frequency, and duration for this specific drug.</li>
    <li><strong>Adding Multiple Events:</strong> If your treatment plan involves more than one drug-whether administered simultaneously as a combination therapy (e.g., Drug A + Drug B given together throughout the study) or sequentially (e.g., Drug A for the first 4 weeks, followed by Drug B for the next 4 weeks)-click "Add Another Administration Event" and repeat the process for each additional drug. You can add as many events as needed to fully capture your treatment regimen within this single plan.</li>
</ul>

<h5>Step 4: Review and Create</h5>
<p>Before finalizing your plan, review the timeline preview displayed at the bottom of the form. This visual representation summarizes key information from both the Plan Information and Administration Events sections, showing how your treatment regimen unfolds over time. Verify that the timeline accurately reflects your protocol, then click "Create Plan" to save.</p>

<figure>
    <img src="{{ asset('images/guides/data-ingestion/11_DrugPlans.png')}}" alt="Drug Administration Plans interface" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 11 Creating a Drug Administration Plan.</strong>  Step 1: Click on the Create New Drug Administration Plan button. Step 2: Provide a Plan Name and the animal age the treatment was started. Step 3: Add relevant drug information and add additional drugs if part of the existing plan. Step 4: Review the plan timeline and make corrections if necessary. Click the "Create Plan" button when okay. Create additional plans as necessary.</figcaption>
</figure>

<h5>Confirming and Adding Additional Plans:</h5>
<p>Once created, your drug administration plan will appear in the Drug Administration Plans List (Figure 12), confirming it has been successfully saved.</p>
<p>To create additional treatment plans for your study, click the "+ Create New Drug Administration Plan" button and repeat the process. For example, you might create separate plans for:</p>
<ul>
<li>Different treatment groups (e.g., high dose vs. low dose)</li>
<li>Control groups that are not vehicle controls (e.g., isotype-matched antibody control, sham procedure)</li>
<li>Alternative drug regimens being compared in your study</li>
</ul>

<h5>Generating a Vehicle Control Plan</h5>
<p>For vehicle controls, you do not need to recreate the regimen by hand. On the Drug Administration Plans List, each treatment plan that has vehicle information recorded displays a "Create vehicle control" button (Figure 12). Clicking it generates a new plan that mirrors the original—same animal age, route, frequency, schedule, and administration volume—but with the active drug removed, leaving only the vehicle. If two drugs in a plan share the same vehicle, route, and schedule, they are combined into a single control event, reflecting how a vehicle control would actually be administered.</p>
<p>Once generated, the vehicle control appears as its own plan in the list, labeled with a "Vehicle control" badge and linked back to the treatment plan it controls. The original treatment plan's button is replaced by an indicator showing which plan its vehicle control is. The generated plan is fully editable, so you can adjust its name or details through the Edit button if needed. Because the generator only produces vehicle controls, you remain free to add other control types (such as an isotype control) as separate plans.</p>

<p>All drug administration plans you create will be stored and made available later in the form when you define the specific sample groups that underwent phenotypic analysis. This allows you to assign the appropriate treatment history to each group of animals measured in your study.</p>


<figure>
    <img src="{{ asset('images/guides/data-ingestion/12_DrugPlansList.png')}}" alt="" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 12: Drug Administration Plans List.</strong> Once you successfully create a drug administration plan, it will be displayed as part of a list. Confirm the information you provided in each plan. For treatment plans with a recorded vehicle, use the "Create vehicle control" button to generate a matching control plan automatically. If changes need to be made you can click the edit button, or if you prefer to start over click the delete button.</figcaption>
</figure>