@props([
    'study',
    'viewonly' => false, // used to disable edit/delete button on report page
])

<style>
.tree-view ul {
    list-style-type: none;
    /* border-left: 1px dashed #ccc; */
    padding-left: 2em;
    margin-bottom:0;
}
.tree-view>ul {
    border-left: none;
    padding-left: 0;
}
.tree-view ul li::before {
    content: '\F132';
    font-family: 'bootstrap-icons';
    display: inline-block;

    margin-right: 4px;
    font-size: 0.75em;
}
.tree-view ul.noicon li::before {
    content: '-';
}
.tree-view>ul>li::before {
    content: '';
}
</style>
<div class="tree-view bg-light px-1 py-2 border mb-3" style="font-size: 13px;">
    <ul>
        <li><span class="fw-bold">Sample Groups</span> /
            <ul>
                <div class="row">
                    <div class="col-md-6">
                        <li><span class="fw-bold">Male</span> /
                            <ul>
                                <li><span class="fw-bold">Control Animals</span> /
                                    @include('study.sample-groups.partials.tree-list', ['sampleGroups' => $study->sampleGroups->where('sex', 'M')->where('control', true)])
                                </li>
                                <li><span class="fw-bold">Experimental Animals</span> /
                                    @include('study.sample-groups.partials.tree-list', ['sampleGroups' => $study->sampleGroups->where('sex', 'M')->where('control', false)])
                                </li>
                            </ul>
                        </li>
                    </div>
                    <div class="col-md-6">
                        <li><span class="fw-bold">Female</span> /
                            <ul>
                                <li><span class="fw-bold">Control Animals</span> /
                                    @include('study.sample-groups.partials.tree-list', ['sampleGroups' => $study->sampleGroups->where('sex', 'F')->where('control', true)])
                                </li>
                                <li><span class="fw-bold">Experimental Animals</span> /
                                    @include('study.sample-groups.partials.tree-list', ['sampleGroups' => $study->sampleGroups->where('sex', 'F')->where('control', false)])
                                </li>
                            </ul>
                        </li>
                    </div>
                </div>
            </ul>
        </li>
    </ul>
</div>