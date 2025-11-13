<?php
include 'core/init.php';

$user_id = $_SESSION['user_id'];
$user = User::getData($user_id);

if (User::checkLogIn() === false) {
    header('location: index.php');
    exit();
}

$tweet_id = isset($_GET['post_id']) ? $_GET['post_id'] : null;
if (!$tweet_id) {
    header('location: home.php');
    exit();
}

$tweet = Tweet::getData($tweet_id);
if (!$tweet) {
    header('location: home.php');
    exit();
}

$who_users = Follow::whoToFollow($user_id);
$notify_count = User::CountNotification($user_id);

// Get tweet data
$tweet_data = null;
$tweet_user = null;
$is_retweet = false;
$retweeted_by = null;

if (Tweet::isTweet($tweet_id)) {
    $tweet_data = Tweet::getTweet($tweet_id);
    $tweet_user = User::getData($tweet_data->user_id);
} else if (Tweet::isRetweet($tweet_id)) {
    $tweet_data = Tweet::getRetweet($tweet_id);
    $tweet_user = User::getData($tweet_data->user_id);
    $is_retweet = true;
    $retweeted_by = User::getData($tweet_data->user_id);
}

if ($tweet_data && $tweet_user) {
    $timeAgo = Tweet::getTimeAgo($tweet_data->post_on);
    $likes_count = Tweet::countLikes($tweet_id);
    $user_like_it = Tweet::userLikeIt($user_id, $tweet_id);
    $retweets_count = Tweet::countRetweets($tweet_id);
    $user_retweeted_it = Tweet::userRetweeetedIt($user_id, $tweet_id);
    $comment_count = Tweet::countComments($tweet_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status | Kabi</title>
    <base href="<?php echo BASE_URL; ?>">
    <link rel="shortcut icon" type="image/png" href="assets/images/kabi.png"> 
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/home_style.css?v=<?php echo time(); ?>">
    <style>
        /* Tweet Options Dropdown */
        .tweet-options-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            min-width: 180px;
            z-index: 1000;
            padding: 8px 0;
        }

        .tweet-options-dropdown.show {
            display: block;
        }

        .tweet-options-dropdown .dropdown-item {
            padding: 8px 16px;
            color: #14171a;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 14px;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .tweet-options-dropdown .dropdown-item:hover {
            background-color: #f5f8fa;
            color: #14171a;
            text-decoration: none;
        }

        .tweet-options-dropdown .dropdown-item i {
            width: 20px;
            text-align: center;
        }

        .tweet-options-dropdown .dropdown-item.delete-tweet,
        .tweet-options-dropdown .dropdown-item.report-tweet,
        .tweet-options-dropdown .dropdown-item.block-user {
            color: #e0245e;
        }

        .tweet-options-dropdown .dropdown-item.delete-tweet:hover,
        .tweet-options-dropdown .dropdown-item.report-tweet:hover,
        .tweet-options-dropdown .dropdown-item.block-user:hover {
            background-color: #ffeef3;
        }

        .position-relative {
            position: relative !important;
        }

        .engagement-stats {
            border-top: 1px solid #e1e8ed;
            margin-top: 10px;
        }

        .engagement-item {
            margin-right: 15px;
            color: #657786;
            font-size: 14px;
        }

        .comments-header {
            border-bottom: 1px solid #e1e8ed;
        }

        /* Edit Modal Styles */
        .modal-content {
            border-radius: 16px;
            border: none;
        }

        .modal-header {
            border-bottom: 1px solid #e1e8ed;
            padding: 16px;
        }

        .modal-footer {
            border-top: 1px solid #e1e8ed;
            padding: 16px;
        }

        .modal-body {
            padding: 16px;
        }

        #editTweetText {
            font-size: 19px;
            line-height: 1.4;
            border: none !important;
            box-shadow: none !important;
            resize: none;
        }

        #editTweetText:focus {
            outline: none;
            box-shadow: none;
        }

        .btn-primary {
            background-color: #1da1f2;
            border-color: #1da1f2;
            border-radius: 50px;
            font-weight: 600;
            padding: 8px 20px;
        }

        .btn-primary:hover {
            background-color: #1a91da;
            border-color: #1a91da;
        }

        .btn-secondary {
            border-radius: 50px;
            font-weight: 600;
            padding: 8px 20px;
        }

        /* Success alert */
        .alert-success {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .character-count {
            color: #657786;
            font-size: 14px;
        }

        .character-count.warning {
            color: #ffad1f;
        }

        .character-count.error {
            color: #e0245e;
        }
    </style>
</head>
<body>
<script src="assets/js/jquery-3.5.1.min.js"></script>
  
<div id="mine">
    <!-- Left Sidebar -->
    <div class="wrapper-left">
        <div class="sidebar-left">
            <div class="grid-sidebar" style="margin-top: 12px">
                <div class="icon-sidebar-align">
                    <img src="<?php echo BASE_URL . "/assets/images/kabi.png"; ?>" alt="" height="30px" width="30px" />
                </div>
            </div>

            <a href="home.php">
                <div class="grid-sidebar bg-active" style="margin-top: 12px">
                    <div class="icon-sidebar-align">
                        <img src="<?php echo BASE_URL . "/includes/icons/tweethome.png"; ?>" alt="" height="26.25px" width="26.25px" />
                    </div>
                    <div class="wrapper-left-elements">
                        <span class="wrapper-left-active" style="margin-top: 4px;"><strong>Home</strong></span>
                    </div>
                </div>
            </a>
  
            <a href="notification.php">
                <div class="grid-sidebar">
                    <div class="icon-sidebar-align position-relative">
                        <?php if ($notify_count > 0) { ?>
                            <i class="notify-count"><?php echo $notify_count; ?></i> 
                        <?php } ?>
                        <img src="<?php echo BASE_URL . "/includes/icons/tweetnotif.png"; ?>" alt="" height="26.25px" width="26.25px" />
                    </div>
                    <div class="wrapper-left-elements">
                        <span style="margin-top: 4px"><strong>Notifications</strong></span>
                    </div>
                </div>
            </a>
        
            <a href="<?php echo BASE_URL . $user->username; ?>">
                <div class="grid-sidebar">
                    <div class="icon-sidebar-align">
                        <img src="<?php echo BASE_URL . "/includes/icons/tweetprof.png"; ?>" alt="" height="26.25px" width="26.25px" />
                    </div>
                    <div class="wrapper-left-elements">
                        <span style="margin-top: 4px"><strong>Profile</strong></span>
                    </div>
                </div>
            </a>
            
            <a href="<?php echo BASE_URL . "account.php"; ?>">
                <div class="grid-sidebar">
                    <div class="icon-sidebar-align">
                        <img src="<?php echo BASE_URL . "/includes/icons/tweetsetting.png"; ?>" alt="" height="26.25px" width="26.25px" />
                    </div>
                    <div class="wrapper-left-elements">
                        <span style="margin-top: 4px"><strong>Settings</strong></span>
                    </div>
                </div>
            </a>
            
            <a href="includes/logout.php">
                <div class="grid-sidebar">
                    <div class="icon-sidebar-align">
                        <i style="font-size: 26px;" class="fas fa-sign-out-alt"></i>
                    </div>
                    <div class="wrapper-left-elements">
                        <span style="margin-top: 4px"><strong>Logout</strong></span>
                    </div>
                </div>
            </a>
         
            <div class="box-user">
                <div class="grid-user">
                    <div>
                        <img src="assets/images/users/<?php echo $user->img ?>" alt="user" class="img-user" />
                    </div>
                    <div>
                        <p class="name"><strong><?php if($user->name !== null) { echo $user->name; } ?></strong></p>
                        <p class="username">@<?php echo $user->username; ?></p>
                    </div>
                    <div class="mt-arrow">
                        <img src="https://i.ibb.co/mRLLwdW/arrow-down.png" alt="" height="18.75px" width="18.75px" />
                    </div>
                </div>
            </div>
        </div>
    </div>
          
    <!-- Main Content -->
    <div class="grid-posts">
        <div class="border-right">
            <!-- Header -->
            <div class="grid-toolbar-center">
                <div class="center-input-search">
                    <div class="container" style="border-bottom: 1px solid #E9ECEF;">
                        <div class="row">
                            <div class="col-xs-1">
                                <a href="javascript: history.go(-1);"> 
                                    <i style="font-size:20px;" class="fas fa-arrow-left arrow-style"></i> 
                                </a>
                            </div>
                            <div class="col-xs-10 mt-1">
                                <p class="tweet-name" style="font-weight:700">Post</p>
                            </div>
                        </div>
                        <div class="part-2">
                            <!-- Tweet engagement summary -->
                            <div class="engagement-stats py-2">
                                <?php if(isset($retweets_count) && $retweets_count > 0): ?>
                                    <span class="engagement-item"><?php echo $retweets_count; ?> Retweets</span>
                                <?php endif; ?>
                                <?php if(isset($likes_count) && $likes_count > 0): ?>
                                    <span class="engagement-item"><?php echo $likes_count; ?> Likes</span>
                                <?php endif; ?>
                                <?php if(isset($comment_count) && $comment_count > 0): ?>
                                    <span class="engagement-item"><?php echo $comment_count; ?> Replies</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
          
            <div class="box-fixed" id="box-fixed"></div>
          
            <?php if ($tweet_data && $tweet_user): ?>
            
            <!-- Tweet Display -->
            <div class="box-tweet feed" style="position: relative;">
                <?php if ($is_retweet && $retweeted_by): ?>
                    <span class="retweed-name"> 
                        <i class="fa fa-retweet retweet-name-i" aria-hidden="true"></i> 
                        <a style="position: relative; z-index:100; color:rgb(102, 117, 130);" href="<?php echo BASE_URL . $retweeted_by->username; ?>">
                            <?php echo ($retweeted_by->id == $user_id) ? "You" : $retweeted_by->name; ?>
                        </a> retweeted
                    </span>
                <?php endif; ?>
                
                <div class="grid-tweet">
                    <a style="position: relative; z-index:1000" href="<?php echo BASE_URL . $tweet_user->username; ?>">
                        <img src="assets/images/users/<?php echo $tweet_user->img; ?>" alt="" class="img-user-tweet" />
                    </a>

                    <div>
                        <p> 
                            <a style="position: relative; z-index:1000; color:black" href="<?php echo BASE_URL . $tweet_user->username; ?>">
                                <strong><?php echo $tweet_user->name; ?></strong> 
                            </a>
                            <span class="username-twitter">@<?php echo $tweet_user->username; ?></span>
                            <span class="username-twitter"><?php echo $timeAgo; ?></span>
                        </p>
                        <p class="tweet-content">
                            <?php echo Tweet::getTweetLinks($tweet_data->status ?? $tweet_data->retweet_msg); ?>
                        </p>
                        
                        <?php if (isset($tweet_data->img) && $tweet_data->img != null): ?>
                            <p class="mt-post-tweet">
                                <img src="assets/images/tweets/<?php echo $tweet_data->img; ?>" alt="" class="img-post-tweet" />
                            </p>
                        <?php endif; ?>

                        <!-- Engagement Stats -->
                        <div class="row home-follow pt-3">
                            <?php if($retweets_count > 0): ?>
                                <div class="col-md-2 users-count">
                                    <i class="retweets-u" data-tweet="<?php echo $tweet_id; ?>">
                                        <span class="home-follow-count"><?php echo $retweets_count; ?></span> Retweets
                                    </i>
                                </div> 
                            <?php endif; ?> 
                            <?php if($likes_count > 0): ?>
                                <div class="col-md-2 users-count">
                                    <div class="likes-u" data-tweet="<?php echo $tweet_id; ?>">
                                        <span class="home-follow-count"><?php echo $likes_count; ?></span> Likes
                                    </div>
                                </div>   
                            <?php endif; ?> 
                        </div>

                        <!-- Interaction Buttons -->
                        <div class="grid-reactions">
                            <!-- Comment Button -->
                            <div class="grid-box-reaction">
                                <div class="hover-reaction hover-reaction-comment comment"
                                    data-user="<?php echo $user_id; ?>" 
                                    data-tweet="<?php echo $tweet_id; ?>">
                                    <i class="far fa-comment"></i>
                                    <div class="mt-counter likes-count d-inline-block">
                                        <p><?php if($comment_count > 0) echo $comment_count; ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Retweet Button -->
                            <div class="grid-box-reaction">
                                <div class="hover-reaction hover-reaction-retweet <?= $user_retweeted_it ? 'retweeted' : 'retweet' ?> option"
                                    data-tweet="<?php echo $tweet_id; ?>" 
                                    data-user="<?php echo $user_id; ?>"
                                    data-retweeted="<?php echo $user_retweeted_it; ?>">
                                    <i class="fas fa-retweet"></i>
                                    <div class="mt-counter likes-count d-inline-block">
                                        <p><?php if($retweets_count > 0) echo $retweets_count; ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Like Button -->
                            <div class="grid-box-reaction">
                                <a class="hover-reaction hover-reaction-like <?= $user_like_it ? 'unlike-btn' : 'like-btn' ?>" 
                                    data-tweet="<?php echo $tweet_id; ?>" 
                                    data-user="<?php echo $user_id; ?>">
                                    <i class="fa-heart <?= $user_like_it ? 'fas' : 'far mt-icon-reaction' ?>"></i>
                                    <div class="mt-counter likes-count d-inline-block">
                                        <p><?php if($likes_count > 0) echo $likes_count; ?></p>
                                    </div>
                                </a>
                            </div>
                            
                            <!-- More Options -->
                            <div class="grid-box-reaction position-relative">
                                <div class="hover-reaction hover-reaction-comment tweet-options-trigger" 
                                     data-tweet="<?php echo $tweet_id; ?>"
                                     data-user="<?php echo $user_id; ?>">
                                    <i class="fas fa-ellipsis-h mt-icon-reaction"></i>
                                </div>
                                
                                <!-- Dropdown Menu -->
                                <div class="tweet-options-dropdown dropdown-menu">
                                    <?php if ($tweet_user->id == $user_id): ?>
                                        <!-- Options for tweet owner -->
                                        <a href="#" class="dropdown-item edit-tweet" data-tweet="<?php echo $tweet_id; ?>">
                                            <i class="fas fa-edit mr-2"></i>Edit Post
                                        </a>
                                    
                                    <?php else: ?>
                                        <!-- Options for other users -->
                                        <a href="#" class="dropdown-item report-tweet" data-tweet="<?php echo $tweet_id; ?>">
                                            <i class="fas fa-flag mr-2"></i>Report Post
                                        </a>
                                       
                                    <?php endif; ?>
                                    <a href="#" class="dropdown-item copy-link" data-link="<?php echo BASE_URL . 'status.php?post_id=' . $tweet_id; ?>">
                                        <i class="fas fa-link mr-2"></i>Copy Post Link
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
            <?php else: ?>
                <div class="box-tweet feed">
                    <div class="alert alert-warning">Post not found or has been deleted.</div>
                </div>
            <?php endif; ?>
             
            <!-- Comments Section -->
            <div class="comments">
                <div class="comments-header py-3">
                    <h5>Replies</h5>
                </div>
                
                <?php 
                $comments = Tweet::comments($tweet_id);
                if (!empty($comments)): 
                    foreach($comments as $comment): 
                        $comment_user = User::getData($comment->user_id);
                        $comment_time = Tweet::getTimeAgo($comment->time);
                        $reply_count = Tweet::countReplies($comment->id);
                ?>
                    <div class="box-comment feed py-2">
                        <div class="grid-tweet">
                            <div>
                                <img src="assets/images/users/<?php echo $comment_user->img; ?>" alt="" class="img-user-tweet" />
                            </div>
                            <div>
                                <p>
                                    <strong><?php echo $comment_user->name; ?></strong>
                                    <span class="username-twitter">@<?php echo $comment_user->username; ?></span>
                                    <span class="username-twitter"><?php echo $comment_time; ?></span>
                                </p>
                                <p><?php echo Tweet::getTweetLinks($comment->comment); ?></p>
                                    
                                <div class="grid-reactions">
                                    <div class="grid-box-reaction-rep">
                                        <div class="hover-reaction-rep hover-reaction-comment reply"
                                            data-user="<?php echo $user_id; ?>" 
                                            data-tweet="<?php echo $comment->id; ?>">
                                            <i class="far fa-comment"></i>
                                            <div class="mt-counter likes-count d-inline-block">
                                                <p><?php if($reply_count > 0) echo $reply_count; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Comment Options -->
                                    <div class="grid-box-reaction position-relative">
                                        <div class="hover-reaction hover-reaction-comment comment-options-trigger" 
                                             data-comment="<?php echo $comment->id; ?>">
                                            <i class="fas fa-ellipsis-h mt-icon-reaction"></i>
                                        </div>
                                        
                                        <div class="tweet-options-dropdown dropdown-menu">
                                            <?php if ($comment_user->id == $user_id): ?>
                                                <a href="#" class="dropdown-item delete-comment" data-comment="<?php echo $comment->id; ?>">
                                                    <i class="fas fa-trash mr-2"></i>Delete Comment
                                                </a>
                                            <?php else: ?>
                                                <a href="#" class="dropdown-item report-comment" data-comment="<?php echo $comment->id; ?>">
                                                    <i class="fas fa-flag mr-2"></i>Report Comment
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                        </div> 
                        
                        <!-- Replies to this comment -->
                        <?php 
                        $replies = Tweet::replies($comment->id);
                        if (!empty($replies)):
                            foreach($replies as $reply):
                                $reply_user = User::getData($reply->user_id);
                                $reply_time = Tweet::getTimeAgo($reply->time);
                        ?>
                            <div class="box-reply feed ml-5">
                                <div class="grid-tweet">
                                    <div>
                                        <img src="assets/images/users/<?php echo $reply_user->img; ?>" alt="" class="img-user-tweet" />
                                    </div>
                                    <div>
                                        <p>
                                            <strong><?php echo $reply_user->name; ?></strong>
                                            <span class="username-twitter">@<?php echo $reply_user->username; ?></span>
                                            <span class="username-twitter"><?php echo $reply_time; ?></span>
                                        </p>
                                        <p><?php echo Tweet::getTweetLinks($reply->reply); ?></p>
                                    </div> 
                                </div> 
                            </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div> 
                <?php endforeach; ?>
                <?php else: ?>
                   
                <?php endif; ?>
            </div>

            <!-- Popup Modals -->
            <div class="popupTweet"></div>
            <div class="popupComment"></div>
            <div class="popupUsers"></div>
        </div> 

        <!-- Right Sidebar -->
        <div class="wrapper-right">
            <div style="width: 90%;" class="container">
                <div class="input-group py-2 m-auto pr-5 position-relative">
                    <i id="icon-search" class="fas fa-search tryy"></i>
                    <input type="text" class="form-control search-input" placeholder="Search ">
                    <div class="search-result"></div>
                </div>
            </div>

            <!-- Who to Follow -->
            <div class="box-share">
                <p class="txt-share"><strong>Who to follow</strong></p>
                <?php foreach($who_users as $follow_user): 
                    $user_follow = Follow::isUserFollow($user_id, $follow_user->id);
                ?>
                    <div class="grid-share">
                        <a style="position: relative; z-index:5; color:black" href="<?php echo BASE_URL . $follow_user->username; ?>">
                            <img src="assets/images/users/<?php echo $follow_user->img; ?>" alt="" class="img-share" />
                        </a>
                        <div>
                            <p>
                                <a style="position: relative; z-index:5; color:black" href="<?php echo BASE_URL . $follow_user->username; ?>">  
                                    <strong><?php echo $follow_user->name; ?></strong>
                                </a>
                            </p>
                            <p class="username">
                                @<?php echo $follow_user->username; ?>
                                <?php if (Follow::FollowsYou($follow_user->id, $user_id)): ?>
                                    <span class="ml-1 follows-you">Follows You</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <button class="follow-btn follow-btn-m <?= $user_follow ? 'following' : 'follow' ?>"
                                data-follow="<?php echo $follow_user->id; ?>"
                                data-user="<?php echo $user_id; ?>">
                                <?php echo $user_follow ? 'Following' : 'Follow'; ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript Files -->
<script src="assets/js/search.js"></script>
<script type="text/javascript" src="assets/js/hashtag.js"></script>
<script type="text/javascript" src="assets/js/like.js"></script>
<script type="text/javascript" src="assets/js/users.js"></script>
<script type="text/javascript" src="assets/js/comment.js?v=<?php echo time(); ?>"></script>
<script type="text/javascript" src="assets/js/retweet.js?v=<?php echo time(); ?>"></script>
<script type="text/javascript" src="assets/js/follow.js?v=<?php echo time(); ?>"></script>

<!-- Tweet Options JavaScript -->
<script type="text/javascript">
$(document).ready(function() {
    // Toggle dropdown menu for tweets
    $(document).on('click', '.tweet-options-trigger', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Close all other dropdowns
        $('.tweet-options-dropdown').removeClass('show');
        
        // Toggle current dropdown
        $(this).siblings('.tweet-options-dropdown').toggleClass('show');
    });
    
    // Toggle dropdown menu for comments
    $(document).on('click', '.comment-options-trigger', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Close all other dropdowns
        $('.tweet-options-dropdown').removeClass('show');
        
        // Toggle current dropdown
        $(this).siblings('.tweet-options-dropdown').toggleClass('show');
    });
    
    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.tweet-options-dropdown, .tweet-options-trigger, .comment-options-trigger').length) {
            $('.tweet-options-dropdown').removeClass('show');
        }
    });
    
    // Copy Tweet Link
    $(document).on('click', '.copy-link', function(e) {
        e.preventDefault();
        const link = $(this).data('link');
        
        // Create temporary input element
        const tempInput = document.createElement('input');
        tempInput.value = link;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        
        // Show success message
        alert('post link copied to clipboard!');
        $('.tweet-options-dropdown').removeClass('show');
    });
    
    // Edit Tweet - FIXED FUNCTIONALITY
    $(document).on('click', '.edit-tweet', function(e) {
        e.preventDefault();
        const tweetId = $(this).data('tweet');
        
        console.log('Edit tweet clicked:', tweetId);
        
        // Fetch the current tweet content
        $.ajax({
            url: 'includes/get_tweet.php',
            type: 'POST',
            data: { tweet_id: tweetId },
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response);
                if (response.success) {
                    // Show edit modal with current content
                    showEditModal(tweetId, response.tweet_text);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                console.log('Response text:', xhr.responseText);
                alert('Error fetching tweet. Please check console for details.');
            }
        });
        
        $('.tweet-options-dropdown').removeClass('show');
    });
    
    // Delete Tweet
    $(document).on('click', '.delete-tweet', function(e) {
        e.preventDefault();
        const tweetId = $(this).data('tweet');
        
        if (confirm('Are you sure you want to delete this tweet? This action cannot be undone.')) {
            $.ajax({
                url: 'includes/delete_tweet.php',
                type: 'POST',
                data: { tweet_id: tweetId },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            alert('Tweet deleted successfully!');
                            window.location.href = 'home.php';
                        } else {
                            alert('Error deleting tweet: ' + result.message);
                        }
                    } catch (e) {
                        alert('Tweet deleted successfully!');
                        window.location.href = 'home.php';
                    }
                },
                error: function() {
                    alert('Error deleting tweet. Please try again.');
                }
            });
        }
        
        $('.tweet-options-dropdown').removeClass('show');
    });
    
    // Report Tweet
    $(document).on('click', '.report-tweet', function(e) {
        e.preventDefault();
        const tweetId = $(this).data('tweet');
        
        const reason = prompt('Please enter the reason for reporting this tweet:');
        if (reason !== null && reason.trim() !== '') {
            // Simulate reporting (you would implement actual reporting logic)
            alert('Thank you for reporting this tweet. We will review it shortly.');
        }
        
        $('.tweet-options-dropdown').removeClass('show');
    });
    
    // Block User
    $(document).on('click', '.block-user', function(e) {
        e.preventDefault();
        const userId = $(this).data('user');
        
        if (confirm('Are you sure you want to block this user? You won\'t see their tweets anymore.')) {
            // Simulate blocking (you would implement actual blocking logic)
            alert('User blocked successfully!');
            window.location.reload();
        }
        
        $('.tweet-options-dropdown').removeClass('show');
    });
    
    // Mute User
    $(document).on('click', '.mute-user', function(e) {
        e.preventDefault();
        const userId = $(this).data('user');
        
        if (confirm('Are you sure you want to mute this user? You won\'t see their tweets in your timeline.')) {
            // Simulate muting (you would implement actual muting logic)
            alert('User muted successfully!');
        }
        
        $('.tweet-options-dropdown').removeClass('show');
    });
    
    // Delete Comment
    $(document).on('click', '.delete-comment', function(e) {
        e.preventDefault();
        const commentId = $(this).data('comment');
        
        if (confirm('Are you sure you want to delete this comment?')) {
            // Simulate comment deletion (you would implement actual deletion logic)
            alert('Comment deleted successfully!');
            window.location.reload();
        }
        
        $('.tweet-options-dropdown').removeClass('show');
    });
    
    // Report Comment
    $(document).on('click', '.report-comment', function(e) {
        e.preventDefault();
        const commentId = $(this).data('comment');
        
        const reason = prompt('Please enter the reason for reporting this comment:');
        if (reason !== null && reason.trim() !== '') {
            // Simulate reporting (you would implement actual reporting logic)
            alert('Thank you for reporting this comment. We will review it shortly.');
        }
        
        $('.tweet-options-dropdown').removeClass('show');
    });
});

// Function to show edit modal
// Function to show edit modal
function showEditModal(tweetId, tweetText) {
    // Create edit modal HTML
    const modalHtml = `
        <div class="modal fade" id="editTweetModal" tabindex="-1" role="dialog" aria-labelledby="editTweetModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTweetModalLabel">Edit Tweet</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editTweetForm">
                            <div class="form-group">
                                <textarea class="form-control" id="editTweetText" rows="4" placeholder="What's happening?" style="resize: none; border: none; font-size: 16px;">${tweetText}</textarea>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> You can edit your tweet
                                    </small>
                                </div>
                            </div>
                            <input type="hidden" id="editTweetId" value="${tweetId}">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveEditTweet">
                            <i class="fas fa-save mr-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#editTweetModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $('#editTweetModal').modal('show');
    
    // Focus on textarea and set cursor at end
    const textarea = $('#editTweetText');
    textarea.focus();
    const length = textarea.val().length;
    textarea[0].setSelectionRange(length, length);
    
    // Handle modal hidden event
    $('#editTweetModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
    
    // Save edit handler
    $('#saveEditTweet').on('click', function() {
        const $btn = $(this);
        const originalText = $btn.html();
        
        // Show loading state
        $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
        $btn.prop('disabled', true);
        
        saveTweetEdit(tweetId);
    });
    
    // Allow Enter key to save (but prevent new lines)
    $('#editTweetText').on('keydown', function(e) {
        if (e.ctrlKey && e.keyCode === 13) {
            // Ctrl+Enter to save
            $('#saveEditTweet').click();
        }
    });
}

// Function to save edited tweet
function saveTweetEdit(tweetId) {
    const editedText = $('#editTweetText').val().trim();
    
    if (editedText === '') {
        alert('Tweet cannot be empty!');
        $('#saveEditTweet').html('<i class="fas fa-save mr-1"></i> Save Changes').prop('disabled', false);
        return;
    }
    
    $.ajax({
        url: 'includes/edit_tweet.php',
        type: 'POST',
        data: { 
            tweet_id: tweetId,
            tweet_text: editedText
        },
        dataType: 'json',
        success: function(response) {
            console.log('Save response:', response);
            if (response.success) {
                // Update the tweet text on the page without reloading
                $('.tweet-content').html(response.new_text);
                
                $('#editTweetModal').modal('hide');
                
                // Show success message
                const successHtml = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> ${response.message}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                `;
                $('body').append(successHtml);
                
                // Auto remove success message after 3 seconds
                setTimeout(() => {
                    $('.alert-success').alert('close');
                }, 3000);
                
            } else {
                alert('Error: ' + response.message);
                $('#saveEditTweet').html('<i class="fas fa-save mr-1"></i> Save Changes').prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', error);
            console.log('Response text:', xhr.responseText);
            
            let errorMsg = 'Error updating tweet. ';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMsg += response.message || 'Please try again.';
            } catch (e) {
                errorMsg += 'Please check console for details.';
            }
            
            alert(errorMsg);
            $('#saveEditTweet').html('<i class="fas fa-save mr-1"></i> Save Changes').prop('disabled', false);
        }
    });
}
</script>

<script src="https://kit.fontawesome.com/38e12cc51b.js" crossorigin="anonymous"></script>
<script src="assets/js/popper.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>