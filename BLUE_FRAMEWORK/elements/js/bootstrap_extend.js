/**
 * @category    BlueFramework
 * @package     js
 * @subpackage  bootstrap
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.1.0
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