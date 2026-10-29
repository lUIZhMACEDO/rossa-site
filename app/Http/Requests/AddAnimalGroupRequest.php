<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AddAnimalGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sex' => 'required|string',
            'age' => 'required|numeric|min:0|max:9999',
            'strain' => $this->study->strains()->count() > 1 ? 'required|string' : 'nullable',
            'gm_gko_genotype' => $this->study->gm_global_knockout ? 'required|string' : 'nullable',
            'gm_gko_genotype_2' => $this->study->gm_global_knockout && $this->study->gm_gko_has_second_animal_line ? 'required|string' : 'nullable',

            'gm_ind_mutation_genotype' => $this->study->gm_induced_mutation ? 'required|string' : 'nullable',
            'gm_ins_mutagenesis_genotype' => $this->study->gm_insertional_mutagenesis ? 'required|string' : 'nullable',

            'gm_cond_knockout_gene_symbol' => $this->study->gm_conditional_knockout ? 'required|string' : 'nullable',
            'gm_cond_knockout_genotype' => $this->study->gm_conditional_knockout ? 'required|string' : 'nullable',
            'gm_cond_knockout_cre_animal_line_abbreviation' => $this->study->gm_conditional_knockout ? 'required|string' : 'nullable',
            'gm_cond_knockout_cre_animal_genotype' => $this->study->gm_conditional_knockout ? 'required|string' : 'nullable',
            'gm_cond_knockout_cre_treatment_group' => $this->study->gm_conditional_knockout && $this->study->gm_cond_knockout_cre_system_used !== 'transgene_regulated_non_inducible' ? 'required|string' : 'nullable',

            'gm_cond_knockin_gene_symbol' => $this->study->gm_cond_knockin_safe_harbor ? 'required|string' : 'nullable',
            'gm_cond_knockin_genotype' => $this->study->gm_cond_knockin_safe_harbor ? 'required|string' : 'nullable',
            'gm_cond_knockin_cre_animal_line_abbreviation' => $this->study->gm_cond_knockin_safe_harbor ? 'required|string' : 'nullable',
            'gm_cond_knockin_cre_animal_genotype' => $this->study->gm_cond_knockin_safe_harbor ? 'required|string' : 'nullable',
            'gm_cond_knockin_cre_treatment_group' => $this->study->gm_cond_knockin_safe_harbor && $this->study->gm_cond_knockin_safe_harbor_cre_system_used !== 'transgene_regulated_non_inducible' ? 'required|string' : 'nullable',

            'gm_random_genome_gene_symbol' => $this->study->gm_transgene ? 'required|string' : 'nullable',
            'gm_random_genome_genotype' => $this->study->gm_transgene ? 'required|string' : 'nullable',
            'gm_random_genome_treatment_group' => $this->study->gm_transgene && $this->study->gm_random_genome_utilizes_drug_inducible_mechanism ? 'required|string' : 'nullable',
            'gm_random_genome_gene_symbol_2' => $this->study->gm_transgene && $this->study->gm_random_genome_second_animal_line ? 'required|string' : 'nullable',
            'gm_random_genome_genotype_2' => $this->study->gm_transgene && $this->study->gm_random_genome_second_animal_line ? 'required|string' : 'nullable',
            'gm_random_genome_treatment_group_2' => $this->study->gm_transgene && $this->study->gm_random_genome_utilizes_drug_inducible_mechanism_2 ? 'required|string' : 'nullable',

            'gonadectomy_type_of_surgery' => 'nullable|string',
            'gonadectomy_age' => 'nullable|required_with:gonadectomy_type_of_surgery|integer|min:0|max:9999',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $data = $validator->validated();
                if ($this->study->gm_global_knockout && ! $this->study->gm_gko_gene_symbol) {
                    $validator->errors()->add('gm_gko_gene_symbol', 'Gene symbol is required for global knockout studies, and must be filled out on the Animal Experimentation page.');

                    if ($this->study->gm_gko_has_second_animal_line && ! $this->study->gm_gko_gene_symbol_2) {
                        $validator->errors()->add('gm_gko_gene_symbol_2', 'Gene symbol is required for second animal line in global knockout studies, and must be filled out on the Animal Experimentation page.');
                    }
                }

                if ($this->study->gm_induced_mutation && ! $this->study->gm_ind_mutation_gene_symbol) {
                    $validator->errors()->add('gm_ind_mutation_gene_symbol', 'Gene symbol is required for induced mutation studies, and must be filled out on the Animal Experimentation page.');
                }

                if ($this->study->gm_insertional_mutagenesis && ! $this->study->gm_ins_mutagenesis_gene_symbol) {
                    $validator->errors()->add('gm_ins_mutagenesis_gene_symbol', 'Gene symbol is required for insertional mutagenesis studies, and must be filled out on the Animal Experimentation page.');
                }

                // Gonadectomy Validation
                if (isset($data['gonadectomy_type_of_surgery']) && $data['gonadectomy_type_of_surgery']) {
                    $invalid_female = $data['gonadectomy_type_of_surgery'] === 'ovx' && $data['sex'] !== 'female';
                    $invalid_male = $data['gonadectomy_type_of_surgery'] === 'orx' && $data['sex'] !== 'male';
                    if ($invalid_male || $invalid_female) {
                        $validator->errors()->add('gonadectomy_type_of_surgery', 'The selected type of surgery is not valid for the selected animal sex.');
                    }
                }
            },
        ];
    }
}
