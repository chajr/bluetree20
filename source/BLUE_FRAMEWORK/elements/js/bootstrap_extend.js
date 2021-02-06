/**
 * @category    BlueFramework
 * @package     js
 * @subpackage  bootstrap
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.2.0
 */

/**
 * hide element after time
 */
(function ($, window, undefined)
{
    $.fn.autoClose = function()
    {
        var time = $(this).data('auto-close');
        $(this).delay(time).slideUp('slow');
    }

    $(document).ready(function()
    {
        $('[data-auto-close]').autoClose();
    });
})(jQuery, this);

/**
 * automatic lunch tooltip and popover
 */
$('[data-toggle="tooltip"]').tooltip();
$('[data-toggle="popover"]').popover();