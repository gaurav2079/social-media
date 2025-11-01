<?php
$user_id = $_SESSION['user_id'];
foreach($tweets as $tweet) { 

$retweet_sign = false;
$retweet_comment =false;
$qoq = false;

if (Tweet::isTweet($tweet->id)) {

$tweet_user = User::getData($tweet->user_id) ;
$tweet_real = Tweet::getTweet($tweet->id);
$timeAgo = Tweet::getTimeAgo($tweet->post_on) ; 
$likes_count = Tweet::countLikes($tweet->id) ;
$user_like_it = Tweet::userLikeIt($user_id ,$tweet->id);
$retweets_count = Tweet::countRetweets($tweet->id) ;
$user_retweeted_it = Tweet::userRetweeetedIt($user_id ,$tweet->id);

} else if (Tweet::isRetweet($tweet->id)) {

$retweet = Tweet::getRetweet($tweet->id);

if ($retweet->retweet_msg == null) {

    if ($retweet->retweet_id == null) {
      
      // if retweeted normal tweet
      $retweeted_tweet = Tweet::getTweet($retweet->tweet_id);
    $tweet_user = User::getData($retweeted_tweet->user_id) ;
    $tweet_real = Tweet::getTweet($retweet->tweet_id);
    $timeAgo = Tweet::getTimeAgo($tweet_real->post_on) ; 
    $likes_count = Tweet::countLikes($retweet->tweet_id) ;
    $user_like_it = Tweet::userLikeIt($user_id ,$retweet->tweet_id);
    $retweets_count = Tweet::countRetweets($retweet->tweet_id) ;
    $user_retweeted_it = Tweet::userRetweeetedIt($user_id ,$retweet->tweet_id); 
    $retweeted_user = User::getData($tweet->user_id);
    $retweet_sign = true;
    } else {

      // this condtion if user retweeted quoted tweet or quote of quote tweet


    $retweeted_tweet = Tweet::getRetweet($retweet->retweet_id);

        if($retweeted_tweet->tweet_id != null) {
        // here it's retweeted quoted
        $tweet_user = User::getData($retweeted_tweet->user_id) ;
        $timeAgo = Tweet::getTimeAgo($retweeted_tweet->post_on) ; 
        $likes_count = Tweet::countLikes($retweeted_tweet->post_id) ;
        $user_like_it = Tweet::userLikeIt($user_id ,$retweeted_tweet->post_id);
        $retweets_count = Tweet::countRetweets($retweeted_tweet->post_id) ;
        $user_retweeted_it = Tweet::userRetweeetedIt($user_id ,$retweeted_tweet->post_id);
      
        
        $tweet_inner = Tweet::getTweet($retweeted_tweet->tweet_id);
        $user_inner_tweet = User::getData($tweet_inner->user_id) ;
        $timeAgo_inner = Tweet::getTimeAgo($tweet_inner->post_on); 
        $retweeted_user = User::getData($tweet->user_id);
        $retweet_sign = true;

        $qoute = $retweeted_tweet->retweet_msg;
        $retweet_comment = true;
        } else {
            // here is retweeted quoted of quoted

        $retweet_sign = true;
        $tweet_user = User::getData($retweeted_tweet->user_id) ;

        $timeAgo = Tweet::getTimeAgo($retweeted_tweet->post_on) ; 
        $likes_count = Tweet::countLikes($retweeted_tweet->post_id) ;
        $user_like_it = Tweet::userLikeIt($user_id ,$retweeted_tweet->post_id);
        $retweets_count = Tweet::countRetweets($retweeted_tweet->post_id) ;
        $user_retweeted_it = Tweet::userRetweeetedIt($user_id ,$retweeted_tweet->post_id);

        $qoq = true; // stand for quote of quote
        $qoute = $retweeted_tweet->retweet_msg;
        $tweet_inner = Tweet::getRetweet($retweeted_tweet->retweet_id);
        $user_inner_tweet = User::getData($tweet_inner->user_id) ;
        $timeAgo_inner = Tweet::getTimeAgo($tweet_inner->post_on);
        $inner_qoute  = $tweet_inner->retweet_msg;
      
        

        $retweeted_user = User::getData($tweet->user_id);

        }
    }

} else {
// quote tweet condtion
if ($retweet->retweet_id == null) {
$tweet_user = User::getData($tweet->user_id) ;
$timeAgo = Tweet::getTimeAgo($tweet->post_on) ; 
$likes_count = Tweet::countLikes($tweet->id) ;
$user_like_it = Tweet::userLikeIt($user_id ,$tweet->id);
$retweets_count = Tweet::countRetweets($tweet->id) ;
$user_retweeted_it = Tweet::userRetweeetedIt($user_id ,$tweet->id);
$qoute = $retweet->retweet_msg;
$retweet_comment = true;


$tweet_inner = Tweet::getTweet($retweet->tweet_id);
$user_inner_tweet = User::getData($tweet_inner->user_id) ;
$timeAgo_inner = Tweet::getTimeAgo($tweet_inner->post_on); 
} else {

// this condtion for quote of quote which retweet_id not null and retweet msg not null
$tweet_user = User::getData($tweet->user_id) ;
$timeAgo = Tweet::getTimeAgo($tweet->post_on) ; 
$likes_count = Tweet::countLikes($tweet->id) ;
$user_like_it = Tweet::userLikeIt($user_id ,$tweet->id);
$retweets_count = Tweet::countRetweets($tweet->id) ;
$user_retweeted_it = Tweet::userRetweeetedIt($user_id ,$tweet->id);
$qoute = $retweet->retweet_msg;
$qoq = true; // stand for quote of quote

$tweet_inner = Tweet::getRetweet($retweet->retweet_id);
$user_inner_tweet = User::getData($tweet_inner->user_id) ;
$timeAgo_inner = Tweet::getTimeAgo($tweet_inner->post_on);
$inner_qoute = $tweet_inner->retweet_msg;
if($inner_qoute == null) {
            
$tweet_innerr = Tweet::getRetweet($tweet_inner->retweet_id);
$inner_qoute = $tweet_innerr->retweet_msg;

}

}

}

} 
$tweet_link = $tweet->id;

if($retweet_sign)
$comment_count = Tweet::countComments($retweeted_tweet->id);
else  $comment_count = Tweet::countComments($tweet->id); 

// Get comments for this tweet
if($retweet_sign) {
    $comments = Tweet::comments($retweeted_tweet->id);
} else {
    $comments = Tweet::comments($tweet->id);
}

?>
         
        <div class="box-tweet feed" style="position: relative;" id="tweet-<?php echo $tweet->id; ?>">
        <a href="status/<?php echo $tweet_link; ?>">
        <span style="position:absolute; width:100%; height:100%; top:0;left: 0; z-index: 1;"></span>
        </a>
        <?php if ($retweet_sign) { ?>
        <span class="retweed-name"> <i class="fa fa-retweet retweet-name-i" aria-hidden="true"></i> 
        <a style="position: relative; z-index:100; color:rgb(102, 117, 130);" href="<?php echo $retweeted_user->username; ?> "> <?php  if($retweeted_user->id == $user_id) echo "You";
        else echo $retweeted_user->name; ?> </a>  retweeted</span>
        <?php } ?>
        <div class="grid-tweet">
        <a style="position: relative; z-index:1000" href="<?php echo $tweet_user->username;  ?>">
        <img
        src="assets/images/users/<?php echo $tweet_user->img; ?>"
        alt=""
        class="img-user-tweet"
        />
        </a >

        <div>
        <p> 
        <a style="position: relative; z-index:1000; color:black" href="<?php echo $tweet_user->username;  ?>">
        <strong> <?php echo $tweet_user->name ?> </strong> 
        </a>
        <span class="username-twitter">@<?php echo $tweet_user->username ?> </span>
        <span class="username-twitter"><?php echo $timeAgo ?></span>
        
        <!-- Three dots menu -->
        <div class="tweet-menu" style="display: inline-block; position: relative;">
            <i class="fas fa-ellipsis-h mt-icon-reaction tweet-menu-toggle" 
               style="cursor: pointer; padding: 5px; border-radius: 50%;"
               data-tweet-id="<?php echo $tweet->id; ?>"
               data-user-id="<?php echo $user_id; ?>"
               data-tweet-user-id="<?php echo $tweet_user->id; ?>"></i>
            <div class="tweet-menu-options" style="display: none; position: absolute; right: 0; top: 100%; background: white; border: 1px solid #e1e8ed; border-radius: 4px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 1000; min-width: 150px;">
                <?php if ($tweet_user->id == $user_id): ?>
                    <div class="tweet-menu-option delete-tweet" 
                         data-tweet-id="<?php echo $tweet->id; ?>"
                         style="padding: 10px; cursor: pointer; color: #e0245e; border-bottom: 1px solid #e1e8ed;">
                        <i class="fas fa-trash"></i> Delete
                    </div>
                <?php endif; ?>
                <div class="tweet-menu-option report-tweet" 
                     data-tweet-id="<?php echo $tweet->id; ?>"
                     style="padding: 10px; cursor: pointer; color: #657786;">
                    <i class="far fa-flag"></i> Report
                </div>
            </div>
        </div>
        </p>
        <p class="tweet-links">
        <?php
        // check if it's quote or normal tweet
        if ($retweet_comment || $qoq)
        echo  Tweet::getTweetLinks($qoute);
        else echo  Tweet::getTweetLinks($tweet_real->status); ?>
        </p>
        <?php if ($retweet_comment == false && $qoq == false) { ?>
        <?php if ($tweet_real->img != null) { ?>
        <p class="mt-post-tweet">
        <img
        src="assets/images/tweets/<?php echo $tweet_real->img; ?>"
        alt=""
        class="img-post-tweet"
        />
        </p>
        <?php } } else { ?>
        <!-- qoued tweet place here --> 

        <div  class="mt-post-tweet comment-post" style="position: relative;">

        <a href="status/<?php echo $tweet_inner->id; ?>">
        <span class="" style="position:absolute; width:100%; height:100%; top:0;left: 0; z-index: 2;"></span>
        </a>
        <div class="grid-tweet py-3 "  > 

        <a style="position: relative; z-index:1000" href="<?php echo $user_inner_tweet->username;  ?>">
        <img
        src="assets/images/users/<?php echo $user_inner_tweet->img; ?>"
        alt=""
        class="img-user-tweet"
        />
        </a >

        <div>
        <p> 
        <a style="position: relative; z-index:1000; color:black" href="<?php echo $user_inner_tweet->username;  ?>">
        <strong> <?php echo $user_inner_tweet->name ?> </strong> 
        </a>
        <span class="username-twitter">@<?php echo $user_inner_tweet->username ?> </span>
        <span class="username-twitter"><?php echo $timeAgo_inner ?></span>
        </p>
        <p>
        <?php
        if ($qoq)
        echo Tweet::getTweetLinks($inner_qoute);
        else  echo  Tweet::getTweetLinks($tweet_inner->status); ?>
        </p>
        <?php   // don't show img if quote of quote
        if ($qoq == false) { 
        if ($tweet_inner->img != null) { ?>
        <p class="mt-post-tweet">
        <img
        src="assets/images/tweets/<?php echo $tweet_inner->img; ?>"
        alt=""
        class="img-post-retweet"
        />
        </p>
        <?php } } ?>

        </div> 

        </div>


        </div>

        <?php } ?>

        <!-- Comments Section -->
        <?php if (!empty($comments)): ?>
        <div class="comments-section" style="margin-top: 15px; padding: 15px; background: #f5f8fa; border-radius: 10px;">
            <h6 style="margin-bottom: 10px; font-size: 14px; color: #657786;">Replies (<?php echo count($comments); ?>)</h6>
            <?php foreach($comments as $comment): 
                $commentUser = User::getData($comment->user_id);
            ?>
                <div class="comment-item" style="display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #e6ecf0;">
                    <img src="assets/images/users/<?php echo htmlspecialchars($commentUser->img); ?>" alt="Profile" class="comment-avatar" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
                    <div class="comment-content" style="flex: 1;">
                        <div class="comment-header" style="display: flex; align-items: center; gap: 5px; margin-bottom: 5px;">
                            <strong style="font-size: 14px;"><?php echo htmlspecialchars($commentUser->name); ?></strong>
                            <span style="font-size: 12px; color: #657786;">@<?php echo htmlspecialchars($commentUser->username); ?></span>
                            <span style="font-size: 12px; color: #657786;">· <?php echo Tweet::getTimeAgo($comment->time); ?></span>
                        </div>
                        <p class="comment-text" style="margin: 0; font-size: 14px; line-height: 1.4;"><?php echo htmlspecialchars($comment->comment); ?></p>
                        <div class="comment-actions" style="display: flex; gap: 15px; margin-top: 5px;">
                            <span class="reply" data-tweet="<?php echo $comment->id; ?>" data-user="<?php echo $user_id; ?>" style="color: #657786; font-size: 12px; cursor: pointer;">
                                <i class="far fa-comment"></i> Reply
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="grid-reactions">
        <div class="grid-box-reaction">
        <div class="hover-reaction hover-reaction-comment comment"
        data-user = "<?php echo $user_id; ?>" 
        data-tweet = "<?php 
        if($retweet_sign)
        echo $retweeted_tweet->id;
        else  echo $tweet->id; ?>">

        <i class="far fa-comment"></i>
        <div class="mt-counter likes-count d-inline-block">
        <p> <?php if($comment_count > 0) echo $comment_count; ?>  </p>
        </div>
        </div>
        </div>
        <div class="grid-box-reaction">

        <div  class="hover-reaction hover-reaction-retweet
        <?= $user_retweeted_it ? 'retweeted' : 'retweet' ?> option"
        data-tweet="<?php
        echo $tweet->id ;
        ?>" 
        data-user="<?php echo $user_id; ?>
        "
        data-retweeted = "<?php echo $user_retweeted_it; ?>"
        data-sign = "<?php echo $retweet_sign; ?>"
        data-tmp="<?php echo $retweet_comment; ?>"
        data-qoq="<?php echo $qoq; ?>">



        <i class="fas fa-retweet"></i>
        <div class="mt-counter likes-count d-inline-block">
        <p><?php if($retweets_count > 0)  echo $retweets_count ; ?></p>
        </div>



        </div>

        <div class="options">

            
        </div> 

        </div>
        <div  class="grid-box-reaction"  >
        <a class="hover-reaction hover-reaction-like 
        <?= $user_like_it ? 'unlike-btn' : 'like-btn' ?> " 
        data-tweet="<?php 
        if($retweet_sign) {
            if($retweet->tweet_id != null) {
            echo $retweet->tweet_id;
            } echo $retweet->retweet_id;
        }  else echo $tweet->id ;
        ?>" 
        data-user="<?php echo $user_id; ?>">


        <i class="fa-heart <?= $user_like_it ? 'fas' : 'far mt-icon-reaction' ?>"></i>

        <div class="mt-counter likes-count d-inline-block">
        <p> <?php if($likes_count > 0)  echo $likes_count ; ?> </p>
        </div>
        </a>


        </div>

        <div class="grid-box-reaction">
        <div class="hover-reaction hover-reaction-comment">

       <!-- Three dots menu -->
<div class="tweet-menu" style="display: inline-block; position: relative;">
    <i class="fas fa-ellipsis-h mt-icon-reaction tweet-menu-toggle" 
       style="cursor: pointer; padding: 5px; border-radius: 50%;"
       data-tweet-id="<?php echo $tweet->id; ?>"
       data-user-id="<?php echo $user_id; ?>"
       data-tweet-user-id="<?php echo $tweet_user->id; ?>"></i>
    <div class="tweet-menu-options" style="display: none; position: absolute; right: 0; top: 100%; background: white; border: 1px solid #e1e8ed; border-radius: 4px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 1000; min-width: 150px;">
        <?php if ($tweet_user->id == $user_id): ?>
            <div class="tweet-menu-option delete-tweet" 
                 data-tweet-id="<?php echo $tweet->id; ?>"
                 style="padding: 10px; cursor: pointer; color: #e0245e; border-bottom: 1px solid #e1e8ed;">
                <i class="fas fa-trash"></i> Delete
            </div>
        <?php endif; ?>
        <div class="tweet-menu-option report-tweet" 
             data-tweet-id="<?php echo $tweet->id; ?>"
             style="padding: 10px; cursor: pointer; color: #657786;">
            <i class="far fa-flag"></i> Report
        </div>
    </div>
</div>
        </div>
        <div class="mt-counter">
        <p></p>
        </div>
        </div>
        </div>
        </div>
        </div>




        </div>


        <div class="popupTweet">

        </div>
        <div class="popupComment">

        </div>




<?php } ?>

<!-- Report Modal -->
<div id="reportModal" class="modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: white; margin: 15% auto; padding: 20px; border-radius: 10px; width: 400px; max-width: 90%;">
        <div class="modal-header" style="display: flex; justify-content: between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin: 0;">Report Tweet</h3>
            <span class="close-modal" style="cursor: pointer; font-size: 24px;">&times;</span>
        </div>
        <form id="reportForm">
            <input type="hidden" id="reportTweetId" name="tweet_id">
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Reason for reporting:</label>
                <select id="reportReason" name="reason" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Select a reason</option>
                    <option value="spam">Spam</option>
                    <option value="harassment">Harassment or bullying</option>
                    <option value="hate_speech">Hate speech</option>
                    <option value="violence">Violence or threats</option>
                    <option value="misinformation">Misinformation</option>
                    <option value="copyright">Copyright infringement</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Additional details (optional):</label>
                <textarea id="reportDescription" name="description" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" placeholder="Please provide more details about your report..."></textarea>
            </div>
            <div class="form-actions" style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="cancel-report" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 20px; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 8px 16px; background: #e0245e; color: white; border: none; border-radius: 20px; cursor: pointer;">Submit Report</button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript for tweet menu functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing tweet functions');
    
    // Toggle tweet menu
    document.querySelectorAll('.tweet-menu-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            // Close all other open menus
            document.querySelectorAll('.tweet-menu-options').forEach(function(menu) {
                if (menu !== this.nextElementSibling) {
                    menu.style.display = 'none';
                }
            }.bind(this));
            
            // Toggle current menu
            const menu = this.nextElementSibling;
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.tweet-menu-options').forEach(function(menu) {
            menu.style.display = 'none';
        });
    });
    
    // Delete tweet functionality
    document.querySelectorAll('.delete-tweet').forEach(function(deleteBtn) {
        deleteBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const tweetId = this.getAttribute('data-tweet-id');
            console.log('Delete clicked for tweet:', tweetId);
            
            if (confirm('Are you sure you want to delete this tweet? This action cannot be undone.')) {
                deleteTweet(tweetId);
            }
        });
    });
    
    // Report tweet functionality
    document.querySelectorAll('.report-tweet').forEach(function(reportBtn) {
        reportBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const tweetId = this.getAttribute('data-tweet-id');
            console.log('Report clicked for tweet:', tweetId);
            openReportModal(tweetId);
        });
    });
    
    // Modal functionality
    const reportModal = document.getElementById('reportModal');
    const closeModal = document.querySelector('.close-modal');
    const cancelReport = document.querySelector('.cancel-report');
    
    if (closeModal) {
        closeModal.addEventListener('click', closeReportModal);
    }
    
    if (cancelReport) {
        cancelReport.addEventListener('click', closeReportModal);
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === reportModal) {
            closeReportModal();
        }
    });
    
    // Report form submission
    const reportForm = document.getElementById('reportForm');
    if (reportForm) {
        reportForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitReport();
        });
    }
    
    console.log('Tweet functions initialized successfully');
});

function deleteTweet(tweetId) {
    console.log('Starting delete process for tweet:', tweetId);
    
    // Show loading state on the delete button
    const deleteButtons = document.querySelectorAll(`.delete-tweet[data-tweet-id="${tweetId}"]`);
    deleteButtons.forEach(btn => {
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        btn.style.pointerEvents = 'none';
        
        // Store original content to restore later
        btn.setAttribute('data-original-html', originalHTML);
    });
    
    const formData = new FormData();
    formData.append('action', 'delete_tweet');
    formData.append('tweet_id', tweetId);
    
    fetch('delete_tweet.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Delete response data:', data);
        if (data.success) {
            // Remove the tweet from the DOM with animation
            const tweetElement = document.getElementById('tweet-' + tweetId);
            if (tweetElement) {
                tweetElement.style.opacity = '0';
                tweetElement.style.transform = 'translateX(-100px)';
                tweetElement.style.transition = 'all 0.3s ease';
                
                setTimeout(function() {
                    tweetElement.remove();
                    showNotification('Tweet deleted successfully', 'success');
                }, 300);
            } else {
                showNotification('Tweet deleted successfully', 'success');
                // Reload page if element not found to reflect changes
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } else {
            showNotification('Delete failed: ' + data.message, 'error');
            // Restore button state
            restoreDeleteButtons(tweetId);
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showNotification('Delete failed: ' + error.message, 'error');
        // Restore button state
        restoreDeleteButtons(tweetId);
    });
}

function restoreDeleteButtons(tweetId) {
    const deleteButtons = document.querySelectorAll(`.delete-tweet[data-tweet-id="${tweetId}"]`);
    deleteButtons.forEach(btn => {
        const originalHTML = btn.getAttribute('data-original-html');
        if (originalHTML) {
            btn.innerHTML = originalHTML;
        } else {
            btn.innerHTML = '<i class="fas fa-trash"></i> Delete';
        }
        btn.style.pointerEvents = 'auto';
    });
}

function openReportModal(tweetId) {
    console.log('Opening report modal for tweet:', tweetId);
    
    const modal = document.getElementById('reportModal');
    const tweetIdInput = document.getElementById('reportTweetId');
    
    if (modal && tweetIdInput) {
        tweetIdInput.value = tweetId;
        modal.style.display = 'block';
        
        // Close any open tweet menus
        document.querySelectorAll('.tweet-menu-options').forEach(function(menu) {
            menu.style.display = 'none';
        });
    }
}

function closeReportModal() {
    console.log('Closing report modal');
    
    const modal = document.getElementById('reportModal');
    if (modal) {
        modal.style.display = 'none';
        // Reset form
        const form = document.getElementById('reportForm');
        if (form) form.reset();
    }
}

function submitReport() {
    const tweetId = document.getElementById('reportTweetId').value;
    const reason = document.getElementById('reportReason').value;
    const description = document.getElementById('reportDescription').value;
    
    console.log('Submitting report for tweet:', tweetId, 'Reason:', reason);
    
    if (!reason) {
        showNotification('Please select a reason for reporting', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = reportForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    submitBtn.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'report_tweet');
    formData.append('tweet_id', tweetId);
    formData.append('reason', reason);
    formData.append('description', description);
    
    fetch('delete_tweet.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Report response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Report response data:', data);
        if (data.success) {
            showNotification('Tweet reported successfully. Thank you for helping keep our community safe.', 'success');
            closeReportModal();
        } else {
            showNotification('Report failed: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Report error:', error);
        showNotification('Report failed: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function showNotification(message, type) {
    console.log('Showing notification:', type, message);
    
    // Remove any existing notifications
    document.querySelectorAll('.notification').forEach(notification => {
        notification.remove();
    });
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
        z-index: 10001;
        opacity: 0;
        transform: translateX(100px);
        transition: all 0.3s ease;
        max-width: 400px;
        word-wrap: break-word;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        font-family: Arial, sans-serif;
    `;
    
    if (type === 'success') {
        notification.style.backgroundColor = '#17bf63';
        notification.style.border = '1px solid #15a858';
    } else {
        notification.style.backgroundColor = '#e0245e';
        notification.style.border = '1px solid #c51d5a';
    }
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 5 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100px)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Add CSS for loading spinner if not already present
if (!document.querySelector('#tweet-styles')) {
    const style = document.createElement('style');
    style.id = 'tweet-styles';
    style.textContent = `
        .fa-spinner {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .tweet-menu-options {
            z-index: 1000;
        }
        .modal {
            z-index: 10000;
        }
        .notification {
            z-index: 10001;
        }
    `;
    document.head.appendChild(style);
}
    // DELETE TWEET FUNCTION - COMPLETE VERSION
    public static function deleteTweet($tweet_id, $user_id) {
        try {
            $pdo = self::connect();
            
            // Start transaction
            $pdo->beginTransaction();
            
            // Check if the user owns the tweet
            $ownership_stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
            $ownership_stmt->execute([$tweet_id]);
            
            if ($ownership_stmt->rowCount() == 0) {
                $pdo->rollBack();
                return ['success' => false, 'message' => 'Tweet not found'];
            }
            
            $tweet_data = $ownership_stmt->fetch(PDO::FETCH_OBJ);
            
            if ($tweet_data->user_id != $user_id) {
                $pdo->rollBack();
                return ['success' => false, 'message' => 'You can only delete your own tweets'];
            }
            
            // Delete from related tables first
            $delete_likes = $pdo->prepare("DELETE FROM likes WHERE post_id = ?");
            $delete_likes->execute([$tweet_id]);
            
            $delete_retweets = $pdo->prepare("DELETE FROM retweets WHERE post_id = ? OR tweet_id = ? OR retweet_id = ?");
            $delete_retweets->execute([$tweet_id, $tweet_id, $tweet_id]);
            
            $delete_comments = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
            $delete_comments->execute([$tweet_id]);
            
            $delete_tweet_data = $pdo->prepare("DELETE FROM tweets WHERE post_id = ?");
            $delete_tweet_data->execute([$tweet_id]);
            
            // Finally delete the main post
            $delete_post = $pdo->prepare("DELETE FROM posts WHERE id = ?");
            $delete_post->execute([$tweet_id]);
            
            // Commit transaction
            $pdo->commit();
            
            return ['success' => true, 'message' => 'Tweet deleted successfully'];
            
        } catch (PDOException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            error_log("Tweet deletion error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()];
        }
    }

    // REPORT TWEET FUNCTION
    public static function reportTweet($user_id, $tweet_id, $reason, $description = '') {
        try {
            // Check if tweet exists
            $tweet_exists = self::connect()->prepare("SELECT id FROM posts WHERE id = ?");
            $tweet_exists->execute([$tweet_id]);
            
            if ($tweet_exists->rowCount() == 0) {
                return ['success' => false, 'message' => 'Tweet does not exist'];
            }
            
            // Check if user already reported this tweet
            $check_stmt = self::connect()->prepare("SELECT id FROM reports WHERE user_id = ? AND tweet_id = ?");
            $check_stmt->execute([$user_id, $tweet_id]);
            
            if ($check_stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'You have already reported this tweet'];
            }
            
            // Insert report
            $stmt = self::connect()->prepare("INSERT INTO reports (user_id, tweet_id, reason, description, created_at) VALUES (?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$user_id, $tweet_id, $reason, $description]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Tweet reported successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to report tweet'];
            }
            
        } catch (PDOException $e) {
            error_log("Report tweet error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    // CHECK IF USER CAN DELETE TWEET
    public static function canDeleteTweet($user_id, $tweet_id) {
        try {
            $stmt = self::connect()->prepare("SELECT user_id FROM posts WHERE id = ?");
            $stmt->execute([$tweet_id]);
            
            if ($stmt->rowCount() > 0) {
                $post = $stmt->fetch(PDO::FETCH_OBJ);
                return $post->user_id == $user_id;
            }
            return false;
            
        } catch (PDOException $e) {
            error_log("Can delete check error: " . $e->getMessage());
            return false;
        }
    }
</script>