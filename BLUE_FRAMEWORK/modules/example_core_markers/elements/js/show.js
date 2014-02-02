$('head').find('link').each(function()
{
    var element = $(this).clone();
    $('#css_links pre').append(element);
    var replace = $('#css_links pre');
    replace.html(replace.html().replace(/</, '&lt;'));
    replace.html(replace.html().replace(/>/, '&gt;'));
});
$('body').find('script').each(function()
{
    var element = $(this).clone();
    $('#js_links pre').append(element);
    var replace = $('#js_links pre');
    replace.html(replace.html().replace(/</, '&lt;'));
    replace.html(replace.html().replace(/>/, '&gt;'));
});
$('head').find('meta').each(function()
{
    var element = $(this).clone();
    $('#meta_tags pre').append(element);
    var replace = $('#meta_tags pre');
    replace.html(replace.html().replace(/</, '&lt;'));
    replace.html(replace.html().replace(/>/, '&gt;'));
});