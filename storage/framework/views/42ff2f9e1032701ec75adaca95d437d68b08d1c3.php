$('.icp-dd').iconpicker();
     $('.icp-dd').on('iconpickerSelected', function (e) {
    var selectedIcon = e.iconpickerValue;
    $(this).parent().parent().children('input').val(selectedIcon);
});<?php /**PATH /Users/asifraza/Documents/proejcts/laravel/ext/adeel/service_onDemand/resources/views/components/icon-picker.blade.php ENDPATH**/ ?>