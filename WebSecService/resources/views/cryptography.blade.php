@extends('layouts.master')
@section('title', 'Cryptography')
@section('content')
<div class="card m-4">
    <div class="card-header">
        <h3>Cryptography Tools</h3>
    </div>
    <div class="card-body">
        <form id="cryptoForm">
            <div class="mb-3">
                <label for="operation" class="form-label">Operation</label>
                <select class="form-select" id="operation" name="operation">
                    <option value="encrypt">Encrypt</option>
                    <option value="decrypt">Decrypt</option>
                    <option value="hash">Hash</option>
                    <option value="sign">Sign</option>
                    <option value="verify">Verify</option>
                    <option value="rsa">RSA Key Generation</option>
                    <option value="key_send">Key Send with RSA</option>
                    <option value="key_receive">Key Receive</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="text_data" class="form-label">Text Data</label>
                <textarea class="form-control" id="text_data" name="text_data" rows="5"></textarea>
            </div>
            
            <div class="mb-3" id="result_field_container" style="display: none;">
                <label for="result_field" class="form-label">Additional Data</label>
                <textarea class="form-control" id="result_field" name="result_field" rows="5" placeholder="For verify: enter hash to verify against. For RSA operations: enter key"></textarea>
            </div>
            
            <button type="button" id="processBtn" class="btn btn-primary">Process</button>
            
            <div class="mt-4">
                <label for="result" class="form-label">Result</label>
                <div class="alert" id="status_alert" role="alert" style="display: none;"></div>
                <textarea class="form-control" id="result" rows="8" readonly></textarea>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const operationSelect = document.getElementById('operation');
    const resultFieldContainer = document.getElementById('result_field_container');
    const resultField = document.getElementById('result_field');
    const processBtn = document.getElementById('processBtn');
    const resultTextarea = document.getElementById('result');
    const statusAlert = document.getElementById('status_alert');
    
    // Show/hide result field based on operation
    operationSelect.addEventListener('change', function() {
        const operation = this.value;
        if (operation === 'verify' || operation === 'key_send' || operation === 'key_receive') {
            resultFieldContainer.style.display = 'block';
            
            if (operation === 'verify') {
                resultField.placeholder = 'Enter hash to verify against';
            } else if (operation === 'key_send') {
                resultField.placeholder = 'Enter public key for encryption';
            } else if (operation === 'key_receive') {
                resultField.placeholder = 'Enter private key for decryption';
            }
        } else {
            resultFieldContainer.style.display = 'none';
        }
    });
    
    // Process button click
    processBtn.addEventListener('click', function() {
        const formData = new FormData(document.getElementById('cryptoForm'));
        
        fetch('/cryptography/process', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                operation: formData.get('operation'),
                text_data: formData.get('text_data'),
                result_field: formData.get('result_field')
            })
        })
        .then(response => response.json())
        .then(data => {
            resultTextarea.value = data.result;
            
            // Show status alert
            statusAlert.style.display = 'block';
            if (data.status === 'success') {
                statusAlert.className = 'alert alert-success';
                statusAlert.textContent = 'Operation completed successfully';
            } else {
                statusAlert.className = 'alert alert-danger';
                statusAlert.textContent = 'Operation failed';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultTextarea.value = 'An error occurred while processing your request.';
            statusAlert.style.display = 'block';
            statusAlert.className = 'alert alert-danger';
            statusAlert.textContent = 'Error: ' + error.message;
        });
    });
});
</script>
@endsection
