@php
    $analysisUploadIds = $analysisUploadIds ?? $study->analysisUploads()->pluck('id');
    $sampleGroupIds = $sampleGroupIds ?? [];
    $baseSampleGroupIds = $baseSampleGroupIds ?? (is_array($sampleGroupIds) && !empty($sampleGroupIds) ? $sampleGroupIds : []);
    $extraSampleGroupIds = $extraSampleGroupIds ?? [];
    $measurementDisplayName = data_get($measurement, 'display_name', data_get($measurement, 'name', 'Measurement'));
    $measurementUnit = data_get($measurement, 'unit', '');
    $yAxisTitle = $measurementDisplayName . ($measurementUnit ? ' (' . $measurementUnit . ')' : '');
    if (!isset($plotPayload)) {
        $summaryDataService = app(\App\Services\StudySummaryDataService::class);
        $plotPayload = $summaryDataService->buildMeasurementPlotSeriesPayload(
            $measurement,
            collect($analysisUploadIds),
            $sampleGroupIds,
            $baseSampleGroupIds,
            $extraSampleGroupIds
        );
    }
    $plotSeries = $plotPayload['plotSeries'];
    $baseSampleGroupIds = $plotPayload['baseSampleGroupIds'];
    $extraSampleGroupIds = $plotPayload['extraSampleGroupIds'];
    // uniqid() is not guaranteed unique within a single request — when many
    // measurement plots render in a tight loop, two calls can land in the
    // same microsecond and produce identical IDs, causing two Alpine/Plotly
    // instances to target the same DOM element. Use a deterministic key
    // built from IDs that are already unique instead.
    $randomID = (isset($comparisonGroup) ? $comparisonGroup->id : 'cg')
        . '-' . ($measurement['id'] ?? uniqid());
@endphp

{{-- Single comparison group per plot. Sex/age/genotype splitting is handled
     upstream by which comparison group's sample_group_ids get passed in here,
     not by this component. The plot title (measurement name + unit) now lives
     in Plotly's own layout.title so it's captured by the camera/export button;
     the parent card shows one shared title for the whole comparison group.
     Window listeners are stored on the component (_onToggle, _onResize) and
     removed in destroy(), which Alpine calls automatically when the plot is
     removed from the page (e.g. when wire:key forces a rebuild). Without this,
     every rebuild would leave orphaned listeners behind.
     NOTE: no JS comments inside x-data below, a stray quote breaks the
     attribute and the component fails to initialize silently. --}}
<div x-data="{
        plotId: 'plotting-{{ $randomID }}',
        plotSeries: {{ Js::from($plotSeries) }},
        plotName: '{{ $measurementDisplayName }}',
        yAxisUnit: '{{ $measurementUnit }}',
        yAxisTitle: '{{ $yAxisTitle }}',
        baseSampleGroupIds: {{ Js::from($baseSampleGroupIds) }},
        extraSampleGroupIds: {{ Js::from($extraSampleGroupIds) }},
        plotElement: null,
        traceMeta: [],
        pendingExtraSelection: [],
        _onToggle: null,
        _onResize: null,

        getPlotWidth() {
            var plottingElement = document.getElementById(this.plotId);
            if (plottingElement && plottingElement.offsetWidth > 0) {
                return plottingElement.offsetWidth;
            }
            return 280;
        },

        wrapTitle(text, maxLineLength) {
            var words = text.split(' ');
            var lines = [];
            var currentLine = '';
            words.forEach((word) => {
                var candidate = currentLine ? currentLine + ' ' + word : word;
                if (candidate.length > maxLineLength && currentLine) {
                    lines.push(currentLine);
                    currentLine = word;
                } else {
                    currentLine = candidate;
                }
            });
            if (currentLine) {
                lines.push(currentLine);
            }
            return lines.join('<br>');
        },

        init() {
            var plottingElement = document.getElementById(this.plotId);
            var traces = [];
            var traceMeta = [];
            var colors = ['#041982', '#560320', '#2ca07f', '#320f8c', '#9467bd', '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf'];
            var orderedIds = this.baseSampleGroupIds.concat(this.extraSampleGroupIds).map(Number);
            var extraIds = this.extraSampleGroupIds.map(Number);

            this.plotSeries.forEach((series, index) => {
                var yValues = series.points.map((item) => item.value);
                var xValues = series.points.map((item) => item.sample_label);
                var sampleGroupId = Number(series.sample_group_id);
                var isExtra = extraIds.includes(sampleGroupId);
                var colorIndex = orderedIds.indexOf(sampleGroupId);
                if (colorIndex < 0) {
                    colorIndex = index;
                }

                traces.push({
                    y: yValues,
                    type: 'box',
                    name: series.label,
                    marker: {
                        color: colors[colorIndex % colors.length],
                        size: 4,
                    },
                    line: { width: 1.5 },
                    text: xValues,
                    boxpoints: 'all',
                    jitter: 0.5,
                    sampleGroupId: sampleGroupId,
                    visible: isExtra ? 'legendonly' : true,
                });
                traceMeta.push({
                    traceIndex: index,
                    sampleGroupId: sampleGroupId,
                    isExtra: isExtra,
                });
            });

            var visibleTraces = traces.filter((trace) => trace.visible === true);
            var allYValues = visibleTraces.flatMap((trace) => trace.y);
            var maxValue = Math.max(...allYValues);
            var minValue = Math.min(...allYValues);
            var pad = (maxValue - minValue) * 0.5;

            var yTitleText = this.yAxisUnit ? this.yAxisUnit : this.yAxisTitle;

            var titleText = this.plotName + (this.yAxisUnit ? ' (' + this.yAxisUnit + ')' : '');
            var wrappedTitle = this.wrapTitle(titleText, 22);

            var layout = {
                width: this.getPlotWidth(),
                height: 380,
                title: {
                    text: '<b>' + wrappedTitle + '</b>',
                    font: { size: 13 },
                    x: 0.5,
                    xanchor: 'center',
                },
                yaxis: {
                    title: {
                        text: '<b>' + yTitleText + '</b>',
                        font: { size: 11 },
                    },
                    tickfont: { size: 10, family: 'Arial Black, Arial, sans-serif' },
                    range: [minValue - pad, maxValue + pad],
                    automargin: true,
                    zeroline: false,
                    showline: true,
                    linewidth: 2,
                    linecolor: 'black',
                    mirror: false,
                },
                xaxis: {
                    showticklabels: false,
                    showline: true,
                    linewidth: 2,
                    linecolor: 'black',
                    zeroline: false,
                    mirror: false,
                },
                margin: { t: 55, r: 15, b: 60, l: 55 },
                showlegend: true,
                legend: {
                    orientation: 'h',
                    y: -0.15,
                    yanchor: 'top',
                    x: 0.5,
                    xanchor: 'center',
                    font: { size: 10 },
                },
                plot_bgcolor: 'white',
                paper_bgcolor: 'white',
            };

            var config = {
                responsive: true,
                displayModeBar: 'hover',
                modeBarButtonsToRemove: ['zoom2d', 'pan2d', 'select2d', 'lasso2d', 'zoomIn2d', 'zoomOut2d', 'autoScale2d', 'resetScale2d'],
                displaylogo: false
            };

            var finalizePlot = function() {
                this.plotElement = plottingElement;
                this.traceMeta = traceMeta;
                if (this.pendingExtraSelection.length > 0) {
                    this.applyExtraVisibility(this.pendingExtraSelection);
                    this.pendingExtraSelection = [];
                } else {
                    this.applyExtraVisibility([]);
                }
            }.bind(this);

            var plotPromise = window.Plotly.newPlot(plottingElement, traces, layout, config);
            if (plotPromise && typeof plotPromise.then === 'function') {
                plotPromise.then(finalizePlot);
            } else {
                finalizePlot();
            }

            this._onToggle = (event) => {
                var payload = [];
                if (event && event.detail && Array.isArray(event.detail.selectedAdditionalSampleGroupIds)) {
                    payload = event.detail.selectedAdditionalSampleGroupIds.map(function(id) {
                        return Number(id);
                    });
                }
                this.applyExtraVisibility(payload);
                if (this.plotElement && window.Plotly && window.Plotly.Plots) {
                    window.Plotly.Plots.resize(this.plotElement);
                }
            };

            this._onResize = () => {
                if (this.plotElement && window.Plotly) {
                    window.Plotly.relayout(this.plotElement, { width: this.getPlotWidth() });
                }
            };

            window.addEventListener('comparison-group-toggle', this._onToggle);
            window.addEventListener('resize', this._onResize);
        },

        destroy() {
            if (this._onToggle) {
                window.removeEventListener('comparison-group-toggle', this._onToggle);
                this._onToggle = null;
            }
            if (this._onResize) {
                window.removeEventListener('resize', this._onResize);
                this._onResize = null;
            }
            if (this.plotElement && window.Plotly) {
                window.Plotly.purge(this.plotElement);
            }
            this.plotElement = null;
            this.traceMeta = [];
        },

        applyExtraVisibility(selectedIds) {
            if (!this.plotElement || !this.traceMeta.length) {
                this.pendingExtraSelection = selectedIds;
                return;
            }
            this.traceMeta.forEach((meta) => {
                if (!meta.isExtra) {
                    return;
                }
                var shouldShow = selectedIds.includes(meta.sampleGroupId);
                window.Plotly.restyle(this.plotElement, { visible: shouldShow ? true : 'legendonly' }, meta.traceIndex);
            });
        }
    }"
>
    <div id="plotting-{{ $randomID }}" style="height: 380px; width: 100%;" wire:ignore></div>
</div>