## Downloading/Loading ChEMBL Molecules

### Step 1: Download Molecules from ChEMBL

Use the following command to download molecule CSV files:

```bash
php artisan rossa:download-molecules [URL]
```

> **As of 1/18/2026**, the following URL can be used:
>
> ```
> https://www.ebi.ac.uk/chembl/interface_api/delayed_jobs/outputs/DOWNLOAD-heRwUfDRJj-nAMSjmA31y8RyPmnAG6YgOpVpkx4anHU_eq_/DOWNLOAD-heRwUfDRJj-nAMSjmA31y8RyPmnAG6YgOpVpkx4anHU_eq_.zip
> ```
>
> In the case that this URL expires or becomes invalid, follow the below steps to obtain a new download URL.

**Getting the Download URL:**

1. Navigate to https://www.ebi.ac.uk/chembl/explore/compounds/
2. Click the "CSV" download button
3. When the "DOWNLOAD FILE" button appears, **do not click it**
4. Open your browser's developer tools (F12)
5. Inspect the "DOWNLOAD FILE" button
6. Extract the download URL from the button's attributes

The URL will be in this format:

```
https://www.ebi.ac.uk/chembl/interface_api/delayed_jobs/outputs/[CONTENT_HASH]/[CONTENT_HASH].zip
```

**What this command does:**

-   Downloads the .zip file from the provided URL
-   Extracts the .csv contents into `database/data/chembl_molecules/`
-   Removes the zip file after extraction

### Step 2: Load Molecules into the Database

Once the CSV files are downloaded, process them with:

```bash
php artisan rossa:process-molecules
```

This command will:

-   Scan the `database/data/chembl_molecules/` directory for CSV files
-   Parse and validate the molecule data
-   Insert new molecules into the database (skips duplicates)

---

A full list of fields that are contained within the CSV file is shows by the following table:

| Property ID                               |              Label | Type    |
| ----------------------------------------- | -----------------: | :------ |
| molecule_chembl_id                        |          ChEMBL ID | string  |
| pref_name                                 |               Name | string  |
| molecule_synonyms                         |           Synonyms | object  |
| molecule_type                             |               Type | string  |
| max_phase                                 |          Max Phase | string  |
| molecule_properties.full_mwt              |   Molecular Weight | double  |
| \_metadata.related_targets.count          |            Targets | integer |
| \_metadata.related_activities.count       |      Bioactivities | integer |
| molecule_properties.alogp                 |              AlogP | double  |
| molecule_properties.psa                   | Polar Surface Area | double  |
| molecule_properties.hba                   |                HBA | integer |
| molecule_properties.hbd                   |                HBD | integer |
| molecule_properties.num_ro5_violations    |    #RO5 Violations | integer |
| molecule_properties.rtb                   |   #Rotatable Bonds | integer |
| molecule_properties.ro3_pass              |         Passes Ro3 | string  |
| molecule_properties.qed_weighted          |       QED Weighted | double  |
| molecule_properties.aromatic_rings        |     Aromatic Rings | integer |
| structure_type                            |     Structure Type | string  |
| inorganic_flag                            |     Inorganic Flag | integer |
| molecule_properties.heavy_atoms           |        Heavy Atoms | integer |
| molecule_properties.np_likeness_score     |  Np Likeness Score | double  |
| molecule_properties.full_molformula       |  Molecular Formula | string  |
| molecule_structures.canonical_smiles      |             Smiles | string  |
| molecule_structures.standard_inchi_key    |          Inchi Key | string  |
| molecule_structures.standard_inchi        |              Inchi | string  |
| withdrawn_flag                            |     Withdrawn Flag | boolean |
| orphan                                    |             Orphan | integer |
| \_metadata.compound_records.compound_key  |        Records Key | string  |
| \_metadata.compound_records.compound_name |       Records Name | string  |

**Data we collect from ChEMBL**

-   ChEMBL ID
-   Name
-   Type
-   Max Phase
-   Molecular Formula
-   Synonyms

## Molecule Targets Investigation

As shown by the table above, we are able to collect data from the `Targets` property, which corresponds to `_metadata.related_targets.count`. This piece of data does not us provide any insight into what the targets actually are.

An example entry from the ChEMBL compounds CSV for `TAMOXIFEN`:

> **Note:** `...` in the JSON below indicates that results were truncated for brevity.

```json
[
	/*  0:  ChEMBL ID          */ "CHEMBL83",
	/*  1:  Name               */ "TAMOXIFEN",
	/*  2:  Synonyms           */ "ICI-47699|MAMMATON|NOVALDEX|NSC-727681|TAMOPLEX|TAMOXIFEN|TAMOXIFENE|TAMOXIFENO",
	/*  3:  Type               */ "Small molecule",
	/*  4:  Max Phase          */ "4.0",
	/*  5:  Molecular Weight   */ "371.52",
	/*  6:  Targets            */ "357",
	/*  7:  Bioactivities      */ "4145",
	/*  8:  AlogP              */ "6.00",
	/*  9:  Polar Surface Area */ "12.47",
	/* 10:  HBA                */ "2",
	/* 11:  HBD                */ "0",
	/* 12:  #RO5 Violations    */ "1",
	/* 13:  #Rotatable Bonds   */ "8",
	/* 14:  Passes Ro3         */ "N",
	/* 15:  QED Weighted       */ "0.45",
	/* 16:  Aromatic Rings     */ "3",
	/* 17:  Structure Type     */ "MOL",
	/* 18:  Inorganic Flag     */ "0",
	/* 19:  Heavy Atoms        */ "28",
	/* 20:  Np Likeness Score  */ "-0.40",
	/* 21:  Molecular Formula  */ "C26H29NO",
	/* 22:  Smiles             */ "CC/C(=C(\\c1ccccc1)c1ccc(OCCN(C)C)cc1)c1ccccc1",
	/* 23:  Inchi Key          */ "NKANXQFJJICGDU-QPLCGJKRSA-N",
	/* 24:  Inchi              */ "InChI=1S/C26H29NO/c1-4-25(21-11-7-5-8-12-21)26(22-13-9-6-10-14-22)23-15-17-24(18-16-23)28-20-19-27(2)3/h5-18H,4,19-20H2,1-3H3/b26-25-",
	/* 25:  Withdrawn Flag     */ "False",
	/* 26:  Orphan             */ "0",
	/* 27:  Records Key        */ "['Tamoxifen', 'tamoxifen', 'Tam', ...]",
	/* 28:  Records Name       */ "['{2-[4-((Z)-1,2-Diphenyl-but-1-enyl)-phenoxy]-ethyl}-dimethyl-amine', ...]"
]
```

By this entry, we can see that TAMOXIFEN has 357 targets, but we do not know what they are.

### Exploring Targets via ChEMBL GUI

Let's use the ChEMBL web interface to search for TAMOXIFEN and explore its targets:

https://www.ebi.ac.uk/chembl/explore/compound/CHEMBL83

**Findings:**

-   Searching the page for `357` yields no results
-   Searching for `target` reveals targets listed under the **Drug Mechanisms** section

**Example Target:**

```
Target ChEMBL ID: CHEMBL206
Target Name: Estrogen receptor
```

**Target Summary Section:**

The page also includes a **Target Summary** section with a pie chart showing the distribution of target types for TAMOXIFEN. This section lists **416 total targets**.

> **Discrepancy:** The CSV file listed `357` targets, but the web interface shows `416` targets for `TAMOXIFEN`.

### ChEMBL Targets CSV File

ChEMBL provides a separate CSV file for downloading target data:

https://www.ebi.ac.uk/chembl/explore/targets/

**Target CSV Fields:**

| Property ID                         | Label              | Type    |
| ----------------------------------- | ------------------ | ------- |
| target_chembl_id                    | ChEMBL ID          | string  |
| pref_name                           | Name               | string  |
| uniprot_accessions                  | Accessions         | object  |
| target_type                         | Type               | string  |
| organism                            | Organism           | string  |
| \_metadata.related_compounds.count  | Compounds          | integer |
| \_metadata.related_activities.count | Activities         | integer |
| tax_id                              | Tax ID             | string  |
| species_group_flag                  | Species Group Flag | boolean |

### Limitation

**There is no way to associate targets back to molecules using only the CSV files provided by ChEMBL.** The molecule CSV contains a target count but no target identifiers, and the target CSV contains compound counts but no compound identifiers.
