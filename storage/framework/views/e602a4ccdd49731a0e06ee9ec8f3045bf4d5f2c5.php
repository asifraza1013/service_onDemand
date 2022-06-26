$(document).on('click','#submit',function () {
    $(this).addClass("disabled")
    $(this).html('<i class="fas fa-spinner fa-spin mr-1"></i> <?php echo e(__("Submitting")); ?>');
});<?php /**PATH /Users/asifraza/Documents/proejcts/laravel/ext/adeel/service_onDemand/resources/views/components/btn/submit.blade.php ENDPATH**/ ?>