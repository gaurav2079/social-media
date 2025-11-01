$(function(){
    console.log("Comment JS loaded");

    // Show comment popup
    $(document).on('click','.comment', function(){
        var tweet_id    = $(this).data('tweet');
        var user_id     = $(this).data('user');
        
        console.log("Comment clicked - Tweet ID:", tweet_id);
        console.log("Comment clicked - User ID:", user_id);
        
        $.post('core/ajax/comment.php', {showPopup:tweet_id,user_id:user_id}, function(data){
            $('.popupComment').html(data);
            $('.retweet-popup').show();
            
            $('.close-retweet-popup').click(function(){
                $('.retweet-popup').hide();
            });
        });
    });

    // Submit comment - COMPLETELY FIXED
    $(document).on('click', '.comment-it', function(event){
        event.preventDefault();
        event.stopPropagation();
        
        var tweet_id   = $(this).data('tweet');
        var user_id    = $(this).data('user');
        
        // DEBUG: Check what we're finding
        console.log("Looking for retweet-msg input...");
        console.log("Number of .retweet-msg elements:", $('.retweet-msg').length);
        console.log("Number of .retweet-popup elements:", $('.retweet-popup').length);
        
        // MULTIPLE WAYS TO GET THE COMMENT VALUE:
        // Method 1: Direct from the popup
        var comment = $('.retweet-popup .retweet-msg').val();
        
        // Method 2: If method 1 doesn't work, try finding the closest input
        if (!comment) {
            comment = $(this).closest('.retweet-popup').find('.retweet-msg').val();
        }
        
        // Method 3: If still not found, try the first one
        if (!comment) {
            comment = $('.retweet-msg').first().val();
        }
        
        // Method 4: Last resort - find any input in the popup
        if (!comment) {
            comment = $('.retweet-popup input[type="text"]').val();
        }
        
        console.log("Final comment value:", comment);

        if (!comment || comment.trim() === '') {
            alert('Please enter a comment');
            return false;
        }

        // Show loading state
        var $button = $(this);
        var originalText = $button.text();
        $button.prop('disabled', true).text('Posting...');

        $.ajax({
            url: 'core/ajax/comment.php',
            type: 'POST',
            data: {
                qoute: tweet_id,
                user_id: user_id,
                comment: comment
            },
            success: function(response){
                console.log("Server response:", response);
                
                $('.retweet-popup').hide();
                alert('Comment posted successfully!');
                location.reload();
            },
            error: function(xhr, status, error){
                console.error("AJAX Error:", status, error);
                console.log("Response text:", xhr.responseText);
                alert('Error posting comment: ' + error);
                $button.prop('disabled', false).text(originalText);
            }
        });
    });

    // Show reply popup for comments
    $(document).on('click','.reply', function(){
        var tweet_id    = $(this).data('tweet');
        var user_id     = $(this).data('user');
        
        console.log("Reply clicked - Comment ID:", tweet_id);
        console.log("Reply clicked - User ID:", user_id);
        
        $.post('core/ajax/comment.php', {showReply:tweet_id,user_id:user_id}, function(data){
            $('.popupComment').html(data);
            $('.retweet-popup').show();
            
            $('.close-retweet-popup').click(function(){
                $('.retweet-popup').hide();
            });
        });
    });

    // Submit reply - COMPLETELY FIXED
    $(document).on('click', '.reply-it', function(event){
        event.preventDefault();
        event.stopPropagation();
        
        var comment_id   = $(this).data('tweet');
        var user_id    = $(this).data('user');
        
        // MULTIPLE WAYS TO GET THE REPLY VALUE:
        var comment = $('.retweet-popup .retweet-msg').val();
        
        if (!comment) {
            comment = $(this).closest('.retweet-popup').find('.retweet-msg').val();
        }
        
        if (!comment) {
            comment = $('.retweet-msg').first().val();
        }
        
        if (!comment) {
            comment = $('.retweet-popup input[type="text"]').val();
        }
        
        console.log("Final reply value:", comment);

        if (!comment || comment.trim() === '') {
            alert('Please enter a reply');
            return false;
        }

        // Show loading state
        var $button = $(this);
        var originalText = $button.text();
        $button.prop('disabled', true).text('Posting...');

        $.ajax({
            url: 'core/ajax/comment.php',
            type: 'POST',
            data: {
                reply: comment_id,
                user_id: user_id,
                comment: comment
            },
            success: function(response){
                console.log("Server response:", response);
                
                $('.retweet-popup').hide();
                alert('Reply posted successfully!');
                location.reload();
            },
            error: function(xhr, status, error){
                console.error("AJAX Error:", status, error);
                console.log("Response text:", xhr.responseText);
                alert('Error posting reply: ' + error);
                $button.prop('disabled', false).text(originalText);
            }
        });
    });

    // Close popup when clicking outside
    $(document).on('click', '.retweet-popup', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });

    // Close popup with escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.retweet-popup').hide();
        }
    });

    // Add real-time input monitoring
    $(document).on('input', '.retweet-msg', function(){
        var value = $(this).val();
        console.log("Input changed to:", value);
    });
});