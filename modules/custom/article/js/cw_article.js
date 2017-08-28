jQuery(window).load(function() {
    // Remove empty space from summary
    var currentSummary = jQuery('textarea.text-summary');
    var actionButtons = jQuery('#edit-actions .dropbutton-multiple');
    if(currentSummary.val().trim().length === 0){
        currentSummary.val('');
        actionButtons.addClass('disabled');
    }

    actionButtons.click(function(e){
        if(jQuery(this).hasClass('disabled')){
            e.stopPropagation();
            alert('please fill in the summary !');
            return false;
        }
    });

    currentSummary.on('change', function(){
        currentSummary.val().trim().length===0?actionButtons.addClass('disabled'):actionButtons.removeClass('disabled');
    });
});