<?php if(config('backpack.base.scripts') && count(config('backpack.base.scripts'))): ?>
    <?php $__currentLoopData = config('backpack.base.scripts'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <script type="text/javascript" src="<?php echo e(asset($path).'?v='.config('backpack.base.cachebusting_string')); ?>"></script>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<?php if(config('backpack.base.mix_scripts') && count(config('backpack.base.mix_scripts'))): ?>
    <?php $__currentLoopData = config('backpack.base.mix_scripts'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $path => $manifest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <script type="text/javascript" src="<?php echo e(mix($path, $manifest)); ?>"></script>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<?php echo $__env->make('backpack::inc.alerts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<!-- page script -->
<script type="text/javascript">
    // To make Pace works on Ajax calls
    $(document).ajaxStart(function() { Pace.restart(); });

    // Ajax calls should always have the CSRF token attached to them, otherwise they won't work
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    
    var activeTab = $('[href="' + location.hash.replace("#", "#tab_") + '"]');
    location.hash && activeTab && activeTab.tab('show');
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        location.hash = e.target.hash.replace("#tab_", "#");
    });
</script><?php /**PATH /nginx-home/laravel/vendor/backpack/crud/src/resources/views/base/inc/scripts.blade.php ENDPATH**/ ?>