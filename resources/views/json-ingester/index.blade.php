@extends('layout')

@section('content')
<div class="container">

    @if (session('identifier'))
        <div class="alert alert-success">
            JSON data ingested successfully. Identifier: {{ session('identifier') }}
        </div>
    @endif

    <h2>JSON Ingester</h2>
    <div class="mb-3">
        <label for="jsonFile" class="form-label">Upload JSON File</label>
        <input type="file" class="form-control" id="jsonFile" name="jsonFile" accept=".json">
    </div>
    <div class="mb-3">
        <label for="jsonData" class="form-label">Or paste JSON data</label>
        <textarea class="form-control" id="jsonData" name="jsonData" rows="10"></textarea>
    </div>

    <button id="submit-json" type="submit" class="btn btn-primary">Submit</button>

    <div class="mb-3">
        <div id="notification" class="alert alert-info mt-3" style="display: none;">
            JSON data ingested successfully. Identifier: <span id="identifier"></span></br>
            Link to study: <a id="study_link" href="#"></span>View Study</a>
        </div>
    </div>

    <div class="mb-3">
        <div id="error-box" class="alert alert-danger mt-3" style="display: none;">
            <strong>Error:</strong> <span id="error-message"></span>
    </div>

    <script>

        // On file submit, set contents to insde the textarea
        document.getElementById('jsonFile').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('jsonData').value = e.target.result;
                };
                reader.readAsText(file);
            }
        });

        const submitButton = document.getElementById('submit-json');
        submitButton.addEventListener('click', async function() {
            const jsonData = document.getElementById('jsonData').value;
            const csrfToken = '{{ csrf_token() }}';

            const response = await fetch('{{ route('json-ingester.index') }}/ingest', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken // TODO: We need to disable required CSRF token for this endpoint because requests won't be coming from our domain
                },
                body: JSON.stringify({ data: jsonData })
            });

            const json = await response.json();

            if (!json.success) {
                document.getElementById('error-box').style.display = 'block';
                const error = json.error || 'An unknown error occurred.';
                document.getElementById('error-message').textContent = error;
                throw new Error('JSON ingest failed with: ' + error);
            }

            // Notify the identifier property from json
            document.getElementById('identifier').textContent = json.identifier;
            document.getElementById('study_link').href = `/study/${json.study_id}/study-information`;
            document.getElementById('notification').style.display = 'block';
            document.getElementById('jsonData').value = '';
            document.getElementById('error-box').style.display = 'none';
        });
    </script>
</div>
@endsection
