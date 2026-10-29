<?php

namespace Database\Seeders\Measurements;

use App\Enums\AnalysisTypeEnum;
use App\Models\AnalysisType;
use App\Models\Measurement;
use App\Models\Region;
use Illuminate\Database\Seeder;

class BoneHistoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding BoneHisto measurements...');
        $instrument = AnalysisType::firstWhere('code', AnalysisTypeEnum::BONE_HISTO->value);

        $this->command->info('Starting to define arrays...'); // ← Add this
        $bonehisto_trabecular_measurements = [

            // ============================================
            // STRUCTURAL TRABECULAR BONE PARAMETERS
            // ============================================

            [
                'name' => 'bone_volume_over_tissue_volume',
                'display_name' => 'Bone Volume/Tissue Volume (BV/TV)',
                'unit' => '%',
                'synonyms' => ['BV/TV', 'bone_volume_over_tissue_volume', 'bv_tv', 'BV_TV', 'BVTV',
                    'BV TV', 'Bone Volume/Tissue Volume', 'Bone Volume Fraction', 'bone volume fraction',
                    'Trabecular Bone Volume', 'trabecular bone volume', 'BV/TV (%)', 'BV/TV%', 'Bone Volume (%)',
                    'Bone Volume', 'bone_volume', 'BvTv', 'Bv/Tv', 'bv/tv', 'BV per TV', 'Bone Volume per Tissue Volume',
                    'Percent Bone Volume', 'percent bone volume'],
                'region' => 'trabecular',
                'grouping' => 'STRUCTURAL TRABECULAR BONE PARAMETERS',
            ],
            [
                'name' => 'trabecular_thickness',
                'display_name' => 'Trabecular Thickness (Tb.Th)',
                'unit' => 'µm',
                'synonyms' => ['Tb.Th', 'trabecular_thickness', 'TbTh', 'Tb.Th.', 'Tb Th', 'Trabecular Thickness',
                    'Trabecular Thickness (µm)', 'Trabecular Thickness (um)', 'Trabecular Thickness (microns)',
                    'trabecular thickness', 'trabecular_thickness_um', 'Trab.Th', 'Trab Th', 'Tb_Th', 'tb.th', 'tb_th'],
                'region' => 'trabecular',
                'grouping' => 'STRUCTURAL TRABECULAR BONE PARAMETERS',
            ],
            [
                'name' => 'trabecular_number',
                'display_name' => 'Trabecular Number (Tb.N)',
                'unit' => '#/mm',
                'synonyms' => ['Tb.N', 'trabecular_number', 'TbN', 'Tb.N.', 'Tb N', 'Trabecular Number', 'Trabecular Number (1/mm)',
                    'Trabecular Number (#/mm)', 'Trabecular Number (mm-1)', 'Trabecular Number (mm^-1)', 'trabecular number',
                    'trabecular_number_per_mm', 'Trab.N', 'Trab N', 'Tb_N', 'tb.n', 'tb_n', 'Trabecular Density', 'trabecular density', 'Tb.N (1/mm)'],
                'region' => 'trabecular',
                'grouping' => 'STRUCTURAL TRABECULAR BONE PARAMETERS',
            ],
            [
                'name' => 'trabecular_spacing',
                'display_name' => 'Trabecular Spacing (Tb.Sp)',
                'unit' => 'µm',
                'synonyms' => ['Tb.Sp', 'trabecular_spacing', 'TbSp', 'Tb.Sp.', 'Tb Sp', 'Trabecular Spacing', 'Trabecular Separation', 'trabecular separation',
                    'Trabecular Spacing (µm)', 'Trabecular Spacing (um)', 'Trabecular Spacing (microns)', 'trabecular spacing', 'trabecular_spacing_um', 'Trab.Sp',
                    'Trab Sp', 'Tb_Sp', 'tb.sp', 'tb_sp', 'Tb.Sep', 'Trabecular Distance'],
                'region' => 'trabecular',
                'grouping' => 'STRUCTURAL TRABECULAR BONE PARAMETERS',
            ],
            [
                'name' => 'tissue_area',
                'display_name' => 'Tissue Area (T.Ar.)',
                'unit' => 'mm2',
                'synonyms' => ['T.Ar', 'tissue_area', 'TAr', 'T.Ar.', 'T Ar', 'Tissue Area', 'Tissue Area (mm2)', 'Tissue Area (mm²)', 'Tissue Area (mm^2)',
                    'tissue area', 'tissue_area_mm2', 'Tiss.Ar', 'Tiss Ar', 'T_Ar', 't.ar', 't_ar', 'TV', 'Tissue Volume', 'Total Tissue Area'],
                'region' => 'trabecular',
                'grouping' => 'STRUCTURAL TRABECULAR BONE PARAMETERS',
            ],
            [
                'name' => 'tissue_volume',
                'display_name' => 'Tissue Volume (TV)',
                'unit' => 'mm2',
                'synonyms' => ['TV', 'tissue_volume', 'T.V', 'T.V.', 'T V', 'Tissue Volume', 'Tissue Volume (mm3)', 'Tissue Volume (mm³)', 'Tissue Volume (mm^3)',
                    'tissue volume', 'tissue_volume_mm3', 'Tiss.V', 'Tiss V', 'T_V', 'tv', 't.v', 't_v', 'Total Tissue Volume', 'total tissue volume'],
                'region' => 'trabecular',
                'grouping' => 'STRUCTURAL TRABECULAR BONE PARAMETERS',
            ],
            [
                'name' => 'bone_area',
                'display_name' => 'Bone Area (B.Ar)',
                'unit' => 'mm2',
                'synonyms' => ['B.Ar', 'bone_area', 'BAr', 'B.Ar.', 'B Ar', 'Bone Area', 'Bone Area (mm2)', 'Bone Area (mm²)', 'Bone Area (mm^2)',
                    'bone area', 'bone_area_mm2', 'B_Ar', 'b.ar', 'b_ar', 'BV', 'Bone Volume', 'Trabecular Bone Area', 'trabecular bone area'],
                'region' => 'trabecular',
                'grouping' => 'STRUCTURAL TRABECULAR BONE PARAMETERS',
            ],
            [
                'name' => 'bone_volume',
                'display_name' => 'Bone Volume (BV)',
                'unit' => 'mm3',
                'synonyms' => ['BV', 'bone_volume', 'B.V', 'B.V.', 'B V', 'Bone Volume', 'Bone Volume (mm3)', 'Bone Volume (mm³)', 'Bone Volume (mm^3)',
                    'bone volume', 'bone_volume_mm3', 'B_V', 'bv', 'b.v', 'b_v', 'Trabecular Bone Volume', 'trabecular bone volume', 'Total Bone Volume'],
                'region' => 'trabecular',
                'grouping' => 'STRUCTURAL TRABECULAR BONE PARAMETERS',
            ],
            [
                'name' => 'bone_surface_over_bone_volume',
                'display_name' => 'Bone Surface/Bone Volume (BS/BV)',
                'unit' => 'mm2/mm3',
                'synonyms' => ['BS/BV', 'bone_surface_bone_volume', 'BS_BV', 'BSBV', 'BS BV', 'Bone Surface/Bone Volume', 'Bone Surface per Bone Volume',
                    'bone surface/bone volume', 'Bone Surface to Bone Volume', 'BS/BV (mm-1)', 'BS/BV (mm^-1)', 'BS/BV (1/mm)', 'Specific Bone Surface',
                    'specific bone surface', 'Bone Surface Density', 'bs/bv', 'Bs/Bv'],
                'region' => 'trabecular',
                'grouping' => 'STRUCTURAL TRABECULAR BONE PARAMETERS',
            ],
            [
                'name' => 'bone_surface_over_tissue_volume',
                'display_name' => 'Bone Surface/Bone Volume (BS/TV)',
                'unit' => 'mm2/mm3',
                'synonyms' => ['BS/TV', 'bone_surface_over_tissue_volume', 'bone_surface_tissue_volume', 'BS_TV', 'BSTV', 'BS TV', 'Bone Surface/Tissue Volume',
                    'Bone Surface per Tissue Volume', 'bone surface/tissue volume', 'Bone Surface to Tissue Volume', 'BS/TV (mm-1)', 'BS/TV (mm^-1)', 'BS/TV (1/mm)',
                    'BS/TV (mm2/mm3)', 'BS/TV (mm²/mm³)', 'bs/tv', 'Bs/Tv', 'Surface Density', 'surface density', 'Bone Surface Density', 'Trabecular Surface Density'],
                'region' => 'trabecular',
                'grouping' => 'STRUCTURAL TRABECULAR BONE PARAMETERS',
            ],

            // ============================================
            // STATIC FORMATION PARAMETERS
            // ============================================

            [
                'name' => 'osteoid_thickness',
                'display_name' => 'Osteoid Thickness (O.Th)',
                'unit' => 'mm2',
                'synonyms' => ['O.Th', 'osteoid_thickness', 'OTh', 'O.Th.', 'O Th', 'Osteoid Thickness', 'Osteoid Thickness (µm)', 'Osteoid Thickness (um)',
                    'Osteoid Thickness (microns)', 'osteoid thickness', 'osteoid_thickness_um', 'O_Th', 'o.th', 'o_th', 'Osteoid Width', 'osteoid width', 'O.Wi', 'Mean Osteoid Thickness'],
                'region' => 'trabecular',
                'grouping' => 'STATIC FORMATION PARAMETERS',
            ],
            [
                'name' => 'osteoid_surface_over_bone_surface',
                'display_name' => 'Osteoid Surface/Bone Surface (OS/BS)',
                'unit' => '%',
                'synonyms' => ['OS/BS', 'osteoid_surface_bone_surface', 'osteoid_surface', 'OS_BS', 'OSBS', 'OS BS', 'Osteoid Surface/Bone Surface',
                    'Osteoid Surface per Bone Surface', 'osteoid surface/bone surface', 'Osteoid Surface (%)', 'OS/BS (%)', 'OS/BS%', 'os/bs', 'Os/Bs',
                    'Osteoid Surface', 'osteoid surface', 'Percent Osteoid Surface', 'percent osteoid surface', 'O.S/B.S', 'O.S/BS'],
                'region' => 'trabecular',
                'grouping' => 'STATIC FORMATION PARAMETERS',
            ],
            [
                'name' => 'osteoid_volume_over_bone_volume',
                'display_name' => 'Osteoid Volume/Bone Volume (OV/BV)',
                'unit' => '%',
                'synonyms' => ['OV/BV', 'osteoid_volume_bone_volume', 'osteoid_volume', 'OV_BV', 'OVBV', 'OV BV', 'Osteoid Volume/Bone Volume',
                    'Osteoid Volume per Bone Volume', 'osteoid volume/bone volume', 'Osteoid Volume (%)', 'OV/BV (%)', 'OV/BV%', 'ov/bv', 'Ov/Bv',
                    'Osteoid Volume', 'osteoid volume', 'Percent Osteoid Volume', 'percent osteoid volume', 'O.V/B.V', 'O.V/BV'],
                'region' => 'trabecular',
                'grouping' => 'STATIC FORMATION PARAMETERS',
            ],
            [
                'name' => 'osteoblast_surface_over_bone_surface',
                'display_name' => 'Osteoblast Surface/Bone Surface (Ob.S/BS)',
                'unit' => '%',
                'synonyms' => ['Ob.S/BS', 'osteoblast_surface_bone_surface', 'osteoblast_surface', 'ObS/BS', 'Ob.S_BS', 'ObS_BS', 'ObSBS', 'Ob.S BS',
                    'Osteoblast Surface/Bone Surface', 'Osteoblast Surface per Bone Surface', 'osteoblast surface/bone surface', 'Osteoblast Surface (%)',
                    'Ob.S/BS (%)', 'Ob.S/BS%', 'ob.s/bs', 'Ob.s/Bs', 'Osteoblast Surface', 'osteoblast surface', 'Percent Osteoblast Surface', 'percent osteoblast surface',
                    'OB.S/BS', 'OBL.S/BS'],
                'region' => 'trabecular',
                'grouping' => 'STATIC FORMATION PARAMETERS',
            ],
            [
                'name' => 'osteoblast_number_over_bone_perimeter',
                'display_name' => 'Osteoblast Number/Bone Perimeter (N.Ob/B.Pm)',
                'unit' => '#/mm',
                'synonyms' => ['N.Ob/B.Pm', 'osteoblast_number_bone_perimeter', 'osteoblast_number', 'NOb/BPm', 'N.Ob_B.Pm', 'NOb_BPm', 'N Ob/B Pm', 'Osteoblast Number/Bone Perimeter',
                    'Osteoblast Number per Bone Perimeter', 'osteoblast number/bone perimeter', 'Osteoblast Number (#/mm)', 'N.Ob/B.Pm (#/mm)', 'n.ob/b.pm', 'N.Ob/BS', 'Osteoblast Number',
                    'osteoblast number', 'Ob.N/B.Pm', 'OB.N/B.Pm', 'OBL.N/B.Pm', 'Number of Osteoblasts'],
                'region' => 'trabecular',
                'grouping' => 'STATIC FORMATION PARAMETERS',
            ],

            // ============================================
            // STATIC RESORPTIVE PARAMETERS
            // ============================================

            [
                'name' => 'eroded_surface_over_bone_surface',
                'display_name' => 'Eroded Surface/Bone Surface (ES/BS)',
                'unit' => '%',
                'synonyms' => ['ES/BS', 'eroded_surface_bone_surface', 'eroded_surface', 'ES_BS', 'ESBS', 'ES BS', 'Eroded Surface/Bone Surface',
                    'Eroded Surface per Bone Surface', 'eroded surface/bone surface', 'Eroded Surface (%)', 'ES/BS (%)', 'ES/BS%', 'es/bs', 'Es/Bs',
                    'Eroded Surface', 'eroded surface', 'Percent Eroded Surface', 'percent eroded surface', 'E.S/B.S', 'E.S/BS', 'Resorption Surface',
                    'resorption surface', 'Lacunar Surface'],
                'region' => 'trabecular',
                'grouping' => 'STATIC RESORPTIVE PARAMETERS',
            ],
            [
                'name' => 'osteoclast_surface_over_bone_surface',
                'display_name' => 'Osteoclast Surface/Bone Surface (Oc.S/BS)',
                'unit' => '%',
                'synonyms' => ['Oc.S/BS', 'osteoclast_surface_bone_surface', 'osteoclast_surface', 'OcS/BS', 'Oc.S_BS', 'OcS_BS', 'OcSBS', 'Oc.S BS',
                    'Osteoclast Surface/Bone Surface', 'Osteoclast Surface per Bone Surface', 'osteoclast surface/bone surface', 'Osteoclast Surface (%)',
                    'Oc.S/BS (%)', 'Oc.S/BS%', 'oc.s/bs', 'Oc.s/Bs', 'Osteoclast Surface', 'osteoclast surface', 'Percent Osteoclast Surface', 'percent osteoclast surface',
                    'OC.S/BS', 'OCL.S/BS'],
                'region' => 'trabecular',
                'grouping' => 'STATIC RESORPTIVE PARAMETERS',
            ],
            [
                'name' => 'osteoclast_number_over_bone_perimeter',
                'display_name' => 'Osteoclast Number/Bone Perimeter (N.Oc/B.Pm)',
                'unit' => '#/mm',
                'synonyms' => ['N.Oc/B.Pm', 'osteoclast_number_bone_perimeter', 'osteoclast_number', 'NOc/BPm', 'N.Oc_B.Pm', 'NOc_BPm', 'N Oc/B Pm',
                    'Osteoclast Number/Bone Perimeter', 'Osteoclast Number per Bone Perimeter', 'osteoclast number/bone perimeter', 'Osteoclast Number (#/mm)',
                    'N.Oc/B.Pm (#/mm)', 'n.oc/b.pm', 'N.Oc/BS', 'Osteoclast Number', 'osteoclast number', 'Oc.N/B.Pm', 'OC.N/B.Pm', 'OCL.N/B.Pm', 'Number of Osteoclasts'],
                'region' => 'trabecular',
                'grouping' => 'STATIC RESORPTIVE PARAMETERS',
            ],

            // ============================================
            // STATIC QUIESCENT PARAMETERS
            // ============================================

            [
                'name' => 'quiescent_surface_over_bone_surface',
                'display_name' => 'Quiescent Surface/Bone Surface (QS/BS)',
                'unit' => '%',
                'synonyms' => ['QS/BS', 'quiescent_surface_bone_surface', 'quiescent_surface', 'QS_BS', 'QSBS', 'QS BS', 'Quiescent Surface/Bone Surface',
                    'Quiescent Surface per Bone Surface', 'quiescent surface/bone surface', 'Quiescent Surface (%)', 'QS/BS (%)', 'QS/BS%', 'qs/bs', 'Qs/Bs',
                    'Quiescent Surface', 'quiescent surface', 'Percent Quiescent Surface', 'percent quiescent surface', 'Q.S/B.S', 'Q.S/BS', 'Resting Surface',
                    'resting surface', 'Inactive Surface'],
                'region' => 'trabecular',
                'grouping' => 'STATIC QUIESCENT PARAMETERS',
            ],

            // ============================================
            // DYNAMIC MINERALIZATION PARAMETERS
            // ============================================

            [
                'name' => 'single_labeled_surface_over_bone_surface',
                'display_name' => 'Single Labeled Surface/Bone Surface (sLS/BS)',
                'unit' => '%',
                'synonyms' => ['sLS/BS', 'single_labeled_surface_bone_surface', 'single_labelled_surface', 'single_labeled_surface', 'sLS_BS', 'sLSBS', 'sLS BS',
                    'Single Labeled Surface/Bone Surface', 'Single Labelled Surface/Bone Surface', 'single labeled surface/bone surface', 'Single Labeled Surface (%)',
                    'Single Labelled Surface (%)', 'sLS/BS (%)', 'sLS/BS%', 'sls/bs', 'sLs/Bs', 'sLS', 'Single Label Surface', 'single label surface', 'Percent Single Labeled Surface',
                    'S.L.S/BS', 'sL.S/BS'],
                'region' => 'trabecular',
                'grouping' => 'DYNAMIC MINERALIZATION PARAMETERS',
            ],
            [
                'name' => 'double_labeled_surface_over_bone_surface',
                'display_name' => 'Double Labeled Surface/Bone Surface (dLS/BS)',
                'unit' => '%',
                'synonyms' => ['dLS/BS', 'double_labeled_surface_bone_surface', 'double_labelled_surface', 'double_labeled_surface', 'dLS_BS', 'dLSBS', 'dLS BS',
                    'Double Labeled Surface/Bone Surface', 'Double Labelled Surface/Bone Surface', 'double labeled surface/bone surface', 'Double Labeled Surface (%)',
                    'Double Labelled Surface (%)', 'dLS/BS (%)', 'dLS/BS%', 'dls/bs', 'dLs/Bs', 'dLS', 'Double Label Surface', 'double label surface', 'Percent Double Labeled Surface',
                    'D.L.S/BS', 'dL.S/BS'],
                'region' => 'trabecular',
                'grouping' => 'DYNAMIC MINERALIZATION PARAMETERS',
            ],
            [
                'name' => 'mineralizing_surface_over_bone_surface',
                'display_name' => 'Mineralizing Surface/Bone Surface (MS/BS)',
                'unit' => '%',
                'synonyms' => ['MS/BS', 'mineralizing_surface_bone_surface', 'mineralizing_surface', 'MS_BS', 'MSBS', 'MS BS', 'Mineralizing Surface/Bone Surface',
                    'Mineralizing Surface per Bone Surface', 'mineralizing surface/bone surface', 'Mineralizing Surface (%)', 'MS/BS (%)', 'MS/BS%', 'ms/bs', 'Ms/Bs',
                    'Mineralizing Surface', 'mineralizing surface', 'Percent Mineralizing Surface', 'percent mineralizing surface', 'M.S/B.S', 'M.S/BS', 'Mineralized Surface',
                    'mineralized surface', 'Calcification Surface', 'Formation Surface'],
                'region' => 'trabecular',
                'grouping' => 'DYNAMIC MINERALIZATION PARAMETERS',
            ],
            [
                'name' => 'mineral_apposition_rate',
                'display_name' => 'Mineral Apposition Rate (MAR)',
                'unit' => 'µm/day',
                'synonyms' => ['MAR', 'mineral_apposition_rate', 'M.A.R', 'M.A.R.', 'M A R', 'Mineral Apposition Rate', 'Mineral Apposition Rate (µm/day)', 'Mineral Apposition Rate (um/day)',
                    'Mineral Apposition Rate (microns/day)', 'mineral apposition rate', 'mineral_apposition_rate_um_day', 'mar', 'Apposition Rate', 'apposition rate', 'Mineralization Rate',
                    'mineralization rate', 'MAR (µm/d)', 'MAR (um/d)', 'Bone Apposition Rate'],
                'region' => 'trabecular',
                'grouping' => 'DYNAMIC MINERALIZATION PARAMETERS',
            ],
            [
                'name' => 'bone_formation_rate_over_bone_surface',
                'display_name' => 'Bone Formation Rate/Bone Surface (BFR/BS)',
                'unit' => 'µm3/µm2/day',
                'synonyms' => ['BFR/BS', 'bone_formation_rate_bone_surface', 'bone_formation_rate', 'BFR_BS', 'BFRBS', 'BFR BS', 'Bone Formation Rate/Bone Surface',
                    'Bone Formation Rate per Bone Surface', 'bone formation rate/bone surface', 'BFR/BS (µm3/µm2/day)', 'BFR/BS (um3/um2/day)', 'BFR/BS (µm³/µm²/day)',
                    'bfr/bs', 'Bfr/Bs', 'BFR', 'Bone Formation Rate', 'bone formation rate', 'Formation Rate', 'formation rate', 'Mineralized Bone Formation Rate',
                    'BFR/BS (µm/day)', 'B.F.R/BS'],
                'region' => 'trabecular',
                'grouping' => 'DYNAMIC MINERALIZATION PARAMETERS',
            ],
            [
                'name' => 'mineralization_lagtime',
                'display_name' => 'Mineralization Lag Time (Mlt)',
                'unit' => 'days',
                'synonyms' => ['Mlt', 'mineralization_lag_time', 'mineralization_lagtime', 'M.L.T', 'M.L.T.', 'MLT', 'M L T', 'Mineralization Lag Time', 'Mineralization Lag Time (days)',
                    'Mineralization Lag Time (d)', 'mineralization lag time', 'mineralization_lag_time_days', 'mlt', 'Lag Time', 'lag time', 'Mineral Lag Time', 'mineral lag time',
                    'Mlt (days)', 'Mlt (d)', 'Mineralization Delay'],
                'region' => 'trabecular',
                'grouping' => 'DYNAMIC MINERALIZATION PARAMETERS',
            ],
        ];
        $this->command->info('Trabecular array defined'); // ← Add this

        $bonehisto_cortical_measurements = [

            // ============================================
            // STRUCTURAL CORTICAL BONE PARAMETERS
            // ============================================

            [
                'name' => 'cortical_thickness',
                'display_name' => 'Cortical Thickness (Ct.Th)',
                'unit' => 'μm',  // Changed from 'mm'
                'synonyms' => ['Ct.Th', 'cortical_thickness', 'CtTh', 'Ct.Th.', 'Ct Th', 'Cortical Thickness', 'Cortical Thickness (µm)', 'Cortical Thickness (um)', 'Cortical Thickness (mm)',
                    'cortical thickness', 'cortical_thickness_um', 'Cort.Th', 'Cort Th', 'Ct_Th', 'ct.th', 'ct_th', 'Cortical Bone Thickness', 'cortical bone thickness'],
                'region' => 'cortical',
                'grouping' => 'STRUCTURAL CORTICAL BONE PARAMETERS',
            ],
            [
                'name' => 'cortical_area',
                'display_name' => 'Cortical Area (Ct.Ar)',
                'unit' => 'mm²',  // Changed from 'mm2'
                'synonyms' => ['Ct.Ar', 'cortical_area', 'CtAr', 'Ct.Ar.', 'Ct Ar', 'Cortical Area', 'Cortical Area (mm2)', 'Cortical Area (mm²)', 'Cortical Area (mm^2)',
                    'cortical area', 'cortical_area_mm2', 'Cort.Ar', 'Cort Ar', 'Ct_Ar', 'ct.ar', 'ct_ar', 'Cortical Bone Area', 'cortical bone area'],
                'region' => 'cortical',
                'grouping' => 'STRUCTURAL CORTICAL BONE PARAMETERS',
            ],
            [
                'name' => 'cortical_area_over_total_area',  // Removed slash from name
                'display_name' => 'Cortical Area/Total Area (Ct.Ar/Tt.Ar)',
                'unit' => '%',
                'synonyms' => ['Ct.Ar/Tt.Ar', 'cortical_area_over_total_area', 'cortical_area_total_area', 'cortical_area/total_area', 'CtAr/TtAr', 'Ct.Ar_Tt.Ar', 'CtAr_TtAr',
                    'Ct Ar/Tt Ar', 'Cortical Area/Total Area', 'Cortical Area per Total Area', 'cortical area/total area', 'Ct.Ar/Tt.Ar (%)', 'ct.ar/tt.ar', 'Ct.Ar/T.Ar',
                    'Cortical Area Fraction', 'cortical area fraction', 'Percent Cortical Area', 'percent cortical area', 'Cortical Bone Fraction'],
                'region' => 'cortical',
                'grouping' => 'STRUCTURAL CORTICAL BONE PARAMETERS',
            ],
            [
                'name' => 'marrow_area',
                'display_name' => 'Marrow Area (Ma.Ar)',
                'unit' => 'mm²',  // Changed from 'mm2'
                'synonyms' => ['Ma.Ar', 'marrow_area', 'MaAr', 'Ma.Ar.', 'Ma Ar', 'Marrow Area', 'Marrow Area (mm2)', 'Marrow Area (mm²)', 'Marrow Area (mm^2)', 'marrow area',
                    'marrow_area_mm2', 'Ma_Ar', 'ma.ar', 'ma_ar', 'Medullary Area', 'medullary area', 'Marrow Cavity Area', 'marrow cavity area'],
                'region' => 'cortical',
                'grouping' => 'STRUCTURAL CORTICAL BONE PARAMETERS',
            ],
            [
                'name' => 'cortical_porosity',
                'display_name' => 'Cortical Porosity (Ct.Po)',
                'unit' => '%',
                'synonyms' => ['Ct.Po', 'cortical_porosity', 'CtPo', 'Ct.Po.', 'Ct Po', 'Cortical Porosity', 'Cortical Porosity (%)', 'cortical porosity', 'cortical_porosity_percent',
                    'Cort.Po', 'Cort Po', 'Ct_Po', 'ct.po', 'ct_po', 'Cortical Bone Porosity', 'cortical bone porosity', 'Intracortical Porosity', 'intracortical porosity'],
                'region' => 'cortical',
                'grouping' => 'STRUCTURAL CORTICAL BONE PARAMETERS',
            ],

            // ============================================
            // DYNAMIC MINERALIZATION PARAMETERS
            // ============================================

            [
                'name' => 'periosteal_mineralizing_surface_over_bone_surface',
                'display_name' => 'Periosteal Mineralizing Surface (Ps.MS/BS)',
                'unit' => '%',
                'synonyms' => ['Ps.MS/BS', 'periosteal_mineralizing_surface_bone_surface', 'periosteal_mineralizing_surface', 'PsMS/BS', 'Ps.MS_BS', 'PsMS_BS', 'Ps MS/BS',
                    'Periosteal Mineralizing Surface/Bone Surface', 'Periosteal Mineralizing Surface per Bone Surface', 'periosteal mineralizing surface/bone surface', 'Ps.MS/BS (%)',
                    'ps.ms/bs', 'Periosteal Mineralizing Surface', 'periosteal mineralizing surface', 'Periosteal MS/BS', 'Ps.M.S/BS', 'Periosteal Formation Surface'],
                'region' => 'cortical',
                'grouping' => 'DYNAMIC MINERALIZATION PARAMETERS',
            ],
            [
                'name' => 'endocortical_mineralizing_surface_over_bone_surface',
                'display_name' => 'Endocortical Mineralizing Surface (Ec.MS/BS)',
                'unit' => '%',
                'synonyms' => ['Ec.MS/BS', 'endocortical_mineralizing_surface_bone_surface', 'endocortical_mineralizing_surface', 'EcMS/BS', 'Ec.MS_BS', 'EcMS_BS', 'Ec MS/BS',
                    'Endocortical Mineralizing Surface/Bone Surface', 'Endocortical Mineralizing Surface per Bone Surface', 'endocortical mineralizing surface/bone surface',
                    'Ec.MS/BS (%)', 'ec.ms/bs', 'Endocortical Mineralizing Surface', 'endocortical mineralizing surface', 'Endocortical MS/BS', 'Ec.M.S/BS',
                    'Endocortical Formation Surface', 'Endosteal Mineralizing Surface'],
                'region' => 'cortical',
                'grouping' => 'DYNAMIC MINERALIZATION PARAMETERS',
            ],
            [
                'name' => 'periosteal_mineral_apposition_rate',
                'display_name' => 'Periosteal Mineral Apposition Rate (Ps.MAR)',
                'unit' => 'μm/day',
                'synonyms' => ['Ps.MAR', 'periosteal_mineral_apposition_rate', 'PsMAR', 'Ps.MAR.', 'Ps MAR', 'Periosteal Mineral Apposition Rate', 'Periosteal Mineral Apposition Rate (µm/day)',
                    'Periosteal Mineral Apposition Rate (um/day)', 'periosteal mineral apposition rate', 'periosteal_mar', 'ps.mar', 'Periosteal MAR', 'periosteal mar', 'Ps.M.A.R',
                    'Periosteal Apposition Rate', 'periosteal apposition rate'],
                'region' => 'cortical',
                'grouping' => 'DYNAMIC MINERALIZATION PARAMETERS',
            ],
            [
                'name' => 'endocortical_mineral_apposition_rate',
                'display_name' => 'Endocortical Mineral Apposition Rate (Ec.MAR)',
                'unit' => 'μm/day',
                'synonyms' => ['Ec.MAR', 'endocortical_mineral_apposition_rate', 'EcMAR', 'Ec.MAR.', 'Ec MAR', 'Endocortical Mineral Apposition Rate',
                    'Endocortical Mineral Apposition Rate (µm/day)', 'Endocortical Mineral Apposition Rate (um/day)', 'endocortical mineral apposition rate',
                    'endocortical_mar', 'ec.mar', 'Endocortical MAR', 'endocortical mar', 'Ec.M.A.R', 'Endocortical Apposition Rate', 'endocortical apposition rate',
                    'Endosteal MAR', 'Endosteal Mineral Apposition Rate'],
                'region' => 'cortical',
                'grouping' => 'DYNAMIC MINERALIZATION PARAMETERS',
            ],
            [
                'name' => 'periosteal_bone_formation_rate_over_bone_surface',
                'display_name' => 'Periosteal Bone Formation Rate (Ps.BFR/BS)',
                'unit' => 'μm³/μm²/day',
                'synonyms' => ['Ps.BFR/BS', 'periosteal_bone_formation_rate_bone_surface', 'periosteal_bone_formation_rate', 'PsBFR/BS', 'Ps.BFR_BS', 'PsBFR_BS', 'Ps BFR/BS',
                    'Periosteal Bone Formation Rate/Bone Surface', 'Periosteal Bone Formation Rate per Bone Surface', 'periosteal bone formation rate/bone surface',
                    'Ps.BFR/BS (µm3/µm2/day)', 'Ps.BFR/BS (µm³/µm²/day)', 'ps.bfr/bs', 'Periosteal BFR', 'periosteal bfr', 'Ps.B.F.R/BS', 'Periosteal Formation Rate',
                    'periosteal formation rate'],
                'region' => 'cortical',
                'grouping' => 'DYNAMIC MINERALIZATION PARAMETERS',
            ],
            [
                'name' => 'endocortical_bone_formation_rate_over_bone_surface',
                'display_name' => 'Endocortical Bone Formation Rate (Ec.BFR/BS)',
                'unit' => 'μm³/μm²/day',
                'synonyms' => ['Ec.BFR/BS', 'endocortical_bone_formation_rate_bone_surface', 'endocortical_bone_formation_rate', 'EcBFR/BS', 'Ec.BFR_BS', 'EcBFR_BS', 'Ec BFR/BS',
                    'Endocortical Bone Formation Rate/Bone Surface', 'Endocortical Bone Formation Rate per Bone Surface', 'endocortical bone formation rate/bone surface',
                    'Ec.BFR/BS (µm3/µm2/day)', 'Ec.BFR/BS (µm³/µm²/day)', 'ec.bfr/bs', 'Endocortical BFR', 'endocortical bfr', 'Ec.B.F.R/BS', 'Endocortical Formation Rate',
                    'endocortical formation rate', 'Endosteal BFR', 'Endosteal Bone Formation Rate'],
                'region' => 'cortical',
                'grouping' => 'DYNAMIC MINERALIZATION PARAMETERS',
            ],

        ];
        $this->command->info('Cortical array defined'); // ← Add this
        $bonehisto_komp_screen_measurements = [

            // ============================================
            // STATIC FORMATION PARAMETERS
            // ============================================

            [
                'name' => 'alkaline_phosphatase_over_bone_surface',
                'display_name' => 'Alkaline Phosphatase+ Bone Surface/Bone Surface (AP/BS)',
                'unit' => '%',
                'synonyms' => ['AP/BS', 'AP_BS', 'APBS', 'AP BS', 'Alkaline Phosphatase+ Bone Surface/Bone Surface', 'Alkaline Phosphatase Bone Surface',
                    'AP/BS (%)', 'ap/bs', 'ALP/BS', 'ALP+ Bone Surface'],
                'region' => 'trabecular',
                'grouping' => 'STATIC FORMATION PARAMETERS',
                'public' => false,
            ],
            [
                'name' => 'alkaline_phosphatase_over_labeled_bone_surface',
                'display_name' => 'Alkaline Phosphatase+ Bone Surface/Labeled Bone Surface (AP/L.BS)',
                'unit' => '%',
                'synonyms' => ['AP/L.BS', 'AP_L_BS', 'AP L BS', 'Alkaline Phosphatase+ Bone Surface/Labeled Bone Surface', 'AP/L.BS (%)', 'ap/l.bs', 'ALP/L.BS',
                    'AP/Labeled BS', 'AP/Mineralizing Surface'],
                'region' => 'trabecular',
                'grouping' => 'STATIC FORMATION PARAMETERS',
                'public' => false,
            ],
            [
                'name' => 'alkaline_phosphatase_over_nonlabeled_bone_surface',
                'display_name' => 'Alkaline Phosphatase+ Bone Surface/Non-Labeled Bone Surface (AP/NL.BS)',
                'unit' => '%',
                'synonyms' => ['AP/NL.BS', 'AP_NL_BS', 'AP NL BS', 'Alkaline Phosphatase+ Bone Surface/Non-Labeled Bone Surface', 'AP/Q.BS', 'AP_Q_BS', 'AP/NL.BS (%)',
                    'ap/nl.bs', 'ALP/NL.BS', 'AP/Quiescent BS', 'AP/Non-Mineralizing Surface'],
                'region' => 'trabecular',
                'grouping' => 'STATIC FORMATION PARAMETERS',
                'public' => false,
            ],
            [
                'name' => 'labeled_alkaline_phosphatase_surface_over_total_ap_surface',  // Reordered for clarity
                'display_name' => 'Labeled Alkaline Phosphatase+ Surface/Total AP+ Surface (L.S+AP/T.AP)',
                'unit' => '%',
                'synonyms' => ['L.S+AP/T.AP', 'AP_L_AP', 'AP L AP', 'Labeled Alkaline Phosphatase+ Surface/Total AP+ Surface', 'L.S+AP/T.AP (%)',
                    'Labeled AP/Total AP', 'l.s+ap/t.ap', 'LS+AP/TAP', 'Mineralizing AP+ Surface/Total AP+ Surface'],
                'region' => 'trabecular',
                'grouping' => 'STATIC FORMATION PARAMETERS',
                'public' => false,
            ],
            [
                'name' => 'nonlabeled_alkaline_phosphatase_surface_over_total_ap_surface',  // Changed from 'unlabeled'
                'display_name' => 'Non-Labeled Alkaline Phosphatase+ Surface/Total AP+ Surface (NL.S+AP/T.AP)',
                'unit' => '%',
                'synonyms' => ['NL.S+AP/T.AP', 'AP_NL_AP', 'AP NL AP', 'Non-Labeled Alkaline Phosphatase+ Surface/Total AP+ Surface', 'NL.S+AP/T.AP (%)', 'Unlabeled AP/Total AP',
                    'nl.s+ap/t.ap', 'NLS+AP/TAP', 'Non-Mineralizing AP+ Surface/Total AP+ Surface'],
                'region' => 'trabecular',
                'grouping' => 'STATIC FORMATION PARAMETERS',
                'public' => false,
            ],

            // ============================================
            // STATIC RESORPTIVE PARAMETERS
            // ============================================

            [
                'name' => 'trap_over_bone_surface',
                'display_name' => 'TRAP+ Bone Surface/Bone Surface (TRAP/BS)',
                'unit' => '%',
                'synonyms' => ['TRAP/BS', 'TRAP_BS', 'TRAPBS', 'TRAP BS', 'TRAP+ Bone Surface/Bone Surface', 'TRAP Bone Surface', 'TRAP/BS (%)', 'trap/bs', 'TRAP+ Surface',
                    'Tartrate-Resistant Acid Phosphatase/BS'],
                'region' => 'trabecular',
                'grouping' => 'STATIC RESORPTIVE PARAMETERS',
                'public' => false,
            ],
            [
                'name' => 'trap_over_labeled_bone_surface',
                'display_name' => 'TRAP+ Bone Surface/Labeled Bone Surface (TRAP/L.BS)',
                'unit' => '%',
                'synonyms' => ['TRAP/L.BS', 'TRAP_L_BS', 'TRAP L BS', 'TRAP+ Bone Surface/Labeled Bone Surface', 'TRAP/L.BS (%)', 'trap/l.bs',
                    'TRAP/Labeled BS', 'TRAP/Mineralizing Surface'],
                'region' => 'trabecular',
                'grouping' => 'STATIC RESORPTIVE PARAMETERS',
                'public' => false,
            ],
            [
                'name' => 'trap_over_nonlabeled_bone_surface',
                'display_name' => 'TRAP+ Bone Surface/Non-Labeled Bone Surface (TRAP/NL.BS)',
                'unit' => '%',
                'synonyms' => ['TRAP/NL.BS', 'TRAP_NL_BS', 'TRAP NL BS', 'TRAP+ Bone Surface/Non-Labeled Bone Surface', 'TRAP/QS.BS', 'TRAP/Q.BS', 'TRAP_Q_BS', 'TRAP/NL.BS (%)', 'trap/nl.bs', 'TRAP/Quiescent BS', 'TRAP/Non-Mineralizing Surface'],
                'region' => 'trabecular',
                'grouping' => 'STATIC RESORPTIVE PARAMETERS',
                'public' => false,
            ],
            [
                'name' => 'trap_bone_surface_over_total_trap',
                'display_name' => 'TRAP+ Bone Surface/Total TRAP+ Surface (TRAP/TRAP)',
                'unit' => '%',
                'synonyms' => ['TRAP/TRAP', 'TRAP_on_TRAP', 'TRAP on TRAP', 'TRAP+ Bone Surface/Total TRAP+', 'TRAP+.BS/TRAP', 'TRAP/TRAP (%)', 'trap/trap'],
                'region' => 'trabecular',
                'grouping' => 'STATIC RESORPTIVE PARAMETERS',
                'public' => false,
            ],
            [
                'name' => 'labeled_trap_surface_over_trap_bone_surface',
                'display_name' => 'TRAP+ Labeled Bone Surface/TRAP+ Bone Surface (TRAP+L.BS/TRAP.BS)',
                'unit' => '%',
                'synonyms' => ['TRAP+L.BS/TRAP.BS', 'TRAP_L_TRAP_on', 'TRAP L TRAP on', 'TRAP+ Labeled Bone Surface/TRAP+ Bone Surface', 'TRAP+L.BS/TRAP.BS (%)',
                    'Labeled TRAP/Total TRAP', 'trap+l.bs/trap.bs', 'Mineralizing TRAP+ Surface/Total TRAP+ Surface'],
                'region' => 'trabecular',
                'grouping' => 'STATIC RESORPTIVE PARAMETERS',
                'public' => false,
            ],
            [
                'name' => 'nonlabeled_trap_surface_over_trap_bone_surface',
                'display_name' => 'TRAP+ Non-Labeled Bone Surface/TRAP+ Bone Surface (TRAP+QS.BS/TRAP.BS)',
                'unit' => '%',
                'synonyms' => ['TRAP+QS.BS/TRAP.BS', 'TRAP+OS.BS/TRAP.BS', 'TRAP_NL_TRAP_on', 'TRAP NL TRAP on', 'TRAP+ Non-Labeled Bone Surface/TRAP+ Bone Surface',
                    'TRAP+QS.BS/TRAP.BS (%)', 'Non-Labeled TRAP/Total TRAP', 'trap+qs.bs/trap.bs', 'Non-Mineralizing TRAP+ Surface/Total TRAP+ Surface'],
                'region' => 'trabecular',
                'grouping' => 'STATIC RESORPTIVE PARAMETERS',
                'public' => false,
            ],
            [
                'name' => 'ap_trap_over_bone_surface',
                'display_name' => 'AP+ TRAP+ Bone Surface/Bone Surface (AP+TRAP/BS)',
                'unit' => '%',
                'synonyms' => ['AP+TRAP/BS', 'AP_TRAP_BS', 'AP TRAP BS', 'AP+ TRAP+ Bone Surface/Bone Surface', 'double+(AP,TRAP)/BS', 'double+(AP,TRAP)/BS (%)',
                    'AP+TRAP+/BS', 'ap+trap/bs', 'ALP+TRAP+/BS', 'Double Positive Surface', 'AP and TRAP/BS'],
                'region' => 'trabecular',
                'grouping' => 'STATIC RESORPTIVE PARAMETERS',
                'public' => false,
            ],
            [
                'name' => 'ap_trap_labeled_bone_surface_over_bone_surface',
                'display_name' => 'AP+ TRAP+ Labeled Bone Surface/Bone Surface (AP+TRAP+L.BS/BS)',
                'unit' => '%',
                'synonyms' => ['AP+TRAP+L.BS/BS', 'AP_TRAP_R_RG_BS', 'AP TRAP R RG BS', 'AP+ TRAP+ Labeled Bone Surface/Bone Surface', 'triple+(AP,TRAP,L.BS)/BS',
                    'triple+(AP,TRAP,L.BS)/BS (%)', 'AP+TRAP+L/BS', 'ap+trap+l.bs/bs', 'Triple Positive Surface', 'AP and TRAP and Label/BS'],
                'region' => 'trabecular',
                'grouping' => 'STATIC RESORPTIVE PARAMETERS',
                'public' => false,
            ],

        ];
        $this->command->info('Komp array defined'); // ← Add this

        // ADD THESE DEBUG LINES:
        $this->command->info('Trabecular count: '.count($bonehisto_trabecular_measurements));
        $this->command->info('Cortical count: '.count($bonehisto_cortical_measurements));
        $this->command->info('KOMP count: '.count($bonehisto_komp_screen_measurements));
        // Combine all measurement arrays
        $all_measurements = array_merge(
            $bonehisto_trabecular_measurements,
            $bonehisto_cortical_measurements,
            $bonehisto_komp_screen_measurements
        );

        $total_count = count($all_measurements);
        $this->command->info("$total_count BoneHisto measurements found.");
        $this->command->info('  - Trabecular: '.count($bonehisto_trabecular_measurements));
        $this->command->info('  - Cortical: '.count($bonehisto_cortical_measurements));
        $this->command->info('  - KOMP Screen: '.count($bonehisto_komp_screen_measurements));

        // CLEANUP: Extract all measurement names and delete obsolete ones
        $allMeasurementNames = [];
        foreach ($all_measurements as $m) {
            $allMeasurementNames[] = $m['name'];
        }

        $deleted = Measurement::where('analysis_type_id', $instrument->id)
            ->whereNotIn('name', $allMeasurementNames)
            ->delete();

        if ($deleted > 0) {
            $this->command->info("Removed {$deleted} obsolete bone histomorphometry measurements");
        }

        $total_count = count($all_measurements);
        $this->command->info("$total_count BoneHisto measurements found.");
        $this->command->info('  - Trabecular: '.count($bonehisto_trabecular_measurements));
        $this->command->info('  - Cortical: '.count($bonehisto_cortical_measurements));
        $this->command->info('  - KOMP Screen: '.count($bonehisto_komp_screen_measurements));

        foreach ($all_measurements as $measurementData) {
            $m = Measurement::updateOrCreate(
                [
                    'name' => $measurementData['name'],
                    'analysis_type_id' => $instrument->id,
                    'specimen_type_id' => null,
                ],
                [
                    'display_name' => $measurementData['display_name'],
                    'unit' => $measurementData['unit'],
                    'synonyms' => $measurementData['synonyms'],
                    'grouping' => $measurementData['grouping'],
                    'public' => $measurementData['public'] ?? true,
                ]
            );

            $region = $measurementData['region'] ?? null;

            // Handle different region scenarios
            if ($region === 'trabecular') {
                $m->regions()->syncWithoutDetaching(Region::firstWhere('code', 'trabecular'));
            } elseif ($region === 'cortical') {
                $m->regions()->syncWithoutDetaching(Region::firstWhere('code', 'cortical'));
            } elseif ($region === 'both' || ! $region) {
                // If region is 'both' or not specified, attach to both regions
                $m->regions()->syncWithoutDetaching(Region::firstWhere('code', 'trabecular'));
                $m->regions()->syncWithoutDetaching(Region::firstWhere('code', 'cortical'));
            }
        }

        $this->command->info('BoneHisto measurements seeded successfully.');
    }
}
