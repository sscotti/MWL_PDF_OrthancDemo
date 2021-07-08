<?php
  $title = 'Error '.$error_number;
?>

<?php $__env->startSection('after_styles'); ?>
  <style>
    .error_number {
      font-size: 156px;
      font-weight: 600;
      line-height: 100px;
    }
    .error_number small {
      font-size: 56px;
      font-weight: 700;
    }

    .error_number hr {
      margin-top: 60px;
      margin-bottom: 0;
      width: 50px;
    }

    .error_title {
      margin-top: 40px;
      font-size: 36px;
      font-weight: 400;
    }

    .error_description {
      font-size: 24px;
      font-weight: 400;
    }
  </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
  <div class="col-md-12 text-center">
    <div class="error_number">
      <small>ERROR</small><br>
      <?php echo e($error_number); ?>

      <hr>
    </div>
    <div class="error_title text-muted">
      <?php echo $__env->yieldContent('title'); ?>
    </div>
    <div class="error_description text-muted">
      <small>
        <?php echo $__env->yieldContent('description'); ?>
     </small>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make(backpack_user() && (Str::startsWith(\Request::path(), config('backpack.base.route_prefix'))) ? 'backpack::layouts.top_left' : 'backpack::layouts.plain', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /nginx-home/laravel/resources/views/errors/layout.blade.php ENDPATH**/ ?>