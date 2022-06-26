<?php if(session()->has('msg')): ?>
    <div class="alert alert-<?php echo e(session('type')); ?>">
        <?php echo Purifier::clean(session('msg')); ?>

    </div>
<?php endif; ?>
<?php /**PATH /Users/asifraza/Documents/proejcts/laravel/ext/adeel/service_onDemand/resources/views/components/msg/success.blade.php ENDPATH**/ ?>