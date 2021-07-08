<?php

use App\Helpers\Widgets;
use App\Models\Referrers\ReferringPhysician;
use App\Models\Orders\Orders;
use App\Helpers\DatabaseFactory;
use Illuminate\Support\Facades\Log;

?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Orders') }}
        </h2>
    </x-slot>
    <hr>
    <div class = "listwrapper">
    <div class="container mt-5">
        <table class="table table-bordered yajra-datatable" id = "orders">
            <thead>
                <tr>
                    <th>Last</th>
                    <th>First</th>
                    <th>DOB</th>
                    <th>Sex</th>
                    <th>MRN</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Last Update</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    </div>
    
<x-myjs /> 

 <script nonce= "{{ csp_nonce() }}">

  $(function () {
    
    var table = $('#orders').DataTable({
    
        processing: true,
        serverSide: true,
        responsive: true,
        pageLength: 10,
        order: [[ 8, "desc" ]], // sort by request date descending
        ajax: {
            url:  '/patientportal/ordersdatatable',
            type: 'POST'
        },
        
        columns: [
            {data: 'patient_lname', name: 'patient_lname'},
            {data: 'patient_fname', name: 'patient_fname'},
            {data: 'patient_birth_date', name: 'patient_birth_date'},
            {data: 'patient_sex', name: 'patient_sex'},
            {data: 'patientid', name: 'patientid'},
            {data: 'requested_procedure_id', name: 'requested_procedure_id'},
            {data: 'scheduled_procedure_step_start_date', name: 'scheduled_procedure_step_start_date'},
            {data: 'scheduled_procedure_step_start_time', name: 'scheduled_procedure_step_start_time'},
            {data: 'timestamp', name: 'timestamp'},
        ],
        "lengthMenu": [ 2,5,10, 25, 50, 75, 100 ]
    });
    
    
  });
</script>   
<style>
	
	.form-group orderform {
	    text-align:right;
	}
	.orderformbuttons {
	    text-align: center;
	}

</style>

<script nonce= "{{ csp_nonce() }}">

</script>
</x-app-layout>