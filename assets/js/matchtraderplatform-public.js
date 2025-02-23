(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Variables for animation and form state
        var animating = false;
        
        // Handle next button click
        $(".next").click(function() {
            if(animating) return false;
            animating = true;
            
            var current_fs = $(this).parent();
            var next_fs = $(this).parent().next();
            
            // Update progress bar
            $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
            
            // Show next fieldset and animate transition
            next_fs.show();
            current_fs.animate({opacity: 0}, {
                step: function(now, mx) {
                    var scale = 1 - (1 - now) * 0.2;
                    var left = (now * 50) + "%";
                    var opacity = 1 - now;
                    
                    current_fs.css({
                        'transform': 'scale(' + scale + ')',
                        'position': 'absolute'
                    });
                    next_fs.css({
                        'left': left,
                        'opacity': opacity
                    });
                },
                duration: 800,
                complete: function() {
                    current_fs.hide();
                    animating = false;
                },
                easing: 'easeInOutBack'
            });
        });
        
        // Handle previous button click
        $(".previous").click(function() {
            if(animating) return false;
            animating = true;
            
            var current_fs = $(this).parent();
            var previous_fs = $(this).parent().prev();
            
            // Update progress bar
            $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");
            
            // Show previous fieldset and animate transition
            previous_fs.show();
            current_fs.animate({opacity: 0}, {
                step: function(now, mx) {
                    var scale = 0.8 + (1 - now) * 0.2;
                    var left = ((1-now) * 50) + "%";
                    var opacity = 1 - now;
                    
                    current_fs.css({'left': left});
                    previous_fs.css({
                        'transform': 'scale(' + scale + ')',
                        'opacity': opacity
                    });
                },
                duration: 800,
                complete: function() {
                    current_fs.hide();
                    animating = false;
                },
                easing: 'easeInOutBack'
            });
        });
        
        // Handle submit button click
        $(".submit").click(function() {
            return false;
        });
    });
    
})(jQuery);