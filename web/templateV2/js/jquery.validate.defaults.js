(function($){
    $.validator.setDefaults({
        errorElement: 'span',
        errorClass: 'help-block',
        msg: 'welcome msg',
        errorPlacement: function(error, element) {
            var serverError = $('#' + error.attr('id'), element.parent());
            if (serverError.length > 0) { serverError.remove(); }

            if(element.parent('.input-group').length) {
                error.insertAfter(element.parent());

            } else {
                // element.parent().appendTo'<span class="glyphicon glyphicon-exclamation-sign"></span>');
                // error.appendTo(element.parent('span').next("welcome"));
                error.insertAfter(element);
            }
        },
        highlight: function(element) {
            // $(element).closest('.form-group').addClass('has-error');
        },
        unhighlight: function(element) {
            // $(element).closest('.form-group').removeClass('has-error');
        },
        ignore: function(idx, elt) {
            // We don't validate hidden fields expect if they have rules attached.
            return $(elt).is(':hidden') && $.isEmptyObject($( this ).rules());
        }
    });
})(jQuery);