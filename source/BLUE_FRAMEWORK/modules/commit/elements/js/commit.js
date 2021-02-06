var blockCounter = 0;

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

/**
 * create message block
 * 
 * @param element string
 */
function duplicateBlock(element)
{
    blockCounter++;
    var fullBlock = $('#empty_messages .message_' + element).clone();

    fullBlock.data('block_counter', blockCounter);
    fullBlock.find('[name="add"]')          .attr('name', 'add_' + blockCounter);
    fullBlock.find('[name="modify"]')       .attr('name', 'modify_' + blockCounter);
    fullBlock.find('[name="modifyVersion"]').attr('name', 'modifyVersion_' + blockCounter);
    fullBlock.find('[name="removed"]')      .attr('name', 'removed_' + blockCounter);
    fullBlock.find('[name="from"]')         .attr('name', 'from_' + blockCounter + '_1');
    fullBlock.find('[name="to"]')           .attr('name', 'to_' + blockCounter + '_1');

    fullBlock.find('textarea[name="added"]').attr(
        'name',
        'added_' + blockCounter + '_1'
    );

    fullBlock.find('textarea[name="remove"]').attr(
        'name',
        'remove_' + blockCounter + '_1'
    );

    $('#content #messages').prepend(fullBlock);
}

/**
 * create description element
 * 
 * @param type string
 * @param parent jQuery object
 */
function duplicateDescription(type, parent)
{
    var fullBlock       = $('#empty_messages > .file_textarea').clone();
    var placeholder     = fullBlock.find('textarea').attr('placeholder');
    var newPlaceholder  = type + ' ' + placeholder;
    var counter         = parent.data('counter');
    var parentCounter   = parent.data('block_counter');

    counter++;
    parent.data('counter', counter);
    var name = type + '_' + parentCounter + '_' + counter;

    fullBlock.find('textarea').attr('placeholder', newPlaceholder);
    fullBlock.find('textarea').attr('name', name);
    parent.append(fullBlock);
}