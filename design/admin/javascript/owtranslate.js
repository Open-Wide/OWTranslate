$(document).ready(function() {
    $('.edit').editable('/translate/ajax_edit', {
        cssclass : 'translation_edit',
        indicator : 'Saving...',
        tooltip   : 'Click to edit...'
    });     
});