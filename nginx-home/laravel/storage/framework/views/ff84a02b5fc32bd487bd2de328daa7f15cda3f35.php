<?php
$pdf = 'data:application/pdf;base64,'.base64_encode(file_get_contents(config('view.paths')[0] . "/privacy_policy.pdf"));
echo '<iframe src="' .$pdf . '" width="100%" style="height:100%"></iframe>';
?><?php /**PATH /nginx-home/laravel/resources/views/terms.blade.php ENDPATH**/ ?>