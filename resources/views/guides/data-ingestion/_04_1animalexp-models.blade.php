<h3 id="section-4">Section 4: Animal Experimentation</h3>
<p>The Animal Experimentation section identifies the experimental variables used in your study. This information is essential for properly annotating sample groups later in the Phenotype Analysis section, ensuring that your experimental design is accurately represented in ROSSA.</p>

<h4>Current Experimental Variables</h4>
<p>ROSSA is currently designed to capture three major categories of experimental manipulation:</p>
<ul>
    <li>Genetic modifications (transgenic models, knockouts, knock-ins, etc.)</li>
    <li>Drug treatments (pharmacological interventions)</li>
    <li>Gonadectomy procedures (surgical removal of gonads)</li>
</ul>

<p>If your study involves experimental approaches outside these categories, please contact us. We are actively working to expand the types of experimental designs ROSSA can accommodate, and your input will help guide these developments.</p>

<h4 id="animal-experimentation-setup">Setup</h4>
<p>The Animal Experimentation section begins with three yes/no questions asking whether your study involved:</p>
<ol>
    <li>Genetically modified animals</li>
    <li>Drug treatment(s)</li>
    <li>Gonadectomy procedure(s)</li>
</ol>
<p>Your responses to these questions will determine which subsequent data entry fields appear in this section.</p>

<h5>Important Note</h5>
<p>Even if you used wild-type animals with no genetic modification, you will still be asked to provide information about the animal species and strain in the Animal Models subsection. This ensures complete documentation of the biological materials used in your study.</p>

<h4 id="animal-models">Animal Models</h4>
<p>The Animal Models section captures detailed information about all animal models used in your study. This includes animal species, strain background, and the specific type of genetic modification (if applicable). Providing accurate information here ensures that other researchers can properly interpret your data and compare it with related studies.</p>

<h4>Preconfigured Animal Model Categories:</h4>
<p>ROSSA provides several predefined categories to describe the genetic status of your animal models. Select the category that best matches each model used in your study (Figure 9):</p>
<figure>
    <img src="{{ asset('images/guides/data-ingestion/09_GeneticModels.png')}}" alt="Genetic Models interface" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 9: Example of Displayed Cards for Preconfigured Genetic Animal Models.</strong></figcaption>
</figure>

<h5>No Genetic Modification</h5>
<p>Use this category for studies using a specific animal strain without any genetic modifications (wild-type animals on a defined genetic background).</p>

<h5>Global Modifications:</h5>
<p><strong>Global Knockin/Knockout - Gene Targeted:</strong> Constitutive (germline) knockout or knock-in created through targeted modification of an endogenous gene, resulting in altered gene function in all tissues throughout development and adulthood.</p>
<p><strong>Global Knockout - Spontaneous Mutation:</strong> Loss-of-function allele resulting from spontaneous mutation or induced by chemical or X-ray mutagenesis. These are randomly generated point mutations or deletions that have been mapped to a specific gene.</p>
<p><strong>Global Knockout - Insertional Mutagenesis:</strong> Loss-of-function allele created through random insertional disruption of an endogenous gene via gene trap, retroviral insertion, or transposon-mediated mutagenesis.</p>

<h5>Conditional Modifications:</h5>
<p><strong>Conditional Knockin/Knockout:</strong> Conditional (floxed) allele that allows tissue-specific or temporal control of gene function, typically activated when crossed with a Cre recombinase transgenic line to achieve lineage-specific or inducible loss-of-function or gain-of-function.</p>
<p><strong>Conditional Knockin - Safe Harbor:</strong> Conditional knock-in of a transgene into a "safe harbor" locus (such as Rosa26 or Col1a1), which allows for reliable transgene expression without disrupting endogenous gene function.</p>

<h5>Standard Transgenic</h5>
<p>Random genomic integration of a transgene, typically used for overexpression of a coding sequence under the control of tissue-specific or ubiquitous regulatory elements (promoter/enhancer sequences).</p>

<h4>Creating Animal Models:</h4>
<p>To add an animal model to your study (Figure 10):</p>
<ol>
    <li>Click the "Create New Animal Model" button</li>
    <li>You will be directed to a page displaying category cards corresponding to the preconfigured animal model categories described above</li>
    <li>Carefully review each card and select the one that best describes your animal model</li>
    <li>Complete the form that appears with detailed information about your animal model</li>
    <li>Once submitted, your animal model will appear in the Animal Models table</li>
</ol>
<figure>
    <img src="{{ asset('images/guides/data-ingestion/10_CreateAnimalModel.png')}}" alt="Genetic Models interface" class="img-fluid mb-3 border">
    <figcaption class="text-muted"><strong>Figure 10. Creating Animal Models.</strong> Clicking “Create New Animal Model” will take you to a page containing multiple cards, each designed for a specific genetic modification. Select the appropriate card, fill out the form, and save your animal model by clicking “Create Animal Model.” Upon doing this, your animal model should appear in the table. Repeat for additional animal models if necessary.</figcaption>
</figure>

<h5>Adding Multiple Animal Models</h5>
<p>If your study involves more than one animal model, simply repeat this process for each additional model. You can create as many animal models as necessary to fully represent your experimental design.</p>

<h5>Important Note for Conditional Models</h5>
<p>The conditional animal model forms include built-in fields for specifying the Cre recombinase line used to activate your conditional allele. If you crossed the same conditionally engineered animal (e.g., the same floxed allele) with multiple different Cre lines, you must create a separate animal model entry for each Cre line combination. For example, if you studied Gene fl/fl crossed with both Osteocalcin-Cre and Dmp1-Cre, create two separate conditional animal model entries-one for each cross.</p>