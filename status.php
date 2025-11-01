<?php
   include 'core/init.php';
  
   $user_id = $_SESSION['user_id'];
  
   $user = User::getData($user_id);
   
   if (User::checkLogIn() === false) 
   header('location: index.php');


   $tweet_id =  $_GET['post_id'];
   $tweet = Tweet::getData($tweet_id);
   $who_users = Follow::whoToFollow($user_id);
   $notify_count = User::CountNotification($user_id);
 
    
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
        /* Edit Modal Styles */
        .edit-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        
        .edit-modal-content {
            background-color: white;
            border-radius: 16px;
            width: 600px;
            max-width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .edit-modal-header {
            padding: 16px;
            border-bottom: 1px solid #E9ECEF;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .edit-modal-body {
            padding: 16px;
        }
        
        .edit-textarea {
            width: 100%;
            border: none;
            resize: none;
            font-size: 20px;
            min-height: 200px;
            outline: none;
            font-family: inherit;
        }
        
        .edit-modal-footer {
            padding: 16px;
            border-top: 1px solid #E9ECEF;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        
        .edit-btn {
            background-color: #1DA1F2;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .edit-btn:disabled {
            background-color: #8ED0F9;
            cursor: not-allowed;
        }
        
        .cancel-btn {
            background-color: transparent;
            border: 1px solid #CFD9DE;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
        }
        
        /* Three-dot menu styles */
        .three-dot-menu {
            position: relative;
            display: inline-block;
        }
        
        .three-dot-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: white;
            min-width: 180px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 4px;
            z-index: 1000;
            border: 1px solid #E9ECEF;
        }
        
        .three-dot-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            cursor: pointer;
        }
        
        .three-dot-content a:hover {
            background-color: #f8f9fa;
        }
        
        .show {
            display: block;
        }
        
        .delete-option {
            color: #E0245E !important;
        }
        
        .delete-option:hover {
            background-color: #ffe6e6 !important;
        }
    </style>
</head>
<body>
<script src="assets/js/jquery-3.5.1.min.js"></script>
  
    <div id="mine">
 
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
              <a class="wrapper-left-active" href="home.php" style="margin-top: 4px;"><strong>Home</strong></a>
            </div>
          </div>
          </a>
  
          <a href="notification.php">
          <div class="grid-sidebar">
            <div class="icon-sidebar-align position-relative">
                <?php if ($notify_count > 0) { ?>
              <i class="notify-count"><?php echo $notify_count; ?></i> 
              <?php } ?>
              <img
                src="<?php echo BASE_URL . "/includes/icons/tweetnotif.png"; ?>"
                alt=""
                height="26.25px"
                width="26.25px"
              />
            </div>
  
            <div class="wrapper-left-elements">
              <a href="notification.php" style="margin-top: 4px"><strong>Notifications</strong></a>
            </div>
          </div>
          </a>
        
            <a href="<?php echo BASE_URL . $user->username; ?>">
          <div class="grid-sidebar">
            <div class="icon-sidebar-align">
              <img src="<?php echo BASE_URL . "/includes/icons/tweetprof.png"; ?>" alt="" height="26.25px" width="26.25px" />
            </div>
  
            <div class="wrapper-left-elements">
              <a  href="<?php echo BASE_URL . $user->username; ?>"  style="margin-top: 4px"><strong>Profile</strong></a>
            </div>
          </div>
          </a>
          <a href="<?php echo BASE_URL . "account.php"; ?>">
          <div class="grid-sidebar ">
            <div class="icon-sidebar-align">
              <img src="<?php echo BASE_URL . "/includes/icons/tweetsetting.png"; ?>" alt="" height="26.25px" width="26.25px" />
            </div>
  
            <div class="wrapper-left-elements">
              <a href="<?php echo BASE_URL . "account.php"; ?>" style="margin-top: 4px"><strong>Settings</strong></a>
            </div>
          </div>
          </a>
          <a href="includes/logout.php">
          <div class="grid-sidebar">
            <div class="icon-sidebar-align">
            <i style="font-size: 26px;" class="fas fa-sign-out-alt"></i>
            </div>
  
            <div class="wrapper-left-elements">
              <a href="includes/logout.php" style="margin-top: 4px"><strong>Logout</strong></a>
            </div>
          </div>
          </a>
        
  
          <div class="box-user">
            <div class="grid-user">
              <div>
                <img
                  src="assets/images/users/<?php echo $user->img ?>"
                  alt="user"
                  class="img-user"
                />
              </div>
              <div>
                <p class="name"><strong><?php if($user->name !== null) {
                echo $user->name; } ?></strong></p>
                <p class="username">@<?php echo $user->username; ?></p>
              </div>
              <div class="mt-arrow">
                <img
                  src="https://i.ibb.co/mRLLwdW/arrow-down.png"
                  alt=""
                  height="18.75px"
                  width="18.75px"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
          
  

      <div class="grid-posts">
        <div class="border-right">
          <div class="grid-toolbar-center">
            <div class="center-input-search">
                <div class="container" style="border-bottom: 1px solid #E9ECEF;">
                  <div class="row">
                       <div class="col-xs-1">
                 <a href="javascript: history.go(-1);"> <i style="font-size:20px;" class="fas fa-arrow-left arrow-style"></i> </a>
                       </div>
                       <div class="col-xs-10 mt-1">
                           <p class="tweet-name" style="font-weight:700"> Tweet</p>
                      </div>
                  </div>
                  <div class="part-2">
                  </div>
                </div>
            </div>
          </div> 
          
          <div class="box-fixed" id="box-fixed"></div>
          
          <?php 
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
                    $retweeted_tweet = Tweet::getRetweet($retweet->retweet_id);
                        if($retweeted_tweet->tweet_id != null) {
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
                        $retweet_sign = true;
                        $tweet_user = User::getData($retweeted_tweet->user_id) ;
                         $timeAgo = Tweet::getTimeAgo($retweeted_tweet->post_on) ; 
                        $likes_count = Tweet::countLikes($retweeted_tweet->post_id) ;
                        $user_like_it = Tweet::userLikeIt($user_id ,$retweeted_tweet->post_id);
                        $retweets_count = Tweet::countRetweets($retweeted_tweet->post_id) ;
                        $user_retweeted_it = Tweet::userRetweeetedIt($user_id ,$retweeted_tweet->post_id);
                        $qoq = true;
                        $qoute = $retweeted_tweet->retweet_msg;
                        $tweet_inner = Tweet::getRetweet($retweeted_tweet->retweet_id);
                        $user_inner_tweet = User::getData($tweet_inner->user_id) ;
                        $timeAgo_inner = Tweet::getTimeAgo($tweet_inner->post_on);
                        $inner_qoute  = $tweet_inner->retweet_msg;
                        $retweeted_user = User::getData($tweet->user_id);
                        }
                    }
            } else {
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
            $tweet_user = User::getData($tweet->user_id) ;
            $timeAgo = Tweet::getTimeAgo($tweet->post_on) ; 
            $likes_count = Tweet::countLikes($tweet->id) ;
            $user_like_it = Tweet::userLikeIt($user_id ,$tweet->id);
            $retweets_count = Tweet::countRetweets($tweet->id) ;
            $user_retweeted_it = Tweet::userRetweeetedIt($user_id ,$tweet->id);
            $qoute = $retweet->retweet_msg;
            $qoq = true;
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
               
            if ($retweet_sign)
              $comments = Tweet::comments($retweeted_tweet->id);
              else  $comments = Tweet::comments($tweet_id);

            if($retweet_sign)
             $comment_count = Tweet::countComments($retweeted_tweet->id);
             else  $comment_count = Tweet::countComments($tweet->id); 
            
            // Check if current user is the owner of the tweet
            $is_owner = false;
            if ($retweet_sign) {
                $is_owner = ($retweeted_user->id == $user_id);
            } else {
                $is_owner = ($tweet_user->id == $user_id);
            }
            ?>
              
          <div class="box-tweet feed" style="position: relative;" >
                 <a href="status/<?php echo $tweet->id; ?>">
                    <span style="position:absolute; width:100%; height:100%; top:0;left: 0; z-index: 1;"></span>
                 </a>
            <?php if ($retweet_sign) { ?>
            <span class="retweed-name"> <i class="fa fa-retweet retweet-name-i" aria-hidden="true"></i> 
            <a style="position: relative; z-index:100; color:rgb(102, 117, 130);" href="<?php echo $retweeted_user->name; ?> "> <?php  if($retweeted_user->id == $user_id) echo "You";
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
                </p>
                <p id="tweet-content-<?php echo $tweet->id; ?>">
                  <?php
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
                <?php if ($qoq == false) { 
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

                <div class="row home-follow pt-3">
                        <?php if($retweets_count > 0)  { ?>
                            <div class="col-md-2 users-count" >
                            <i class="retweets-u"
                            data-tweet="<?php 
                            if($retweet_sign)
                                echo $retweeted_tweet->id;
                            else  echo $tweet->id; ?>"> 
                     <span class="home-follow-count"> <?php echo $retweets_count ; ?> </span> Retweets</i>
                        </div> 
                        <?php } ?> 
                        <?php if($likes_count > 0)  { ?>
                        <div class="col-md-2 users-count">
                            <div class="likes-u" 
                            data-tweet="<?php 
                            if($retweet_sign)
                                echo $retweeted_tweet->id;
                            else  echo $tweet->id; ?>">
                             <span class="home-follow-count">  <?php echo $likes_count ; ?>  </span> Likes</div>
                        </div>   
                        <?php } ?> 
                  </div>

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
                        <p> <?php if($comment_count > 0) echo $comment_count; ?> </p>
                      </div>
                    </div>
                  </div>
                  <div class="grid-box-reaction">
                    <div  class="hover-reaction hover-reaction-retweet
                    <?= $user_retweeted_it ? 'retweeted' : 'retweet' ?> option"
                    data-tweet="<?php echo $tweet->id ; ?>" 
                    data-user="<?php echo $user_id; ?>"
                    data-retweeted = "<?php echo $user_retweeted_it; ?>"
                    data-sign = "<?php echo $retweet_sign; ?>"
                    data-tmp="<?php echo $retweet_comment; ?>"
                    data-qoq="<?php echo $qoq; ?>"
                    data-status="<?php echo true; ?>">
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
                    <div class="three-dot-menu">
                      <div class="hover-reaction hover-reaction-comment three-dot-trigger">
                        <i class="fas fa-ellipsis-h mt-icon-reaction"></i>
                      </div>
                      <div class="three-dot-content" id="three-dot-<?php echo $tweet->id; ?>">
                        <?php if ($is_owner): ?>
                        <a class="edit-option" data-tweet-id="<?php echo $tweet->id; ?>" data-tweet-content="<?php echo htmlspecialchars($retweet_comment || $qoq ? $qoute : $tweet_real->status); ?>">Edit Tweet</a>
                        <a class="delete-option" data-tweet-id="<?php echo $tweet->id; ?>">Delete Tweet</a>
                        <?php else: ?>
                        <a>Report Tweet</a>
                        <a>Mute @<?php echo $tweet_user->username; ?></a>
                        <a>Block @<?php echo $tweet_user->username; ?></a>
                        <?php endif; ?>
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
             
              <div class="comments">
          <!-- comments place --> 
          <?php foreach($comments as $comment) { 
                     $tweet_user = User::getData($comment->user_id) ;
                     $timeAgo = Tweet::getTimeAgo($comment->time);
                     $replies = Tweet::replies($comment->id);
                     $reply_count = Tweet::countReplies($comment->id);
                     $is_comment_owner = ($comment->user_id == $user_id);
              ?>

          <div class="box-comment feed py-2"  >
            <div class="grid-tweet">
              <div>
                <img
                  src="assets/images/users/<?php echo $tweet_user->img; ?>"
                  alt=""
                  class="img-user-tweet"
                />
              </div>
  
              <div>
                <p>
                  <strong> <?php echo $tweet_user->name ?> </strong>
                  <span class="username-twitter">@<?php echo $tweet_user->username ?> </span>
                  <span class="username-twitter"><?php echo $timeAgo ?></span>
                </p>
                <p id="comment-content-<?php echo $comment->id; ?>">
                  <?php echo  Tweet::getTweetLinks($comment->comment); ?>
                </p>
                    
                <div class="grid-reactions">
                  <div class="grid-box-reaction-rep">
                    <div class="hover-reaction-rep hover-reaction-comment reply"
                    data-user = "<?php echo $user_id; ?>" 
                    data-tweet = "<?php echo $comment->id; ?>">
                      <i class="far fa-comment"></i>
                      <div class="mt-counter likes-count d-inline-block">
                        <p > <?php if($reply_count > 0) echo $reply_count; ?> </p>
                      </div>
                    </div>
                  </div>
                  
                  <?php if ($is_comment_owner): ?>
                  <div class="grid-box-reaction-rep">
                    <div class="three-dot-menu">
                      <div class="hover-reaction-rep hover-reaction-comment three-dot-trigger">
                        <i class="fas fa-ellipsis-h mt-icon-reaction"></i>
                      </div>
                      <div class="three-dot-content" id="three-dot-comment-<?php echo $comment->id; ?>">
                        <a class="edit-option" data-comment-id="<?php echo $comment->id; ?>" data-comment-content="<?php echo htmlspecialchars($comment->comment); ?>">Edit Comment</a>
                        <a class="delete-option" data-comment-id="<?php echo $comment->id; ?>">Delete Comment</a>
                      </div>
                    </div>
                  </div>
                  <?php endif; ?>
                </div>
              </div> 
            </div> 
          </div> 

          <!-- replies -->
          <?php foreach ($replies as $reply) {
                 $tweet_user = User::getData($reply->user_id) ;
                 $timeAgo = Tweet::getTimeAgo($reply->time);
                 $is_reply_owner = ($reply->user_id == $user_id);
            ?>
            <div class="box-reply feed"  >
              <div class="grid-tweet">
                <div>
                  <img
                    src="assets/images/users/<?php echo $tweet_user->img; ?>"
                    alt=""
                    class="img-user-tweet"
                  />
                </div>
                <div>
                  <p>
                    <strong> <?php echo $tweet_user->name ?> </strong>
                    <span class="username-twitter">@<?php echo $tweet_user->username ?> </span>
                    <span class="username-twitter"><?php echo $timeAgo ?></span>
                  </p>
                  <p id="reply-content-<?php echo $reply->id; ?>">
                    <?php echo  Tweet::getTweetLinks($reply->reply); ?>
                  </p>
                  
                  <?php if ($is_reply_owner): ?>
                  <div class="grid-reactions">
                    <div class="grid-box-reaction-rep">
                      <div class="three-dot-menu">
                        <div class="hover-reaction-rep hover-reaction-comment three-dot-trigger">
                          <i class="fas fa-ellipsis-h mt-icon-reaction"></i>
                        </div>
                        <div class="three-dot-content" id="three-dot-reply-<?php echo $reply->id; ?>">
                          <a class="edit-option" data-reply-id="<?php echo $reply->id; ?>" data-reply-content="<?php echo htmlspecialchars($reply->reply); ?>">Edit Reply</a>
                          <a class="delete-option" data-reply-id="<?php echo $reply->id; ?>">Delete Reply</a>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php endif; ?>
                </div> 
              </div> 
            </div> 
          <?php } ?>
          <?php } ?>
         
          <div class="popupTweet"></div>
          <div class="popupComment"></div>
          <div class="popupUsers"></div>
        </div>
      </div> 

      <!-- Edit Modal -->
      <div class="edit-modal" id="editModal">
        <div class="edit-modal-content">
          <div class="edit-modal-header">
            <button type="button" class="cancel-btn" id="closeEditModal">Cancel</button>
            <strong>Edit Tweet</strong>
            <button type="button" class="edit-btn" id="saveEdit">Save</button>
          </div>
          <div class="edit-modal-body">
            <textarea class="edit-textarea" id="editTextarea" maxlength="280" placeholder="What's happening?"></textarea>
            <div class="char-count" style="text-align: right; color: #657786; font-size: 14px; margin-top: 10px;">
              <span id="charCount">0</span>/280
            </div>
          </div>
        </div>
      </div>
      
        <div class="wrapper-right">
            <div style="width: 90%;" class="container">
          <div class="input-group py-2 m-auto pr-5 position-relative">
          <i id="icon-search" class="fas fa-search tryy"></i>
          <input type="text" class="form-control search-input"  placeholder="Search Twitter">
          <div class="search-result">
          </div>
          </div>
          </div>

          <div class="box-share">
            <p class="txt-share"><strong>Who to follow</strong></p>
            <?php 
            foreach($who_users as $user) { 
               $user_follow = Follow::isUserFollow($user_id , $user->id) ;
               ?>
          <div class="grid-share">
          <a style="position: relative; z-index:5; color:black" href="<?php echo $user->username;  ?>">
                      <img
                        src="assets/images/users/<?php echo $user->img; ?>"
                        alt=""
                        class="img-share"
                      />
                    </a>
                    <div>
                      <p>
                      <a style="position: relative; z-index:5; color:black" href="<?php echo $user->username;  ?>">  
                      <strong><?php echo $user->name; ?></strong>
                      </a>
                    </p>
                      <p class="username">@<?php echo $user->username; ?>
                      <?php if (Follow::FollowsYou($user->id , $user_id)) { ?>
                  <span class="ml-1 follows-you">Follows You</span></p>
                  <?php } ?></p></p>
                    </div>
                    <div>
                      <button class="follow-btn follow-btn-m 
                      <?= $user_follow ? 'following' : 'follow' ?>"
                      data-follow="<?php echo $user->id; ?>"
                      data-user="<?php echo $user_id; ?>"
                      data-profile="<?php echo $u_id; ?>"
                      style="font-weight: 700;">
                      <?php if($user_follow) { ?>
                        Following 
                      <?php } else {  ?>  
                          Follow
                        <?php }  ?> 
                      </button>
                    </div>
                  </div>
                  <?php }?>
          </div>
        </div>
      </div>
      </div> 

      <script src="assets/js/search.js"></script>
      <script type="text/javascript" src="assets/js/hashtag.js"></script>
      <script type="text/javascript" src="assets/js/like.js"></script>
      <script type="text/javascript" src="assets/js/users.js"></script>
      <script type="text/javascript" src="assets/js/comment.js?v=<?php echo time(); ?>"></script>
      <script type="text/javascript" src="assets/js/retweet.js?v=<?php echo time(); ?>"></script>
      <script type="text/javascript" src="assets/js/follow.js?v=<?php echo time(); ?>"></script>
      
      <!-- Edit functionality JavaScript -->
      <script>
      $(document).ready(function() {
          let currentEditId = null;
          let currentEditType = null; // 'tweet', 'comment', or 'reply'
          
          // Three-dot menu toggle
          $('.three-dot-trigger').click(function(e) {
              e.stopPropagation();
              const menu = $(this).siblings('.three-dot-content');
              $('.three-dot-content').not(menu).removeClass('show');
              menu.toggleClass('show');
          });
          
          // Close three-dot menu when clicking elsewhere
          $(document).click(function() {
              $('.three-dot-content').removeClass('show');
          });
          
          // Edit option click
          $('.edit-option').click(function(e) {
              e.preventDefault();
              e.stopPropagation();
              
              if ($(this).data('tweet-id')) {
                  // Editing a tweet
                  currentEditType = 'tweet';
                  currentEditId = $(this).data('tweet-id');
                  const content = $(this).data('tweet-content');
                  $('#editTextarea').val(content);
              } else if ($(this).data('comment-id')) {
                  // Editing a comment
                  currentEditType = 'comment';
                  currentEditId = $(this).data('comment-id');
                  const content = $(this).data('comment-content');
                  $('#editTextarea').val(content);
              } else if ($(this).data('reply-id')) {
                  // Editing a reply
                  currentEditType = 'reply';
                  currentEditId = $(this).data('reply-id');
                  const content = $(this).data('reply-content');
                  $('#editTextarea').val(content);
              }
              
              $('#charCount').text($('#editTextarea').val().length);
              $('#editModal').show();
              $('.three-dot-content').removeClass('show');
          });
          
          // Close modal
          $('#closeEditModal').click(function() {
              $('#editModal').hide();
              currentEditId = null;
              currentEditType = null;
          });
          
          // Character count
          $('#editTextarea').on('input', function() {
              const length = $(this).val().length;
              $('#charCount').text(length);
              
              if (length > 280) {
                  $('#charCount').css('color', '#E0245E');
                  $('#saveEdit').prop('disabled', true);
              } else {
                  $('#charCount').css('color', '#657786');
                  $('#saveEdit').prop('disabled', false);
              }
          });
          
          // Save edit
          $('#saveEdit').click(function() {
              const newContent = $('#editTextarea').val().trim();
              
              if (newContent === '') {
                  alert('Content cannot be empty');
                  return;
              }
              
              if (newContent.length > 1000000) {
                  alert('Content is too long');
                  return;
              }
              
              $.ajax({
                  url: 'includes/edit_post.php',
                  type: 'POST',
                  data: {
                      id: currentEditId,
                      type: currentEditType,
                      content: newContent
                  },
                  success: function(response) {
                      const result = JSON.parse(response);
                      if (result.success) {
                          // Update the content on the page
                          if (currentEditType === 'tweet') {
                              $('#tweet-content-' + currentEditId).html(result.formatted_content);
                          } else if (currentEditType === 'comment') {
                              $('#comment-content-' + currentEditId).html(result.formatted_content);
                          } else if (currentEditType === 'reply') {
                              $('#reply-content-' + currentEditId).html(result.formatted_content);
                          }
                          
                          $('#editModal').hide();
                          currentEditId = null;
                          currentEditType = null;
                      } else {
                          alert('Error: ' + result.message);
                      }
                  },
                  error: function() {
                      alert('Error updating content');
                  }
              });
          });
          
          // Delete option click
          $('.delete-option').click(function(e) {
              e.preventDefault();
              e.stopPropagation();
              
              if (!confirm('Are you sure you want to delete this?')) {
                  return;
              }
              
              let deleteData = {};
              
              if ($(this).data('tweet-id')) {
                  deleteData = {
                      id: $(this).data('tweet-id'),
                      type: 'tweet'
                  };
              } else if ($(this).data('comment-id')) {
                  deleteData = {
                      id: $(this).data('comment-id'),
                      type: 'comment'
                  };
              } else if ($(this).data('reply-id')) {
                  deleteData = {
                      id: $(this).data('reply-id'),
                      type: 'reply'
                  };
              }
              
              $.ajax({
                  url: 'includes/delete_post.php',
                  type: 'POST',
                  data: deleteData,
                  success: function(response) {
                      const result = JSON.parse(response);
                      if (result.success) {
                          location.reload();
                      } else {
                          alert('Error: ' + result.message);
                      }
                  },
                  error: function() {
                      alert('Error deleting content');
                  }
              });
              
              $('.three-dot-content').removeClass('show');
          });
      });
      </script>
      
      <script src="https://kit.fontawesome.com/38e12cc51b.js" crossorigin="anonymous"></script>
      <script src="assets/js/popper.min.js"></script>
      <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>