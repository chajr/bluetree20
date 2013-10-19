$(document).ready(function()
{
    $('#buttons button').click(function()
    {
        type = $(this).data('type');
        duplicateBlock(type);
    });

    $('body').on('click', '#messages .buttons_method button', function()
    {
        parent  = $(this).parent().parent();
        type    = $(this).data('type');
        duplicateDescription(type, parent);
    });

    $('body').on('click', '#messages .file_label button', function()
    {
        $(this).parent().parent().remove();
    });

    $('body').on('click', '#messages .file_textarea button', function()
    {
        $(this).parent().remove();
    });

    $('#submit').click(function()
    {
        $('#commit_generator_form').submit();
    });
});

function duplicateBlock(element)
{
    fullBlock = $('#empty_messages .message_' + element).clone();

    $('#content #messages').append(fullBlock);
}

function duplicateDescription(type, parent)
{
    fullBlock       = $('#empty_messages .file_textarea').clone();
    placeholder     = fullBlock.find('textarea').attr('placeholder');
    newPlaceholder  = type + ' ' + placeholder;

    fullBlock.find('textarea').attr('placeholder', newPlaceholder);
    parent.append(fullBlock);
}