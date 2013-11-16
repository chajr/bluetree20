$(document).ready(function()
{
    input  = $('#form input[name="chk[]"]').clone();
    parent = $('#form input[name="chk[]"]');

    input.attr('value', 2);
    parent.after(input);

    parent = $('#form input[name="chk[]"]');
    parent.after(input);

    parent = $('#form input[name="chk[]"]');
    parent.after(input);

    input  = $('#form input[name="rad[]"]').clone();
    parent = $('#form input[name="rad[]"]');

    input.attr('value', 2)
    parent.after(input);

    parent = $('#form input[name="rad[]"]');
    input.attr('value', 3)
    parent.after(input);

    parent = $('#form input[name="rad[]"]');
    input.attr('value', 4)
    parent.after(input);

    input = $('#form input[name="input[]"]').clone();
    parent = $('#form input[name="input[]"]');
    parent.after(input);

    parent = $('#form input[name="input[]"]');
    parent.after(input);
});