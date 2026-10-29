<div class="mb-3">
    {{-- Hidden field for sample group ID when updating --}}
    @if($sampleGroup && $sampleGroup->id)
        <input type="hidden" name="sample_group_id" value="{{ $sampleGroup->id }}">
    @endif

    {{-- Sample Group Name --}}
    <div class="mb-3 p-3" style="background-color: rgba(13, 59, 82, 0.05); border: 1px solid rgba(13, 59, 82, 0.15); border-radius: 0.375rem;">
        <div class="row">
            <div class="col-md-6">
                <label for="label" class="form-label required fw-semibold">Name Your Sample Group</label>
                <div class="d-flex align-items-center gap-2">
                    <input type="text" class="form-control" id="label" name="label" placeholder="Car12 Knockout Males" value="{{ old('label', $sampleGroup->label ?? '') }}" style="flex: 1;">
                    <i class="bi bi-question-circle fs-5"
                        data-bs-toggle="popover"
                        data-bs-trigger="hover focus"
                        data-bs-placement="right"
                        data-bs-title="<i class='bi bi-info-circle me-2'></i>Importance of Sample Group Labels"
                        data-image-url="{{ asset('images/popovers/sg_label_table.png') }}"
                        data-image-caption="<strong>The names you assign to your sample groups matter.</strong> They will appear as-is in all tables and figures of the final report, so name them clearly so your results are easy to interpret at a glance."
                        style="cursor: pointer; color: var(--bs-nav-link-color); flex-shrink: 0;"></i>
                </div>
                <div class="form-text">
                    A descriptive name for this sample group
                </div>
            </div>
        </div>
    </div>

    {{-- Animal Model, Drug Administration, Gonadectomy, Other Intervention —
         each of these adds a meaningful characteristic to the group, so each
         gets its own highlighted box rather than blending into a plain form row. --}}

    <div class="mb-3 p-3" style="background-color: rgba(13, 59, 82, 0.05); border: 1px solid rgba(13, 59, 82, 0.15); border-radius: 0.375rem;">
        <label for="animalModel" class="form-label required fw-semibold">Select Your Animal Model</label>
        <select class="form-select" id="animalModel" name="animal_model" hx-get="" hx-target="#genotypeSwap" hx-select="#genotypeSwap" hx-swap="outerHTML" hx-trigger="change">
            <option value="">-- Select Animal Model --</option>
            @foreach($study->animalModels as $animalModel)
                <option value="{{ $animalModel->id }}" @selected(old('animal_model', $sampleGroup->animal_model_id ?? '') == $animalModel->id || ($selectedAnimalModel && $selectedAnimalModel->id === $animalModel->id))>{{ $animalModel->selectOptionLabel() }} (ID: {{ $animalModel->id }})</option>
            @endforeach
        </select>

        {{-- Genotype / Cre Status, dependent on the selected animal model above --}}
        <div class="row mt-3" id="genotypeSwap">
            @if ( $selectedAnimalModel )
                @php
                    $customGenotypes = $selectedAnimalModel->sampleGroupGenotypeChoices();
                    $currentGenotype = old('genotype', $sampleGroup->genotype ?? '');
                @endphp
                <div class="col-md-6">
                    <label for="genotype" class="form-label">Genotype</label>
                    @if (count($customGenotypes) > 0)
                        <select name="genotype" id="genotype" class="form-select">
                            <option value="">-- Select Genotype --</option>
                            @foreach ($customGenotypes as $option)
                                <option value="{{ $option }}" @selected($currentGenotype === $option)>{{ $option }}</option>
                            @endforeach
                            @if ($currentGenotype && !in_array($currentGenotype, $customGenotypes, true))
                                <option value="{{ $currentGenotype }}" selected>{{ $currentGenotype }}</option>
                            @endif
                        </select>
                    @elseif ( $selectedAnimalModel->geneticMethod )
                        <select name="genotype" id="genotype" class="form-select">
                            <option value="">-- Select Genotype --</option>
                            <option value="+/+" @selected($currentGenotype == '+/+')>Wild Type (+/+)</option>
                            <option value="+/-" @selected($currentGenotype == '+/-')>Heterozygous (+/{{ $selectedAnimalModel->geneticMethod->allele_abbreviation }})</option>
                            <option value="-/-" @selected($currentGenotype == '-/-')>Homozygous ({{ $selectedAnimalModel->geneticMethod->allele_abbreviation }}/{{ $selectedAnimalModel->geneticMethod->allele_abbreviation }})</option>
                        </select>
                    @else
                        <select name="genotype" id="genotype" class="form-select">
                            <option value="+/+" selected>Wild Type (+/+)</option>
                        </select>
                    @endif
                </div>

                @if ( $selectedAnimalModel->cre_system_used )
                    <div class="col-md-6">
                        <label for="creStatus" class="form-label">Cre Status</label>
                        <select name="cre_status" id="creStatus" class="form-select">
                            <option value="">-- Select Cre Status --</option>
                            <option value="positive" @selected(old('cre_status', $sampleGroup->cre_status ?? '') == 'positive')>Cre Positive (Cre+)</option>
                            <option value="negative" @selected(old('cre_status', $sampleGroup->cre_status ?? '') == 'negative')>Cre Negative (Cre-)</option>
                        </select>
                    </div>
                @endif

                <div class="col-md-6">
                    @if ( $selectedAnimalModel2 )
                        <label for="animalGenotype">{{ $selectedAnimalModel2->animal_model_name }} Genotype</label>
                        <select name="animal_genotype" id="animalGenotype" class="form-select">
                            <option value="">-- Select Genotype --</option>
                            <option value="+/+">Wild Type (+/+)</option>
                            <option value="+/-">Heterozygous (+/{{ $selectedAnimalModel2->geneticMethod->allele_abbreviation}})</option>
                            <option value="-/-">Homozygous ({{ $selectedAnimalModel2->geneticMethod->allele_abbreviation}}/{{ $selectedAnimalModel2->geneticMethod->allele_abbreviation}})</option>
                        </select>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if ( $study->performs_drug_treatment )
        <div class="mb-3 p-3" style="background-color: rgba(13, 59, 82, 0.05); border: 1px solid rgba(13, 59, 82, 0.15); border-radius: 0.375rem;">
            <label for="drugAdministrationPlan" class="form-label fw-semibold">Select Your Drug Administration Plan</label>
            <select class="form-select" id="drugAdministrationPlan" name="drug_administration_plan_id">
                <option value="">-- No Drug Treatment --</option>
                @foreach($study->drugAdministrationPlans as $plan)
                    <option
                        value="{{ $plan->id }}"
                        @selected(old('drug_administration_plan_id', $sampleGroup->drug_administration_plan_id ?? '') == $plan->id)>
                        {{ $plan->full_display_name }}
                    </option>
                @endforeach
            </select>

            {{-- Preview selected plan --}}
            @if(isset($sampleGroup) && $sampleGroup->drugAdministrationPlan)
                <div class="alert alert-info mt-2 p-2 mb-0">
                    <small>
                        <strong>Selected Plan:</strong><br>
                        {!! nl2br(e($sampleGroup->drugAdministrationPlan->getTimelinePreview())) !!}
                    </small>
                </div>
            @endif
        </div>
    @endif

    @if ( $study->performs_gonadectomy )
        <div class="mb-3 p-3" style="background-color: rgba(13, 59, 82, 0.05); border: 1px solid rgba(13, 59, 82, 0.15); border-radius: 0.375rem;">
            <label for="gonadectomyProcedure" class="form-label fw-semibold">Select Your Gonadectomy Procedure</label>
            <select class="form-select" id="gonadectomyProcedure" name="gonadectomy_procedure">
                <option value="">-- Select Procedure --</option>
                @foreach($study->gonadectomyProcedures as $procedure)
                    <option value="{{ $procedure->id }}" @selected(old('gonadectomy_procedure', $sampleGroup->gonadectomy_procedure_id ?? '') == $procedure->id)>{{ $procedure->getDisplayName() }} (ID: {{ $procedure->id }})</option>
                @endforeach
            </select>
        </div>
    @endif

    @if ( $study->performs_other_intervention )
        <div class="mb-3 p-3" style="background-color: rgba(13, 59, 82, 0.05); border: 1px solid rgba(13, 59, 82, 0.15); border-radius: 0.375rem;">
            <label for="otherInterventionPlan" class="form-label fw-semibold">Select Your Intervention Plan</label>
            <select class="form-select" id="otherInterventionPlan" name="other_intervention_plan_id">
                <option value="">-- No Other Intervention --</option>
                @foreach($study->otherInterventionPlans as $plan)
                    <option
                        value="{{ $plan->id }}"
                        @selected(old('other_intervention_plan_id', $sampleGroup->other_intervention_plan_id ?? '') == $plan->id)>
                        {{ $plan->plan_name }}
                    </option>
                @endforeach
            </select>

            {{-- Preview selected plan --}}
            @if(isset($sampleGroup) && $sampleGroup->otherInterventionPlan)
                <div class="alert alert-info mt-2 p-2 mb-0">
                    <small>
                        <strong>Selected Plan:</strong><br>
                        {!! nl2br(e($sampleGroup->otherInterventionPlan->getTimelinePreview())) !!}
                    </small>
                </div>
            @endif
        </div>
    @endif

    {{-- Age / Sex — moved below the highlighted selectors above --}}
    <div class="mb-3 p-3" style="background-color: rgba(13, 59, 82, 0.05); border: 1px solid rgba(13, 59, 82, 0.15); border-radius: 0.375rem;">
        <div class="row">
            <div class="col-md-6">
                <label for="age" class="form-label required fw-semibold">Age Animal was Analyzed</label>
                <input type="number" class="form-control" id="age" name="age" placeholder="Enter animal age (in weeks)" value="{{ old('age', $sampleGroup->age ?? '') }}">
                <div class="form-text">E.g., 12, 16, etc.</div>
            </div>

            <div class="col-md-6">
                <label for="sex" class="form-label required fw-semibold">Sex</label>
                <select class="form-select" id="sex" name="sex">
                    <option value="">-- Select --</option>
                    <option value="M" @selected(old('sex', $sampleGroup->sex ?? '') == 'M')>Male</option>
                    <option value="F" @selected(old('sex', $sampleGroup->sex ?? '') == 'F')>Female</option>
                </select>
            </div>
        </div>
    </div>

    <fieldset class="my-3">
        <div class="form-check">
            <input class="form-check-input border-primary" type="checkbox" name="control" id="control" value="1" @checked(old('control', $sampleGroup->control ?? false) == true)>
            <label class="form-check-label" for="control">
                This is a Control Group
            </label>
            <p class="form-text fst-italic fs-6">This group is used as a control for comparison with experimental groups.</p>
        </div>
    </fieldset>

</div>
