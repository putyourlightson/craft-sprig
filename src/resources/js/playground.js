htmx.on('htmx:afterSwap', function(event) {
  $('#sourcecode').val($('#playground').html());
});

$('#run').click(function() {
    $('#playground').append('<input type="hidden" name="component" value="' + $('#component').val() + '">');

    var variables = $('#variables').val().replace(' ', '').split(',');

    $.each(variables, function(index, value) {
        var keyValue = value.split('=');
        $('#playground').append('<input type="hidden" name="variables[' + keyValue[0] + ']" value="' + keyValue[1] + '">');
    });

    document.getElementById('playground').dispatchEvent(new Event('refresh'));
});

