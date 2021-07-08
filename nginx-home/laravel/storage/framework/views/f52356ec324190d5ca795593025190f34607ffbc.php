<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <meta name="robots" content="none" />
        <title><?php echo e(config('app.name', 'Laravel')); ?></title>

        <!-- Many libraries are are Via Laravel Mix in /resources/js/app.js && /resources/sass/app.scss -->
        <link rel="icon" href="data:;base64,iVBORw0KGgo=">
        <link rel="stylesheet" href="/bower/jquery-ui/themes/dark-hive/jquery-ui.min.css" type="text/css">
        <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
        <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
        <link rel="stylesheet" href="<?php echo e(asset('css/my.css')); ?>">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.24/b-1.7.0/cr-1.5.3/date-1.0.2/r-2.2.7/sb-1.0.1/sp-1.2.2/datatables.min.css"/>
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <?php echo \Livewire\Livewire::styles(); ?>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script>
        <script src="/bower/jquery/dist/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/3.0.1/jquery-migrate.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
        <script src="/bower/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="/bower/jquery-ui/jquery-ui.min.js"></script>
        <script src="/bower/jquery-timepicker-jt/jquery.timepicker.min.js"></script>
        <script src="/bower/moment/min/moment.min.js"></script>
        <script src="/bower/moment-timezone/builds/moment-timezone-with-data-1970-2030.min.js"></script>
        <script src="/bower/jquery-validation/dist/jquery.validate.min.js"></script>
        <script src="/bower/jquery-validation/dist/additional-methods.min.js"></script>
        <script src="/bower/sumoselect/jquery.sumoselect.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.24/b-1.7.0/cr-1.5.3/date-1.0.2/r-2.2.7/sb-1.0.1/sp-1.2.2/datatables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
        <script src="<?php echo e(asset('js/app.js')); ?>"></script>
        <script nonce="<?php echo e(csp_nonce()); ?>">
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                beforeSend: function(xhr) {
                    $("body").addClass("loading");
                    // xhr.setRequestHeader('custom-header', 'some value');
                },
            });
        </script>
        
    </head>
    <body class="font-sans antialiased">
        <div class="spinner_overlay"></div>
        <div class="font-sans text-gray-900 antialiased">
            <?php echo e($slot); ?>

        </div>
        <?php if (isset($component)) { $__componentOriginal45d2dce3b6e23e3648270c5a8b7bfdd46e45fa69 = $component; } ?>
<?php $component = $__env->getContainer()->make(App\View\Components\AppFooter::class, []); ?>
<?php $component->withName('AppFooter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?>
<?php if (isset($__componentOriginal45d2dce3b6e23e3648270c5a8b7bfdd46e45fa69)): ?>
<?php $component = $__componentOriginal45d2dce3b6e23e3648270c5a8b7bfdd46e45fa69; ?>
<?php unset($__componentOriginal45d2dce3b6e23e3648270c5a8b7bfdd46e45fa69); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
        <?php echo $__env->yieldPushContent('modals'); ?>
        <?php $nonce = ["nonce" => csp_nonce()] ?>
        <?php echo \Livewire\Livewire::scripts($nonce); ?>

        <!-- The Modal -->
        <div class="modal fade hide" id="myModal" data-keyboard="true" data-backdrop="true" tabindex='-1'>

            <div class="modal-dialog">
            <div class="modal-content">

              <!-- Modal Header -->
              <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
              </div>

              <!-- Modal body -->
              <div class="modal-body"></div>

              <!-- Modal footer -->
              <div class="modal-footer">
                <button type="button" class="uibuttonsmallred" data-dismiss="modal">Close</button>
              </div>

            </div>
            </div>
        </div>
<?php
    $modal_message = session('modal_message', false);
    if ($modal_message !== false) {
?>
    <script nonce= "<?php echo e(csp_nonce()); ?>">
    $(window).on('load', function() {
        $('#myModal .modal-body').html('<?php echo $modal_message ?>');
        $('#myModal').modal('show');
    });
    </script>
<?php
    }
?>
        </body>
</html>
<?php /**PATH /nginx-home/laravel/resources/views/layouts/guest.blade.php ENDPATH**/ ?>