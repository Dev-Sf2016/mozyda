(function($){
    $.validator.setDefaults({
        errorElement: 'li',
        errorPlacement: function(error, element) {
            var label = $('label[for="'+$(element).attr('id')+'"]');
            var input = $('input[id="'+$(element).attr('id')+'"]');
            var container = label.next('ul');
            if (container.length === 1) {
                console.log('if');
                container.empty();
            } else {
                console.log('else');
                container = $('<ul></ul>').insertAfter(input);
            }
            container.append(error);
        },
        highlight: function(element) {
            $(element).closest('.form-group').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).closest('.form-group').removeClass('has-error');        },
        ignore: function(idx, elt) {
            // Only validate a hidden field when it has a rule attached.
            return $(elt).is(':hidden') && $.isEmptyObject($( this ).rules());
        }
    });
})(jQuery);