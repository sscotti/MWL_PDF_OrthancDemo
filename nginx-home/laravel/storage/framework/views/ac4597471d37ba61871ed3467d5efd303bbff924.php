<?php

use App\Helpers\Widgets;
use App\Models\Referrers\ReferringPhysician;
use App\Models\Orders\Orders;
use App\Helpers\DatabaseFactory;
use Illuminate\Support\Facades\Log;

?>
<?php if (isset($component)) { $__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da = $component; } ?>
<?php $component = $__env->getContainer()->make(App\View\Components\AppLayout::class, []); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header'); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Orders')); ?>

        </h2>
     <?php $__env->endSlot(); ?>
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
    
<?php if (isset($component)) { $__componentOriginal6b44892cf265e56cdfc262f28fc33c7c88b51cc3 = $component; } ?>
<?php $component = $__env->getContainer()->make(App\View\Components\Myjs::class, []); ?>
<?php $component->withName('myjs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?>
<?php if (isset($__componentOriginal6b44892cf265e56cdfc262f28fc33c7c88b51cc3)): ?>
<?php $component = $__componentOriginal6b44892cf265e56cdfc262f28fc33c7c88b51cc3; ?>
<?php unset($__componentOriginal6b44892cf265e56cdfc262f28fc33c7c88b51cc3); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?> 

 <script nonce= "<?php echo e(csp_nonce()); ?>">

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

<script nonce= "<?php echo e(csp_nonce()); ?>">

</script>
 <?php if (isset($__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da)): ?>
<?php $component = $__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da; ?>
<?php unset($__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?><?php /**PATH /nginx-home/laravel/resources/views/patientportal/orders.blade.php ENDPATH**/ ?>