@extends('layout')

@section('title', 'Grant Resources')
@section('description', 'Ready-to-use data sharing and repository language for NIH grant applications, from the Rodent Open Science Skeletal Archive.')

@section('content')
<div class="grant-resources-page">
<div class="container pt-4 pb-5">
	<div class="row justify-content-center">
		<div class="col-lg-10 col-xl-9">

			<div class="news-hero-band rounded-3 px-4 py-4 px-md-5 py-md-4 mb-3">
				<h1 class="text-white mb-2 fs-2">Grant Resources</h1>
				<p class="lead mb-0 news-hero-subtitle">Ready-to-use language for NIH data management and sharing requirements.</p>
			</div>

			<h2 class="h4 fw-bold text-dark mb-3">
				Using ROSSA in Your Grant Application
			</h2>
			<p>
				The statement below is provided as a starting point for investigators preparing NIH applications.
				It was written to help you address the NIH Data Management and Sharing Policy, including the
				expectation that scientific data be shared through an established repository. You are welcome to
				copy, edit, and adapt it to fit your study, your data types, and your application format.
				Highlighted text marks details that will vary by project and should be replaced with your own information.
			</p>

			<div class="card shadow-sm my-4">
				<div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
					<h2 class="h5 mb-0">Data Sharing and Repository Plan</h2>
					<button type="button" class="btn btn-sm btn-outline-primary" id="copyDmsButton" aria-describedby="copyDmsStatus">
						Copy text
					</button>
				</div>
				<div class="card-body">
					<div id="dmsStatement" class="grant-statement">
						<p class="mb-0">
							Scientific data generated in this project, including
							<mark>[micro-CT, histology, and other skeletal phenotyping data; edit to match the project]</mark>
							and associated metadata, will be deposited in the Rodent Open Science Skeletal Archive
							(ROSSA; rossa.uchc.edu), an NIH-supported domain-specific repository listed on the NIH Office
							of Data Science Strategy's list of open domain-specific data sharing repositories. In keeping
							with NIH guidance to prefer established domain-specific repositories when one is available,
							ROSSA was selected because it is built specifically to serve the skeletal biology community.
							Unlike general-purpose repositories, which store files without regard to data type, ROSSA is
							designed around rodent skeletal phenotyping data and supports rapid searching, filtering, and
							in-browser viewing of datasets, so that other investigators can find, evaluate, and reuse our
							data without first downloading and processing it. ROSSA collects extensive structured metadata
							describing each study, including
							<mark>[animal model, genotype, age, sex, and imaging or assay parameters]</mark>,
							and assigns persistent identifiers to deposited datasets, which supports discoverability,
							citation, and long-term reuse. Data can be viewed without a login and are freely available to
							academic and not-for-profit users; programmatic (API) access requires a login. Data will be
							deposited <mark>[no later than the time of associated publication]</mark>.
						</p>
					</div>
				</div>
				<div class="card-footer text-muted small">
					<span id="copyDmsStatus" role="status" aria-live="polite">
						Pasting into a grant system? The copied text is plain, unformatted text.
					</span>
				</div>
			</div>

			<h2 class="h5 mt-5">Repository listing</h2>
			<p>
				ROSSA is listed by the NIH as a domain-specific data sharing repository.
				<a href="https://www.nlm.nih.gov/NIHbmic/domain_specific_repositories.html" target="_blank" rel="noopener noreferrer">
					View the NIH listing of domain-specific repositories<span class="visually-hidden"> (opens in a new tab)</span>
				</a>.
			</p>

			<h2 class="h5 mt-4">Questions</h2>
			<p>
				If you have questions about using ROSSA in an application, please
				<a href="{{ route('contact') }}">contact us</a>.
			</p>

		</div>
	</div>
</div>
</div>
@endsection

@push('scripts')
<script>
	(function () {
		var button = document.getElementById('copyDmsButton');
		var source = document.getElementById('dmsStatement');
		var status = document.getElementById('copyDmsStatus');
		if (!button || !source) { return; }

		var defaultStatus = status ? status.textContent : '';

		function getText() {
			return source.innerText.replace(/\s+/g, ' ').trim();
		}

		function flash(message, label) {
			if (status) { status.textContent = message; }
			button.textContent = label;
			setTimeout(function () {
				button.textContent = 'Copy text';
				if (status) { status.textContent = defaultStatus; }
			}, 2500);
		}

		function fallbackCopy(text) {
			var area = document.createElement('textarea');
			area.value = text;
			area.setAttribute('readonly', '');
			area.style.position = 'absolute';
			area.style.left = '-9999px';
			document.body.appendChild(area);
			area.select();
			var ok = false;
			try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
			document.body.removeChild(area);
			return ok;
		}

		button.addEventListener('click', function () {
			var text = getText();
			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(text).then(function () {
					flash('Statement copied to clipboard.', 'Copied');
				}, function () {
					var ok = fallbackCopy(text);
					flash(ok ? 'Statement copied to clipboard.' : 'Copy failed. Please select the text and copy it manually.',
						ok ? 'Copied' : 'Copy failed');
				});
			} else {
				var ok = fallbackCopy(text);
				flash(ok ? 'Statement copied to clipboard.' : 'Copy failed. Please select the text and copy it manually.',
					ok ? 'Copied' : 'Copy failed');
			}
		});
	})();
</script>
@endpush
