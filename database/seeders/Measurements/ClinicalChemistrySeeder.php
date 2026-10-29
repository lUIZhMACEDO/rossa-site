<?php

namespace Database\Seeders\Measurements;

use App\Enums\AnalysisTypeEnum;
use App\Models\AnalysisType;
use App\Models\AnalyteCategory;
use App\Models\Measurement;
use App\Models\SpecimenType;
use Illuminate\Database\Seeder;

class ClinicalChemistrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Clinical Chemistry measurements...');

        // analysis type
        $analysis_type = AnalysisType::firstWhere('code', AnalysisTypeEnum::CLINICAL_CHEMISTRY->value);

        // analyte categories
        $specimen_types = [
            'Serum' => [
                'Bone Turnover' => [
                    'name' => 'Bone Turnover',
                    'slug' => 'bone_turnover',
                    'grouping' => 'Bone Turnover',
                    'analytes' => [
                        'Alkaline Phosphatase (ALP)' => [
                            'name' => 'alkaline_phosphatase',
                            'display_name' => 'Alkaline Phosphatase',
                            'unit' => 'U/L',
                            'synonyms' => ['alkaline_phosphatase', 'Alkaline Phosphatase', 'alkaline phosphatase', 'Alkaline Phosphatase (ALP)', 'alkaline phosphatase (ALP)', 'ALP', 'ALP (U/L)'],
                        ],
                        'Bone Specific Alkaline Phosphatase (BALP)' => [
                            'name' => 'bone_specific_alkaline_phosphatase',
                            'display_name' => 'Bone Specific Alkaline Phosphatase (BALP)',
                            'unit' => 'U/L',
                            'synonyms' => ['bone_specific_alkaline_phosphatase', 'Bone Specific Alkaline Phosphatase', 'Bone Specific Alkaline Phosphatase (BALP)', 'bone specific alkaline phosphatase', 'BALP', 'BALP (U/L)'],
                        ],
                        'Osteocalcin (OC)' => [
                            'name' => 'osteocalcin',
                            'display_name' => 'Osteocalcin',
                            'unit' => 'ng/mL',
                            'synonyms' => ['osteocalcin', 'Osteocalcin', 'Osteocalcin (OC)', 'osteocalcin (OC)', 'OC', 'OC (ng/mL)'],
                        ],
                        'Procollagen type 1 N-terminal Propeptide (P1NP)' => [
                            'name' => 'p1np',
                            'display_name' => 'Procollagen type 1 N-terminal Propeptide (P1NP)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['p1np', 'P1NP', 'Procollagen type 1 N-terminal Propeptide', 'Procollagen type 1 N-terminal Propeptide (P1NP)', 'procollagen type 1 n-terminal propeptide', 'P1NP (ng/mL)'],
                        ],
                        'Procollagen type 1 C-terminal Propeptide (P1CP)' => [
                            'name' => 'p1cp',
                            'display_name' => 'Procollagen type 1 C-terminal Propeptide (P1CP)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['p1cp', 'P1CP', 'Procollagen type 1 C-terminal Propeptide', 'Procollagen type 1 C-terminal Propeptide (P1CP)', 'procollagen type 1 c-terminal propeptide', 'P1CP (ng/mL)'],
                        ],
                        'C-terminal Telopeptide of Type 1 Collagen (CTX-1)' => [
                            'name' => 'ctx_1',
                            'display_name' => 'C-terminal Telopeptide of Type 1 Collagen (CTX-1)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['ctx_1', 'CTX-1', 'CTX1', 'C-terminal Telopeptide of Type 1 Collagen', 'C-terminal Telopeptide of Type 1 Collagen (CTX-1)', 'c-terminal telopeptide of type 1 collagen', 'CTX1 (ng/mL)', 'CTX-1 (ng/mL)'],
                        ],
                        'N-terminal Telopeptide of Type 1 Collagen (NTX-1)' => [
                            'name' => 'ntx_1',
                            'display_name' => 'N-terminal Telopeptide of Type 1 Collagen (NTX-1)',
                            'unit' => 'nmol BCE/mmol creatinine',
                            'synonyms' => ['ntx_1', 'NTX-1', 'NTX1', 'N-terminal Telopeptide of Type 1 Collagen', 'N-terminal Telopeptide of Type 1 Collagen (NTX-1)', 'n-terminal telopeptide of type 1 collagen', 'NTX-1 (nmol BCE/mmol creatinine)', 'NTX1 (nmol BCE/mmol creatinine)'],
                        ],
                        'Bone Sialoprotein (IBSP)' => [
                            'name' => 'bone_sialoprotein',
                            'display_name' => 'Bone Sialoprotein (IBSP)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['bone_sialoprotein', 'Bone Sialoprotein', 'Bone Sialoprotein (IBSP)', 'bone sialoprotein', 'IBSP', 'IBSP (ng/mL)'],
                        ],
                        'Osteopontin (OP)' => [
                            'name' => 'osteopontin',
                            'display_name' => 'Osteopontin (OP)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['osteopontin', 'Osteopontin', 'Osteopontin (OP)', 'OP', 'OP (ng/mL)'],
                        ],
                        'Tartrate-resistant Acid Phosphatase 5b (TRAP 5b)' => [
                            'name' => 'trap_5b',
                            'display_name' => 'Tartrate-resistant Acid Phosphatase 5b (TRAP 5b)',
                            'unit' => 'U/L',
                            'synonyms' => ['trap_5b', 'TRAP 5b', 'Tartrate-resistant Acid Phosphatase 5b', 'Tartrate-resistant Acid Phosphatase 5b (TRAP 5b)', 'tartrate-resistant acid phosphatase 5b', 'TRAP5b', 'TRAP 5b (U/L)'],
                        ],
                        'Cathepsin K (CTSK)' => [
                            'name' => 'cathepsin_k',
                            'display_name' => 'Cathepsin K (CTSK)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['cathepsin_k', 'Cathepsin K', 'Cathepsin K (CTSK)', 'cathepsin k', 'CTSK', 'CTSK (ng/mL)'],
                        ],
                        'Receptor Activator of NF-κB Ligand (RANKL)' => [
                            'name' => 'rankl',
                            'display_name' => 'Receptor Activator of NF-κB Ligand (RANKL)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['rankl', 'RANKL', 'Receptor Activator of NF-κB Ligand', 'Receptor Activator of NF-κB Ligand (RANKL)', 'receptor activator of nf-kb ligand', 'RANKL (pg/mL)'],
                        ],
                        'Osteoprotegerin (OPG)' => [
                            'name' => 'osteoprotegerin',
                            'display_name' => 'Osteoprotegerin (OPG)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['osteoprotegerin', 'Osteoprotegerin', 'Osteoprotegerin (OPG)', 'OPG', 'OPG (pg/mL)'],
                        ],
                        'Dickkopf-1 (DKK-1)' => [
                            'name' => 'dickkopf_1',
                            'display_name' => 'Dickkopf-1 (DKK-1)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['dickkopf_1', 'Dickkopf-1', 'Dickkopf-1 (DKK-1)', 'dickkopf-1', 'DKK-1', 'DKK1', 'DKK-1 (ng/mL)'],
                        ],
                        'Sclerostin (SOST)' => [
                            'name' => 'sclerostin',
                            'display_name' => 'Sclerostin (SOST)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['sclerostin', 'Sclerostin', 'Sclerostin (SOST)', 'SOST', 'SOST (ng/mL)'],
                        ],
                    ],
                ],
                'Metabolic' => [
                    'name' => 'Metabolic',
                    'slug' => 'metabolic',
                    'grouping' => 'Metabolic',
                    'analytes' => [
                        'Parathyroid Hormone (PTH)' => [
                            'name' => 'parathyroid_hormone',
                            'display_name' => 'Parathyroid Hormone (PTH)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['parathyroid_hormone', 'Parathyroid Hormone', 'Parathyroid Hormone (PTH)', 'parathyroid hormone', 'PTH', 'PTH (pg/mL)'],
                        ],
                        'Calcitriol' => [
                            'name' => 'calcitriol',
                            'display_name' => 'Calcitriol',
                            'unit' => 'pg/mL',
                            'synonyms' => ['calcitriol', 'Calcitriol', 'Calcitriol (pg/mL)', '1,25-dihydroxyvitamin D3', '1,25(OH)2D3'],
                        ],
                        '25-OH Vitamin D' => [
                            'name' => 'vitamin_d_25_oh',
                            'display_name' => '25-OH Vitamin D',
                            'unit' => 'ng/mL',
                            'synonyms' => ['vitamin_d_25_oh', '25-OH Vitamin D', '25-OH Vitamin D (ng/mL)', '25-hydroxyvitamin D', '25(OH)D', 'calcidiol'],
                        ],
                        'Calcitonin' => [
                            'name' => 'calcitonin',
                            'display_name' => 'Calcitonin',
                            'unit' => 'pg/mL',
                            'synonyms' => ['calcitonin', 'Calcitonin', 'Calcitonin (pg/mL)'],
                        ],
                        'Estradiol (E2)' => [
                            'name' => 'estradiol',
                            'display_name' => 'Estradiol (E2)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['estradiol', 'Estradiol', 'Estradiol (E2)', 'estradiol (E2)', 'E2', 'E2 (pg/mL)'],
                        ],
                        'Testosterone' => [
                            'name' => 'testosterone',
                            'display_name' => 'Testosterone',
                            'unit' => 'ng/dL',
                            'synonyms' => ['testosterone', 'Testosterone', 'Testosterone (ng/dL)'],
                        ],
                        'Growth Hormone' => [
                            'name' => 'growth_hormone',
                            'display_name' => 'Growth Hormone',
                            'unit' => 'ng/mL',
                            'synonyms' => ['growth_hormone', 'Growth Hormone', 'Growth Hormone (ng/mL)', 'growth hormone', 'GH', 'GH (ng/mL)', 'somatotropin'],
                        ],
                        'Insulin Growth Factor 1 (IGF-1)' => [
                            'name' => 'igf_1',
                            'display_name' => 'Insulin Growth Factor 1 (IGF-1)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['igf_1', 'IGF-1', 'Insulin Growth Factor 1', 'Insulin Growth Factor 1 (IGF-1)', 'insulin growth factor 1', 'IGF1', 'IGF-1 (ng/mL)'],
                        ],
                        'Thyroxine (T4)' => [
                            'name' => 'thyroxine',
                            'display_name' => 'Thyroxine (T4)',
                            'unit' => 'µg/dL',
                            'synonyms' => ['thyroxine', 'Thyroxine', 'Thyroxine (T4)', 'thyroxine (T4)', 'T4', 'T4 (µg/dL)'],
                        ],
                        'Triiodothyronine (T3)' => [
                            'name' => 'triiodothyronine',
                            'display_name' => 'Triiodothyronine (T3)',
                            'unit' => 'ng/dL',
                            'synonyms' => ['triiodothyronine', 'Triiodothyronine', 'Triiodothyronine (T3)', 'triiodothyronine (T3)', 'T3', 'T3 (ng/dL)'],
                        ],
                        'Thyroid-Stimulating Hormone (TSH)' => [
                            'name' => 'thyroid_stimulating_hormone',
                            'display_name' => 'Thyroid-Stimulating Hormone (TSH)',
                            'unit' => 'µIU/mL',
                            'synonyms' => ['thyroid_stimulating_hormone', 'Thyroid-Stimulating Hormone', 'Thyroid-Stimulating Hormone (TSH)', 'thyroid stimulating hormone', 'TSH', 'TSH (µIU/mL)', 'thyrotropin'],
                        ],
                        'Cortisol' => [
                            'name' => 'cortisol',
                            'display_name' => 'Cortisol',
                            'unit' => 'µg/dL',
                            'synonyms' => ['cortisol', 'Cortisol', 'Cortisol (µg/dL)', 'hydrocortisone'],
                        ],
                        'Insulin' => [
                            'name' => 'insulin',
                            'display_name' => 'Insulin',
                            'unit' => 'µU/mL',
                            'synonyms' => ['insulin', 'Insulin', 'Insulin (µU/mL)', 'insulin (µU/mL)'],
                        ],
                        'Leptin' => [
                            'name' => 'leptin',
                            'display_name' => 'Leptin',
                            'unit' => 'ng/mL',
                            'synonyms' => ['leptin', 'Leptin', 'Leptin (ng/mL)', 'leptin (ng/mL)'],
                        ],
                        'Glucose' => [
                            'name' => 'glucose',
                            'display_name' => 'Glucose',
                            'unit' => 'mg/dL',
                            'synonyms' => ['glucose', 'Glucose', 'Glucose (mg/dL)', 'blood glucose', 'blood sugar'],
                        ],
                        'Glucose-dependent Insulinotropic Polypeptide (GIP)' => [
                            'name' => 'gip',
                            'display_name' => 'Glucose-dependent Insulinotropic Polypeptide (GIP)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['gip', 'GIP', 'Glucose-dependent Insulinotropic Polypeptide', 'Glucose-dependent Insulinotropic Polypeptide (GIP)', 'glucose-dependent insulinotropic polypeptide', 'GIP (pg/mL)'],
                        ],
                        'Glucagon-like Peptide-1 (GLP-1)' => [
                            'name' => 'glp_1',
                            'display_name' => 'Glucagon-like Peptide-1 (GLP-1)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['glp_1', 'GLP-1', 'Glucagon-like Peptide-1', 'Glucagon-like Peptide-1 (GLP-1)', 'glucagon-like peptide-1', 'GLP1', 'GLP-1 (pg/mL)'],
                        ],
                        'Glucagon-like Peptide-2 (GLP-2)' => [
                            'name' => 'glp_2',
                            'display_name' => 'Glucagon-like Peptide-2 (GLP-2)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['glp_2', 'GLP-2', 'Glucagon-like Peptide-2', 'Glucagon-like Peptide-2 (GLP-2)', 'glucagon-like peptide-2', 'GLP2', 'GLP-2 (pg/mL)'],
                        ],
                        'Fibroblast Growth Factor 23 (FGF-23)' => [
                            'name' => 'fgf_23',
                            'display_name' => 'Fibroblast Growth Factor 23 (FGF-23)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['fgf_23', 'FGF-23', 'Fibroblast Growth Factor 23', 'Fibroblast Growth Factor 23 (FGF-23)', 'fibroblast growth factor 23', 'FGF23', 'FGF-23 (pg/mL)'],
                        ],
                        'Serotonin' => [
                            'name' => 'serotonin',
                            'display_name' => 'Serotonin',
                            'unit' => 'ng/mL',
                            'synonyms' => ['serotonin', 'Serotonin', 'Serotonin (ng/mL)', '5-hydroxytryptamine', '5-HT'],
                        ],
                        'Calcium' => [
                            'name' => 'calcium',
                            'display_name' => 'Calcium',
                            'unit' => 'mg/dL',
                            'synonyms' => ['calcium', 'Calcium', 'Calcium (mg/dL)', 'serum calcium', 'Ca'],
                        ],
                        'Phosphorus' => [
                            'name' => 'phosphorus',
                            'display_name' => 'Phosphorus',
                            'unit' => 'mg/dL',
                            'synonyms' => ['phosphorus', 'Phosphorus', 'Phosphorus (mg/dL)', 'phosphate', 'serum phosphorus', 'P'],
                        ],
                        'Iron' => [
                            'name' => 'iron',
                            'display_name' => 'Iron',
                            'unit' => 'µg/dL',
                            'synonyms' => ['iron', 'Iron', 'Iron (µg/dL)', 'serum iron', 'Fe'],
                        ],
                        'Creatinine' => [
                            'name' => 'creatinine',
                            'display_name' => 'Creatinine',
                            'unit' => 'mg/dL',
                            'synonyms' => ['creatinine', 'Creatinine', 'Creatinine (mg/dL)', 'serum creatinine'],
                        ],
                    ],
                ],
                'Cytokines' => [
                    'name' => 'Cytokines',
                    'slug' => 'cytokines',
                    'grouping' => 'Cytokines',
                    'analytes' => [
                        'Transforming Growth Factor beta 1 (TGFβ1)' => [
                            'name' => 'tgf_beta_1',
                            'display_name' => 'Transforming Growth Factor beta 1 (TGFβ1)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['tgf_beta_1', 'TGFβ1', 'Transforming Growth Factor beta 1', 'Transforming Growth Factor beta 1 (TGFβ1)', 'transforming growth factor beta 1', 'TGF-β1', 'TGF-beta1', 'TGFβ1 (pg/mL)'],
                        ],
                        'Tumor Necrosis Factor alpha (TNFα)' => [
                            'name' => 'tnf_alpha',
                            'display_name' => 'Tumor Necrosis Factor alpha (TNFα)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['tnf_alpha', 'TNFα', 'Tumor Necrosis Factor alpha', 'Tumor Necrosis Factor alpha (TNFα)', 'tumor necrosis factor alpha', 'TNF-α', 'TNF-alpha', 'TNFα (pg/mL)', 'TNF-alpha (pg/mL)'],
                        ],
                        'Interleukin 1 beta (IL-1β)' => [
                            'name' => 'il_1_beta',
                            'display_name' => 'Interleukin 1 beta (IL-1β)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_1_beta', 'IL-1β', 'Interleukin 1 beta', 'Interleukin 1 beta (IL-1β)', 'interleukin 1 beta', 'IL1β', 'IL-1β (pg/mL)'],
                        ],
                        'Interleukin 4 (IL-4)' => [
                            'name' => 'il_4',
                            'display_name' => 'Interleukin 4 (IL-4)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_4', 'IL-4', 'Interleukin 4', 'Interleukin 4 (IL-4)', 'interleukin 4', 'IL4', 'IL-4 (pg/mL)'],
                        ],
                        'Interleukin 6 (IL-6)' => [
                            'name' => 'il_6',
                            'display_name' => 'Interleukin 6 (IL-6)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_6', 'IL-6', 'Interleukin 6', 'Interleukin 6 (IL-6)', 'interleukin 6', 'IL6', 'IL-6 (pg/mL)', 'IL6 (pg/mL)'],
                        ],
                        'Interleukin 7 (IL-7)' => [
                            'name' => 'il_7',
                            'display_name' => 'Interleukin 7 (IL-7)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_7', 'IL-7', 'Interleukin 7', 'Interleukin 7 (IL-7)', 'interleukin 7', 'IL7', 'IL-7 (pg/mL)'],
                        ],
                        'Interleukin 8 (IL-8)' => [
                            'name' => 'il_8',
                            'display_name' => 'Interleukin 8 (IL-8)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_8', 'IL-8', 'Interleukin 8', 'Interleukin 8 (IL-8)', 'interleukin 8', 'IL8', 'IL-8 (pg/mL)'],
                        ],
                        'Interleukin 10 (IL-10)' => [
                            'name' => 'il_10',
                            'display_name' => 'Interleukin 10 (IL-10)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_10', 'IL-10', 'Interleukin 10', 'Interleukin 10 (IL-10)', 'interleukin 10', 'IL10', 'IL-10 (pg/mL)'],
                        ],
                        'Interleukin 12 (IL-12)' => [
                            'name' => 'il_12',
                            'display_name' => 'Interleukin 12 (IL-12)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_12', 'IL-12', 'Interleukin 12', 'Interleukin 12 (IL-12)', 'interleukin 12', 'IL12', 'IL-12 (pg/mL)'],
                        ],
                        'Interleukin 21 (IL-21)' => [
                            'name' => 'il_21',
                            'display_name' => 'Interleukin 21 (IL-21)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_21', 'IL-21', 'Interleukin 21', 'Interleukin 21 (IL-21)', 'interleukin 21', 'IL21', 'IL-21 (pg/mL)'],
                        ],
                        'Interleukin 23 (IL-23)' => [
                            'name' => 'il_23',
                            'display_name' => 'Interleukin 23 (IL-23)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_23', 'IL-23', 'Interleukin 23', 'Interleukin 23 (IL-23)', 'interleukin 23', 'IL23', 'IL-23 (pg/mL)'],
                        ],
                        'Interferon gamma (IFNγ)' => [
                            'name' => 'interferon_gamma',
                            'display_name' => 'Interferon gamma (IFNγ)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['interferon_gamma', 'IFNγ', 'Interferon gamma', 'Interferon gamma (IFNγ)', 'interferon gamma', 'IFN-γ', 'IFNG', 'IFNγ (pg/mL)'],
                        ],
                        'GM-CSF' => [
                            'name' => 'gm_csf',
                            'display_name' => 'GM-CSF',
                            'unit' => 'pg/mL',
                            'synonyms' => ['gm_csf', 'GM-CSF', 'Granulocyte-Macrophage Colony-Stimulating Factor', 'granulocyte-macrophage colony-stimulating factor', 'GM-CSF (pg/mL)'],
                        ],
                        'M-CSF' => [
                            'name' => 'm_csf',
                            'display_name' => 'M-CSF',
                            'unit' => 'pg/mL',
                            'synonyms' => ['m_csf', 'M-CSF', 'Macrophage Colony-Stimulating Factor', 'macrophage colony-stimulating factor', 'M-CSF (pg/mL)'],
                        ],
                        'MCP-1' => [
                            'name' => 'mcp_1',
                            'display_name' => 'MCP-1',
                            'unit' => 'pg/mL',
                            'synonyms' => ['mcp_1', 'MCP-1', 'Monocyte Chemoattractant Protein-1', 'monocyte chemoattractant protein-1', 'CCL2', 'MCP-1 (pg/mL)'],
                        ],
                        'Bradykinin' => [
                            'name' => 'bradykinin',
                            'display_name' => 'Bradykinin',
                            'unit' => 'pg/mL',
                            'synonyms' => ['bradykinin', 'Bradykinin', 'Bradykinin (pg/mL)'],
                        ],
                        'Prostaglandin E2 (PGE2)' => [
                            'name' => 'pge2',
                            'display_name' => 'Prostaglandin E2 (PGE2)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['pge2', 'PGE2', 'Prostaglandin E2', 'Prostaglandin E2 (PGE2)', 'prostaglandin e2', 'PGE2 (pg/mL)'],
                        ],
                        'Adiponectin' => [
                            'name' => 'adiponectin',
                            'display_name' => 'Adiponectin',
                            'unit' => 'µg/mL',
                            'synonyms' => ['adiponectin', 'Adiponectin', 'Adiponectin (µg/mL)', 'ACRP30', 'AdipoQ'],
                        ],
                        'Oncostatin M (OSM)' => [
                            'name' => 'oncostatin_m',
                            'display_name' => 'Oncostatin M (OSM)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['oncostatin_m', 'OSM', 'Oncostatin M', 'Oncostatin M (OSM)', 'oncostatin m', 'OSM (pg/mL)'],
                        ],
                        'Chemokine Ligand 5/RANTES' => [
                            'name' => 'rantes',
                            'display_name' => 'Chemokine Ligand 5/RANTES',
                            'unit' => 'pg/mL',
                            'synonyms' => ['rantes', 'RANTES', 'Chemokine Ligand 5', 'Chemokine Ligand 5/RANTES', 'chemokine ligand 5', 'CCL5', 'RANTES (pg/mL)'],
                        ],
                    ],
                ],
                'Lipid Metabolism' => [
                    'name' => 'Lipid Metabolism',
                    'slug' => 'lipid_metabolism',
                    'grouping' => 'Lipid Metabolism',
                    'analytes' => [
                        'Total Cholesterol' => [
                            'name' => 'total_cholesterol',
                            'display_name' => 'Total Cholesterol',
                            'unit' => 'mg/dL',
                            'synonyms' => ['total_cholesterol', 'Total Cholesterol', 'Total Cholesterol (mg/dL)', 'total cholesterol', 'total cholesterol (mg/dL)'],
                        ],
                        'High-Density Lipoprotein (HDL-C)' => [
                            'name' => 'hdl_cholesterol',
                            'display_name' => 'High-Density Lipoprotein (HDL-C)',
                            'unit' => 'mg/dL',
                            'synonyms' => ['hdl_cholesterol', 'HDL Cholesterol', 'High-Density Lipoprotein', 'High-Density Lipoprotein (HDL-C)', 'high-density lipoprotein', 'HDL-C', 'HDL', 'HDL Cholesterol (mg/dL)', 'HDL-C (mg/dL)'],
                        ],
                        'Low-Density Lipoprotein (LDL-C)' => [
                            'name' => 'ldl_cholesterol',
                            'display_name' => 'Low-Density Lipoprotein (LDL-C)',
                            'unit' => 'mg/dL',
                            'synonyms' => ['ldl_cholesterol', 'LDL Cholesterol', 'Low-Density Lipoprotein', 'Low-Density Lipoprotein (LDL-C)', 'low-density lipoprotein', 'LDL-C', 'LDL', 'LDL Cholesterol (mg/dL)', 'LDL-C (mg/dL)'],
                        ],
                        'Very Low-Density Lipoprotein (VLDL-C)' => [
                            'name' => 'vldl_cholesterol',
                            'display_name' => 'Very Low-Density Lipoprotein (VLDL-C)',
                            'unit' => 'mg/dL',
                            'synonyms' => ['vldl_cholesterol', 'VLDL Cholesterol', 'Very Low-Density Lipoprotein', 'Very Low-Density Lipoprotein (VLDL-C)', 'very low-density lipoprotein', 'VLDL-C', 'VLDL', 'VLDL Cholesterol (mg/dL)', 'VLDL-C (mg/dL)'],
                        ],
                        'Triglycerides (TG)' => [
                            'name' => 'triglycerides',
                            'display_name' => 'Triglycerides (TG)',
                            'unit' => 'mg/dL',
                            'synonyms' => ['triglycerides', 'Triglycerides', 'Triglycerides (TG)', 'triglycerides (TG)', 'TG', 'Triglycerides (mg/dL)', 'TG (mg/dL)'],
                        ],
                        'Free Fatty Acids (FFA)' => [
                            'name' => 'free_fatty_acids',
                            'display_name' => 'Free Fatty Acids (FFA)',
                            'unit' => 'µmol/L',
                            'synonyms' => ['free_fatty_acids', 'Free Fatty Acids', 'Free Fatty Acids (FFA)', 'free fatty acids', 'FFA', 'Free Fatty Acids (µmol/L)', 'FFA (µmol/L)'],
                        ],
                        'Phospholipids' => [
                            'name' => 'phospholipids',
                            'display_name' => 'Phospholipids',
                            'unit' => 'µmol/L',
                            'synonyms' => ['phospholipids', 'Phospholipids', 'Phospholipids (µmol/L)'],
                        ],
                        'Apolipoprotein A1 (ApoA1)' => [
                            'name' => 'apolipoprotein_a1',
                            'display_name' => 'Apolipoprotein A1 (ApoA1)',
                            'unit' => 'mg/dL',
                            'synonyms' => ['apolipoprotein_a1', 'Apolipoprotein A1', 'Apolipoprotein A1 (ApoA1)', 'apolipoprotein a1', 'ApoA1', 'ApoA-1', 'Apo A1', 'ApoA1 (mg/dL)'],
                        ],
                        'Apolipoprotein B (ApoB)' => [
                            'name' => 'apolipoprotein_b',
                            'display_name' => 'Apolipoprotein B (ApoB)',
                            'unit' => 'mg/dL',
                            'synonyms' => ['apolipoprotein_b', 'Apolipoprotein B', 'Apolipoprotein B (ApoB)', 'apolipoprotein b', 'ApoB', 'ApoB-100', 'Apo B', 'ApoB (mg/dL)'],
                        ],
                    ],
                ],
            ],
            'Plasma' => [
                'Bone Turnover' => [
                    'name' => 'Bone Turnover',
                    'slug' => 'bone_turnover',
                    'grouping' => 'Bone Turnover',
                    'analytes' => [
                        'Alkaline Phosphatase (ALP)' => [
                            'name' => 'alkaline_phosphatase',
                            'display_name' => 'Alkaline Phosphatase',
                            'unit' => 'U/L',
                            'synonyms' => ['alkaline_phosphatase', 'Alkaline Phosphatase', 'alkaline phosphatase', 'Alkaline Phosphatase (ALP)', 'alkaline phosphatase (ALP)', 'ALP', 'ALP (U/L)', 'plasma_alkaline_phosphatase'],
                        ],
                        'Bone Specific Alkaline Phosphatase (BALP)' => [
                            'name' => 'bone_specific_alkaline_phosphatase',
                            'display_name' => 'Bone Specific Alkaline Phosphatase (BALP)',
                            'unit' => 'U/L',
                            'synonyms' => ['bone_specific_alkaline_phosphatase', 'Bone Specific Alkaline Phosphatase', 'Bone Specific Alkaline Phosphatase (BALP)', 'bone specific alkaline phosphatase', 'BALP', 'BALP (U/L)', 'plasma_bone_specific_alkaline_phosphatase'],
                        ],
                        'Osteocalcin (OC)' => [
                            'name' => 'osteocalcin',
                            'display_name' => 'Osteocalcin',
                            'unit' => 'ng/mL',
                            'synonyms' => ['osteocalcin', 'Osteocalcin', 'Osteocalcin (OC)', 'osteocalcin (OC)', 'OC', 'OC (ng/mL)', 'plasma_osteocalcin'],
                        ],
                        'Procollagen type 1 N-terminal Propeptide (P1NP)' => [
                            'name' => 'p1np',
                            'display_name' => 'Procollagen type 1 N-terminal Propeptide (P1NP)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['p1np', 'P1NP', 'Procollagen type 1 N-terminal Propeptide', 'Procollagen type 1 N-terminal Propeptide (P1NP)', 'procollagen type 1 n-terminal propeptide', 'P1NP (ng/mL)', 'plasma_p1np'],
                        ],
                        'Procollagen type 1 C-terminal Propeptide (P1CP)' => [
                            'name' => 'p1cp',
                            'display_name' => 'Procollagen type 1 C-terminal Propeptide (P1CP)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['p1cp', 'P1CP', 'Procollagen type 1 C-terminal Propeptide', 'Procollagen type 1 C-terminal Propeptide (P1CP)', 'procollagen type 1 c-terminal propeptide', 'P1CP (ng/mL)', 'plasma_p1cp'],
                        ],
                        'C-terminal Telopeptide of Type 1 Collagen (CTX-1)' => [
                            'name' => 'ctx_1',
                            'display_name' => 'C-terminal Telopeptide of Type 1 Collagen (CTX-1)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['ctx_1', 'CTX-1', 'CTX1', 'C-terminal Telopeptide of Type 1 Collagen', 'C-terminal Telopeptide of Type 1 Collagen (CTX-1)', 'c-terminal telopeptide of type 1 collagen', 'CTX1 (ng/mL)', 'CTX-1 (ng/mL)', 'plasma_ctx_1'],
                        ],
                        'N-terminal Telopeptide of Type 1 Collagen (NTX-1)' => [
                            'name' => 'ntx_1',
                            'display_name' => 'N-terminal Telopeptide of Type 1 Collagen (NTX-1)',
                            'unit' => 'nmol BCE/mmol creatinine',
                            'synonyms' => ['ntx_1', 'NTX-1', 'NTX1', 'N-terminal Telopeptide of Type 1 Collagen', 'N-terminal Telopeptide of Type 1 Collagen (NTX-1)', 'n-terminal telopeptide of type 1 collagen', 'NTX-1 (nmol BCE/mmol creatinine)', 'NTX1 (nmol BCE/mmol creatinine)', 'plasma_ntx_1'],
                        ],
                        'Bone Sialoprotein (IBSP)' => [
                            'name' => 'bone_sialoprotein',
                            'display_name' => 'Bone Sialoprotein (IBSP)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['bone_sialoprotein', 'Bone Sialoprotein', 'Bone Sialoprotein (IBSP)', 'bone sialoprotein', 'IBSP', 'IBSP (ng/mL)', 'plasma_bone_sialoprotein'],
                        ],
                        'Osteopontin (OP)' => [
                            'name' => 'osteopontin',
                            'display_name' => 'Osteopontin (OP)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['osteopontin', 'Osteopontin', 'Osteopontin (OP)', 'OP', 'OP (ng/mL)', 'plasma_osteopontin'],
                        ],
                        'Tartrate-resistant Acid Phosphatase 5b (TRAP 5b)' => [
                            'name' => 'trap_5b',
                            'display_name' => 'Tartrate-resistant Acid Phosphatase 5b (TRAP 5b)',
                            'unit' => 'U/L',
                            'synonyms' => ['trap_5b', 'TRAP 5b', 'Tartrate-resistant Acid Phosphatase 5b', 'Tartrate-resistant Acid Phosphatase 5b (TRAP 5b)', 'tartrate-resistant acid phosphatase 5b', 'TRAP5b', 'TRAP 5b (U/L)', 'plasma_trap_5b'],
                        ],
                        'Cathepsin K (CTSK)' => [
                            'name' => 'cathepsin_k',
                            'display_name' => 'Cathepsin K (CTSK)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['cathepsin_k', 'Cathepsin K', 'Cathepsin K (CTSK)', 'cathepsin k', 'CTSK', 'CTSK (ng/mL)', 'plasma_cathepsin_k'],
                        ],
                        'Receptor Activator of NF-κB Ligand (RANKL)' => [
                            'name' => 'rankl',
                            'display_name' => 'Receptor Activator of NF-κB Ligand (RANKL)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['rankl', 'RANKL', 'Receptor Activator of NF-κB Ligand', 'Receptor Activator of NF-κB Ligand (RANKL)', 'receptor activator of nf-kb ligand', 'RANKL (pg/mL)', 'plasma_rankl'],
                        ],
                        'Osteoprotegerin (OPG)' => [
                            'name' => 'osteoprotegerin',
                            'display_name' => 'Osteoprotegerin (OPG)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['osteoprotegerin', 'Osteoprotegerin', 'Osteoprotegerin (OPG)', 'OPG', 'OPG (pg/mL)', 'plasma_osteoprotegerin'],
                        ],
                        'Dickkopf-1 (DKK-1)' => [
                            'name' => 'dickkopf_1',
                            'display_name' => 'Dickkopf-1 (DKK-1)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['dickkopf_1', 'Dickkopf-1', 'Dickkopf-1 (DKK-1)', 'dickkopf-1', 'DKK-1', 'DKK1', 'DKK-1 (ng/mL)', 'plasma_dickkopf_1'],
                        ],
                        'Sclerostin (SOST)' => [
                            'name' => 'sclerostin',
                            'display_name' => 'Sclerostin (SOST)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['sclerostin', 'Sclerostin', 'Sclerostin (SOST)', 'SOST', 'SOST (ng/mL)', 'plasma_sclerostin'],
                        ],
                    ],
                ],
                'Metabolic' => [
                    'name' => 'Metabolic',
                    'slug' => 'metabolic',
                    'grouping' => 'Metabolic',
                    'analytes' => [
                        'Parathyroid Hormone (PTH)' => [
                            'name' => 'parathyroid_hormone',
                            'display_name' => 'Parathyroid Hormone (PTH)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['parathyroid_hormone', 'Parathyroid Hormone', 'Parathyroid Hormone (PTH)', 'parathyroid hormone', 'PTH', 'PTH (pg/mL)', 'plasma_parathyroid_hormone'],
                        ],
                        'Calcitriol' => [
                            'name' => 'calcitriol',
                            'display_name' => 'Calcitriol',
                            'unit' => 'pg/mL',
                            'synonyms' => ['calcitriol', 'Calcitriol', 'Calcitriol (pg/mL)', '1,25-dihydroxyvitamin D3', '1,25(OH)2D3', 'plasma_calcitriol'],
                        ],
                        '25-OH Vitamin D' => [
                            'name' => 'vitamin_d_25_oh',
                            'display_name' => '25-OH Vitamin D',
                            'unit' => 'ng/mL',
                            'synonyms' => ['vitamin_d_25_oh', '25-OH Vitamin D', '25-OH Vitamin D (ng/mL)', '25-hydroxyvitamin D', '25(OH)D', 'calcidiol', 'plasma_vitamin_d_25_oh'],
                        ],
                        'Calcitonin' => [
                            'name' => 'calcitonin',
                            'display_name' => 'Calcitonin',
                            'unit' => 'pg/mL',
                            'synonyms' => ['calcitonin', 'Calcitonin', 'Calcitonin (pg/mL)', 'plasma_calcitonin'],
                        ],
                        'Estradiol (E2)' => [
                            'name' => 'estradiol',
                            'display_name' => 'Estradiol (E2)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['estradiol', 'Estradiol', 'Estradiol (E2)', 'estradiol (E2)', 'E2', 'E2 (pg/mL)', 'plasma_estradiol'],
                        ],
                        'Testosterone' => [
                            'name' => 'testosterone',
                            'display_name' => 'Testosterone',
                            'unit' => 'ng/dL',
                            'synonyms' => ['testosterone', 'Testosterone', 'Testosterone (ng/dL)', 'plasma_testosterone'],
                        ],
                        'Growth Hormone' => [
                            'name' => 'growth_hormone',
                            'display_name' => 'Growth Hormone',
                            'unit' => 'ng/mL',
                            'synonyms' => ['growth_hormone', 'Growth Hormone', 'Growth Hormone (ng/mL)', 'growth hormone', 'GH', 'GH (ng/mL)', 'somatotropin', 'plasma_growth_hormone'],
                        ],
                        'Insulin Growth Factor 1 (IGF-1)' => [
                            'name' => 'igf_1',
                            'display_name' => 'Insulin Growth Factor 1 (IGF-1)',
                            'unit' => 'ng/mL',
                            'synonyms' => ['igf_1', 'IGF-1', 'Insulin Growth Factor 1', 'Insulin Growth Factor 1 (IGF-1)', 'insulin growth factor 1', 'IGF1', 'IGF-1 (ng/mL)', 'plasma_igf_1'],
                        ],
                        'Thyroxine (T4)' => [
                            'name' => 'thyroxine',
                            'display_name' => 'Thyroxine (T4)',
                            'unit' => 'µg/dL',
                            'synonyms' => ['thyroxine', 'Thyroxine', 'Thyroxine (T4)', 'thyroxine (T4)', 'T4', 'T4 (µg/dL)', 'plasma_thyroxine'],
                        ],
                        'Triiodothyronine (T3)' => [
                            'name' => 'triiodothyronine',
                            'display_name' => 'Triiodothyronine (T3)',
                            'unit' => 'ng/dL',
                            'synonyms' => ['triiodothyronine', 'Triiodothyronine', 'Triiodothyronine (T3)', 'triiodothyronine (T3)', 'T3', 'T3 (ng/dL)', 'plasma_triiodothyronine'],
                        ],
                        'Thyroid-Stimulating Hormone (TSH)' => [
                            'name' => 'thyroid_stimulating_hormone',
                            'display_name' => 'Thyroid-Stimulating Hormone (TSH)',
                            'unit' => 'µIU/mL',
                            'synonyms' => ['thyroid_stimulating_hormone', 'Thyroid-Stimulating Hormone', 'Thyroid-Stimulating Hormone (TSH)', 'thyroid stimulating hormone', 'TSH', 'TSH (µIU/mL)', 'thyrotropin', 'plasma_thyroid_stimulating_hormone'],
                        ],
                        'Cortisol' => [
                            'name' => 'cortisol',
                            'display_name' => 'Cortisol',
                            'unit' => 'µg/dL',
                            'synonyms' => ['cortisol', 'Cortisol', 'Cortisol (µg/dL)', 'hydrocortisone', 'plasma_cortisol'],
                        ],
                        'Insulin' => [
                            'name' => 'insulin',
                            'display_name' => 'Insulin',
                            'unit' => 'µU/mL',
                            'synonyms' => ['insulin', 'Insulin', 'Insulin (µU/mL)', 'insulin (µU/mL)', 'plasma_insulin'],
                        ],
                        'Leptin' => [
                            'name' => 'leptin',
                            'display_name' => 'Leptin',
                            'unit' => 'ng/mL',
                            'synonyms' => ['leptin', 'Leptin', 'Leptin (ng/mL)', 'leptin (ng/mL)', 'plasma_leptin'],
                        ],
                        'Glucose' => [
                            'name' => 'glucose',
                            'display_name' => 'Glucose',
                            'unit' => 'mg/dL',
                            'synonyms' => ['glucose', 'Glucose', 'Glucose (mg/dL)', 'blood glucose', 'blood sugar', 'plasma_glucose'],
                        ],
                        'Glucose-dependent Insulinotropic Polypeptide (GIP)' => [
                            'name' => 'gip',
                            'display_name' => 'Glucose-dependent Insulinotropic Polypeptide (GIP)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['gip', 'GIP', 'Glucose-dependent Insulinotropic Polypeptide', 'Glucose-dependent Insulinotropic Polypeptide (GIP)', 'glucose-dependent insulinotropic polypeptide', 'GIP (pg/mL)', 'plasma_gip'],
                        ],
                        'Glucagon-like Peptide-1 (GLP-1)' => [
                            'name' => 'glp_1',
                            'display_name' => 'Glucagon-like Peptide-1 (GLP-1)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['glp_1', 'GLP-1', 'Glucagon-like Peptide-1', 'Glucagon-like Peptide-1 (GLP-1)', 'glucagon-like peptide-1', 'GLP1', 'GLP-1 (pg/mL)', 'plasma_glp_1'],
                        ],
                        'Glucagon-like Peptide-2 (GLP-2)' => [
                            'name' => 'glp_2',
                            'display_name' => 'Glucagon-like Peptide-2 (GLP-2)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['glp_2', 'GLP-2', 'Glucagon-like Peptide-2', 'Glucagon-like Peptide-2 (GLP-2)', 'glucagon-like peptide-2', 'GLP2', 'GLP-2 (pg/mL)', 'plasma_glp_2'],
                        ],
                        'Fibroblast Growth Factor 23 (FGF-23)' => [
                            'name' => 'fgf_23',
                            'display_name' => 'Fibroblast Growth Factor 23 (FGF-23)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['fgf_23', 'FGF-23', 'Fibroblast Growth Factor 23', 'Fibroblast Growth Factor 23 (FGF-23)', 'fibroblast growth factor 23', 'FGF23', 'FGF-23 (pg/mL)', 'plasma_fgf_23'],
                        ],
                        'Serotonin' => [
                            'name' => 'serotonin',
                            'display_name' => 'Serotonin',
                            'unit' => 'ng/mL',
                            'synonyms' => ['serotonin', 'Serotonin', 'Serotonin (ng/mL)', '5-hydroxytryptamine', '5-HT', 'plasma_serotonin'],
                        ],
                        'Calcium' => [
                            'name' => 'calcium',
                            'display_name' => 'Calcium',
                            'unit' => 'mg/dL',
                            'synonyms' => ['calcium', 'Calcium', 'Calcium (mg/dL)', 'serum calcium', 'Ca', 'plasma_calcium'],
                        ],
                        'Phosphorus' => [
                            'name' => 'phosphorus',
                            'display_name' => 'Phosphorus',
                            'unit' => 'mg/dL',
                            'synonyms' => ['phosphorus', 'Phosphorus', 'Phosphorus (mg/dL)', 'phosphate', 'serum phosphorus', 'P', 'plasma_phosphorus'],
                        ],
                        'Iron' => [
                            'name' => 'iron',
                            'display_name' => 'Iron',
                            'unit' => 'µg/dL',
                            'synonyms' => ['iron', 'Iron', 'Iron (µg/dL)', 'serum iron', 'Fe', 'plasma_iron'],
                        ],
                        'Creatinine' => [
                            'name' => 'creatinine',
                            'display_name' => 'Creatinine',
                            'unit' => 'mg/dL',
                            'synonyms' => ['creatinine', 'Creatinine', 'Creatinine (mg/dL)', 'serum creatinine', 'plasma_creatinine'],
                        ],
                    ],
                ],
                'Cytokines' => [
                    'name' => 'Cytokines',
                    'slug' => 'cytokines',
                    'grouping' => 'Cytokines',
                    'analytes' => [
                        'Transforming Growth Factor beta 1 (TGFβ1)' => [
                            'name' => 'tgf_beta_1',
                            'display_name' => 'Transforming Growth Factor beta 1 (TGFβ1)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['tgf_beta_1', 'TGFβ1', 'Transforming Growth Factor beta 1', 'Transforming Growth Factor beta 1 (TGFβ1)', 'transforming growth factor beta 1', 'TGF-β1', 'TGF-beta1', 'TGFβ1 (pg/mL)', 'plasma_tgf_beta_1'],
                        ],
                        'Tumor Necrosis Factor alpha (TNFα)' => [
                            'name' => 'tnf_alpha',
                            'display_name' => 'Tumor Necrosis Factor alpha (TNFα)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['tnf_alpha', 'TNFα', 'Tumor Necrosis Factor alpha', 'Tumor Necrosis Factor alpha (TNFα)', 'tumor necrosis factor alpha', 'TNF-α', 'TNF-alpha', 'TNFα (pg/mL)', 'TNF-alpha (pg/mL)', 'plasma_tnf_alpha'],
                        ],
                        'Interleukin 1 beta (IL-1β)' => [
                            'name' => 'il_1_beta',
                            'display_name' => 'Interleukin 1 beta (IL-1β)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_1_beta', 'IL-1β', 'Interleukin 1 beta', 'Interleukin 1 beta (IL-1β)', 'interleukin 1 beta', 'IL1β', 'IL-1β (pg/mL)', 'plasma_il_1_beta'],
                        ],
                        'Interleukin 4 (IL-4)' => [
                            'name' => 'il_4',
                            'display_name' => 'Interleukin 4 (IL-4)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_4', 'IL-4', 'Interleukin 4', 'Interleukin 4 (IL-4)', 'interleukin 4', 'IL4', 'IL-4 (pg/mL)', 'plasma_il_4'],
                        ],
                        'Interleukin 6 (IL-6)' => [
                            'name' => 'il_6',
                            'display_name' => 'Interleukin 6 (IL-6)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_6', 'IL-6', 'Interleukin 6', 'Interleukin 6 (IL-6)', 'interleukin 6', 'IL6', 'IL-6 (pg/mL)', 'IL6 (pg/mL)', 'plasma_il_6'],
                        ],
                        'Interleukin 7 (IL-7)' => [
                            'name' => 'il_7',
                            'display_name' => 'Interleukin 7 (IL-7)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_7', 'IL-7', 'Interleukin 7', 'Interleukin 7 (IL-7)', 'interleukin 7', 'IL7', 'IL-7 (pg/mL)', 'plasma_il_7'],
                        ],
                        'Interleukin 8 (IL-8)' => [
                            'name' => 'il_8',
                            'display_name' => 'Interleukin 8 (IL-8)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_8', 'IL-8', 'Interleukin 8', 'Interleukin 8 (IL-8)', 'interleukin 8', 'IL8', 'IL-8 (pg/mL)', 'plasma_il_8'],
                        ],
                        'Interleukin 10 (IL-10)' => [
                            'name' => 'il_10',
                            'display_name' => 'Interleukin 10 (IL-10)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_10', 'IL-10', 'Interleukin 10', 'Interleukin 10 (IL-10)', 'interleukin 10', 'IL10', 'IL-10 (pg/mL)', 'plasma_il_10'],
                        ],
                        'Interleukin 12 (IL-12)' => [
                            'name' => 'il_12',
                            'display_name' => 'Interleukin 12 (IL-12)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_12', 'IL-12', 'Interleukin 12', 'Interleukin 12 (IL-12)', 'interleukin 12', 'IL12', 'IL-12 (pg/mL)', 'plasma_il_12'],
                        ],
                        'Interleukin 21 (IL-21)' => [
                            'name' => 'il_21',
                            'display_name' => 'Interleukin 21 (IL-21)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_21', 'IL-21', 'Interleukin 21', 'Interleukin 21 (IL-21)', 'interleukin 21', 'IL21', 'IL-21 (pg/mL)', 'plasma_il_21'],
                        ],
                        'Interleukin 23 (IL-23)' => [
                            'name' => 'il_23',
                            'display_name' => 'Interleukin 23 (IL-23)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['il_23', 'IL-23', 'Interleukin 23', 'Interleukin 23 (IL-23)', 'interleukin 23', 'IL23', 'IL-23 (pg/mL)', 'plasma_il_23'],
                        ],
                        'Interferon gamma (IFNγ)' => [
                            'name' => 'interferon_gamma',
                            'display_name' => 'Interferon gamma (IFNγ)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['interferon_gamma', 'IFNγ', 'Interferon gamma', 'Interferon gamma (IFNγ)', 'interferon gamma', 'IFN-γ', 'IFNG', 'IFNγ (pg/mL)', 'plasma_interferon_gamma'],
                        ],
                        'GM-CSF' => [
                            'name' => 'gm_csf',
                            'display_name' => 'GM-CSF',
                            'unit' => 'pg/mL',
                            'synonyms' => ['gm_csf', 'GM-CSF', 'Granulocyte-Macrophage Colony-Stimulating Factor', 'granulocyte-macrophage colony-stimulating factor', 'GM-CSF (pg/mL)', 'plasma_gm_csf'],
                        ],
                        'M-CSF' => [
                            'name' => 'm_csf',
                            'display_name' => 'M-CSF',
                            'unit' => 'pg/mL',
                            'synonyms' => ['m_csf', 'M-CSF', 'Macrophage Colony-Stimulating Factor', 'macrophage colony-stimulating factor', 'M-CSF (pg/mL)', 'plasma_m_csf'],
                        ],
                        'MCP-1' => [
                            'name' => 'mcp_1',
                            'display_name' => 'MCP-1',
                            'unit' => 'pg/mL',
                            'synonyms' => ['mcp_1', 'MCP-1', 'Monocyte Chemoattractant Protein-1', 'monocyte chemoattractant protein-1', 'CCL2', 'MCP-1 (pg/mL)', 'plasma_mcp_1'],
                        ],
                        'Bradykinin' => [
                            'name' => 'bradykinin',
                            'display_name' => 'Bradykinin',
                            'unit' => 'pg/mL',
                            'synonyms' => ['bradykinin', 'Bradykinin', 'Bradykinin (pg/mL)', 'plasma_bradykinin'],
                        ],
                        'Prostaglandin E2 (PGE2)' => [
                            'name' => 'pge2',
                            'display_name' => 'Prostaglandin E2 (PGE2)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['pge2', 'PGE2', 'Prostaglandin E2', 'Prostaglandin E2 (PGE2)', 'prostaglandin e2', 'PGE2 (pg/mL)', 'plasma_pge2'],
                        ],
                        'Adiponectin' => [
                            'name' => 'adiponectin',
                            'display_name' => 'Adiponectin',
                            'unit' => 'µg/mL',
                            'synonyms' => ['adiponectin', 'Adiponectin', 'Adiponectin (µg/mL)', 'ACRP30', 'AdipoQ', 'plasma_adiponectin'],
                        ],
                        'Oncostatin M (OSM)' => [
                            'name' => 'oncostatin_m',
                            'display_name' => 'Oncostatin M (OSM)',
                            'unit' => 'pg/mL',
                            'synonyms' => ['oncostatin_m', 'OSM', 'Oncostatin M', 'Oncostatin M (OSM)', 'oncostatin m', 'OSM (pg/mL)', 'plasma_oncostatin_m'],
                        ],
                        'Chemokine Ligand 5/RANTES' => [
                            'name' => 'rantes',
                            'display_name' => 'Chemokine Ligand 5/RANTES',
                            'unit' => 'pg/mL',
                            'synonyms' => ['rantes', 'RANTES', 'Chemokine Ligand 5', 'Chemokine Ligand 5/RANTES', 'chemokine ligand 5', 'CCL5', 'RANTES (pg/mL)', 'plasma_rantes'],
                        ],
                    ],
                ],
                'Lipid Metabolism' => [
                    'name' => 'Lipid Metabolism',
                    'slug' => 'lipid_metabolism',
                    'grouping' => 'Lipid Metabolism',
                    'analytes' => [
                        'Total Cholesterol' => [
                            'name' => 'total_cholesterol',
                            'display_name' => 'Total Cholesterol',
                            'unit' => 'mg/dL',
                            'synonyms' => ['total_cholesterol', 'Total Cholesterol', 'Total Cholesterol (mg/dL)', 'total cholesterol', 'total cholesterol (mg/dL)', 'plasma_total_cholesterol'],
                        ],
                        'High-Density Lipoprotein (HDL-C)' => [
                            'name' => 'hdl_cholesterol',
                            'display_name' => 'High-Density Lipoprotein (HDL-C)',
                            'unit' => 'mg/dL',
                            'synonyms' => ['hdl_cholesterol', 'HDL Cholesterol', 'High-Density Lipoprotein', 'High-Density Lipoprotein (HDL-C)', 'high-density lipoprotein', 'HDL-C', 'HDL', 'HDL Cholesterol (mg/dL)', 'HDL-C (mg/dL)', 'plasma_hdl_cholesterol'],
                        ],
                        'Low-Density Lipoprotein (LDL-C)' => [
                            'name' => 'ldl_cholesterol',
                            'display_name' => 'Low-Density Lipoprotein (LDL-C)',
                            'unit' => 'mg/dL',
                            'synonyms' => ['ldl_cholesterol', 'LDL Cholesterol', 'Low-Density Lipoprotein', 'Low-Density Lipoprotein (LDL-C)', 'low-density lipoprotein', 'LDL-C', 'LDL', 'LDL Cholesterol (mg/dL)', 'LDL-C (mg/dL)', 'plasma_ldl_cholesterol'],
                        ],
                        'Very Low-Density Lipoprotein (VLDL-C)' => [
                            'name' => 'vldl_cholesterol',
                            'display_name' => 'Very Low-Density Lipoprotein (VLDL-C)',
                            'unit' => 'mg/dL',
                            'synonyms' => ['vldl_cholesterol', 'VLDL Cholesterol', 'Very Low-Density Lipoprotein', 'Very Low-Density Lipoprotein (VLDL-C)', 'very low-density lipoprotein', 'VLDL-C', 'VLDL', 'VLDL Cholesterol (mg/dL)', 'VLDL-C (mg/dL)', 'plasma_vldl_cholesterol'],
                        ],
                        'Triglycerides (TG)' => [
                            'name' => 'triglycerides',
                            'display_name' => 'Triglycerides (TG)',
                            'unit' => 'mg/dL',
                            'synonyms' => ['triglycerides', 'Triglycerides', 'Triglycerides (TG)', 'triglycerides (TG)', 'TG', 'Triglycerides (mg/dL)', 'TG (mg/dL)', 'plasma_triglycerides'],
                        ],
                        'Free Fatty Acids (FFA)' => [
                            'name' => 'free_fatty_acids',
                            'display_name' => 'Free Fatty Acids (FFA)',
                            'unit' => 'µmol/L',
                            'synonyms' => ['free_fatty_acids', 'Free Fatty Acids', 'Free Fatty Acids (FFA)', 'free fatty acids', 'FFA', 'Free Fatty Acids (µmol/L)', 'FFA (µmol/L)', 'plasma_free_fatty_acids'],
                        ],
                        'Phospholipids' => [
                            'name' => 'phospholipids',
                            'display_name' => 'Phospholipids',
                            'unit' => 'µmol/L',
                            'synonyms' => ['phospholipids', 'Phospholipids', 'Phospholipids (µmol/L)', 'plasma_phospholipids'],
                        ],
                        'Apolipoprotein A1 (ApoA1)' => [
                            'name' => 'apolipoprotein_a1',
                            'display_name' => 'Apolipoprotein A1 (ApoA1)',
                            'unit' => 'mg/dL',
                            'synonyms' => ['apolipoprotein_a1', 'Apolipoprotein A1', 'Apolipoprotein A1 (ApoA1)', 'apolipoprotein a1', 'ApoA1', 'ApoA-1', 'Apo A1', 'ApoA1 (mg/dL)', 'plasma_apolipoprotein_a1'],
                        ],
                        'Apolipoprotein B (ApoB)' => [
                            'name' => 'apolipoprotein_b',
                            'display_name' => 'Apolipoprotein B (ApoB)',
                            'unit' => 'mg/dL',
                            'synonyms' => ['apolipoprotein_b', 'Apolipoprotein B', 'Apolipoprotein B (ApoB)', 'apolipoprotein b', 'ApoB', 'ApoB-100', 'Apo B', 'ApoB (mg/dL)', 'plasma_apolipoprotein_b'],
                        ],
                    ],
                ],
            ],
            'Urine' => [
                'Bone Turnover' => [
                    'name' => 'Bone Turnover',
                    'slug' => 'bone_turnover',
                    'grouping' => 'Bone Turnover',
                    'analytes' => [
                        'Pyridinoline (PYD)' => [
                            'name' => 'pyridinoline',
                            'display_name' => 'Pyridinoline (PYD)',
                            'unit' => 'nmol/mmol creatinine',
                            'synonyms' => ['pyridinoline', 'Pyridinoline', 'pyridinoline', 'Pyridinoline (PYD)', 'pyridinoline (PYD)', 'PYD', 'PYD (nmol/mmol creatinine)'],
                        ],
                        'Deoxypyridinoline (DPD)' => [
                            'name' => 'deoxypyridinoline',
                            'display_name' => 'Deoxypyridinoline (DPD)',
                            'unit' => 'nmol/mmol creatinine',
                            'synonyms' => ['deoxypyridinoline', 'Deoxypyridinoline', 'deoxypyridinoline', 'Deoxypyridinoline (DPD)', 'deoxypyridinoline (DPD)', 'DPD', 'DPD (nmol/mmol creatinine)'],
                        ],
                        'Hydroxyproline (HYP)' => [
                            'name' => 'hydroxyproline',
                            'display_name' => 'Hydroxyproline (HYP)',
                            'unit' => 'µmol/L',
                            'synonyms' => ['hydroxyproline', 'Hydroxyproline', 'Hydroxyproline (HYP)', 'hydroxyproline (HYP)', 'HYP', 'HYP (µmol/L)'],
                        ],
                        'Hydroxylysine (HYL)' => [
                            'name' => 'hydroxylysine',
                            'display_name' => 'Hydroxylysine (HYL)',
                            'unit' => 'µmol/L',
                            'synonyms' => ['hydroxylysine', 'Hydroxylysine', 'Hydroxylysine (HYL)', 'hydroxylysine (HYL)', 'HYL', 'HYL (µmol/L)'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($specimen_types as $name => $categories) {
            $specimenType = SpecimenType::updateOrCreate(
                ['name' => $name],
                ['specimen_category' => 'fluid']
            );
            foreach ($categories as $category_slug => $category_data) {
                // create or get analyte category
                $analyte_category = AnalyteCategory::updateOrCreate(
                    [
                        'slug' => $category_data['slug'],
                        'specimen_type_id' => $specimenType->id ?? null,
                    ],
                    [
                        'name' => $category_data['name'],
                    ]
                );
                \Log::info('Analyte Category: '.$analyte_category->id.' - '.$analyte_category->name);

                foreach ($category_data['analytes'] as $analyte_name => $analyte_data) {
                    // first check for existing measurement - old seeding
                    $existingMeasurement = Measurement::where('name', $analyte_data['name'])
                        ->where('analysis_type_id', $analysis_type->id)
                        ->whereNull('specimen_type_id')
                        ->first();

                    if ($existingMeasurement) {
                        // update measurement
                        $existingMeasurement->update([
                            'specimen_type_id' => $specimenType->id,
                            'display_name' => $analyte_data['display_name'],
                            'unit' => $analyte_data['unit'],
                            'synonyms' => $analyte_data['synonyms'],
                            'grouping' => strtoupper($category_data['name']),
                            'analyte_category_id' => $analyte_category->id,
                        ]);
                    } else {
                        // create measurement or update existing
                        Measurement::updateOrCreate(
                            [
                                'name' => $analyte_data['name'],
                                'analysis_type_id' => $analysis_type->id,
                                'specimen_type_id' => $specimenType->id,
                            ],
                            [
                                'display_name' => $analyte_data['display_name'],
                                'unit' => $analyte_data['unit'],
                                'synonyms' => $analyte_data['synonyms'],
                                'grouping' => strtoupper($category_data['name']),
                                'analyte_category_id' => $analyte_category->id,
                            ]
                        );
                    }
                }
            }
        }

        $this->command->info('Clinical Chemistry measurements seeded successfully.');
    }
}
