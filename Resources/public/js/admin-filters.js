/**
 * Show filter (bug, not shown by default)
 */
window.onload = function() {
    if (jQuery === undefined) {
        return;
    }

    jQuery.find("form.sonata-filter-form select").forEach(function(element) {
        var $el = jQuery(element);
        if ($el.val()) {
            $el.closest('.form-group[sonata-filter="true"]').show();
        } 
    });
}
