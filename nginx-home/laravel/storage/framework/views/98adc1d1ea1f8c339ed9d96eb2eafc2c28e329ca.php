<?php
use App\Models\Referrers\ReferringPhysician;
use Illuminate\Support\Facades\Log;
$doctor = ReferringPhysician::where('identifier', $doctor_id)->first();
?>
<?php if (isset($component)) { $__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da = $component; } ?>
<?php $component = $__env->getContainer()->make(App\View\Components\AppLayout::class, []); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header'); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Practice Profile')); ?>

        </h2>
     <?php $__env->endSlot(); ?>
    <?php echo $__env->make('referrers.referrers_demographics', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div id = "licensewrapper">
    <?php echo $__env->make('referrers.referrers_licenses', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
    <div class="form-group shadedform row">
        <div class="col-form-label-sm col-sm-2" id="add_license" style="cursor:pointer" data-id= "<?php echo $doctor->id ?>" data-identifier = "<?php echo $doctor_id ?>">Add License: <i class="fas fa-plus"></i></div>
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
    
    attachSumoSelect("#demographics");
    attachSumoSelect("#licenselist");
    
    function licenseAction(action, data) {
    
        let identifier = data.identifier;
        
        $.ajax({
            type: "POST",
            url: "/provider_licenses/" + action,
            dataType: "json",
            data: data
        })
        .done(function(data, textStatus, jqXHR) {
            getLicenseList(identifier);
        });
    }
    
    function getLicenseList(identifier) {
	    $.ajax({
            type: "POST",
            url: "/provider_licenses/listlicenses",
            dataType: "html",
            data: {identifier:identifier}
        })
        .done(function(data, textStatus, jqXHR) {
            $("#licenselist").replaceWith(data);
            attachSumoSelect("#licenselist");
        });
    }
    
    $('#add_license').on('click', function(e) {
            data = {"identifier":$(this).data("identifier"),"license_provider_id":$(this).data("id")};
            licenseAction('addlicense', data);
    });
    $('#licensewrapper').on('click', '.deletelicense', function(e) {
            data = {"license_id" : $(this).closest("form").find("[name='license_id']").val(),"identifier":$(this).closest("form").find("[name='license_provider_identifier']").val(),"id":$(this).closest("form").find("[name='license_provider_id']").val()};
            licenseAction('deletelicense', data);
    });
    
    </script>
 <?php if (isset($__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da)): ?>
<?php $component = $__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da; ?>
<?php unset($__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?><?php /**PATH /nginx-home/laravel/resources/views/referrers/referrers_profile.blade.php ENDPATH**/ ?>