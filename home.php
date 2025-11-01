<?php
include 'core/init.php';

// Check if user is logged in first
if (User::checkLogIn() === false) {
    header('location: index.php');
    exit();
}

// Validate session user_id
if (!isset($_SESSION['user_id'])) {
    header('location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = User::getData($user_id);

// Check if user data was found
if (!$user || $user === false) {
    // User doesn't exist - log them out
    session_destroy();
    header('location: index.php');
    exit();
}

$tweets = Tweet::tweets($user_id);
$who_users = Follow::whoToFollow($user_id);
$notify_count = User::CountNotification($user_id);
 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Kabi</title>
    
    <link rel="shortcut icon" type="image/png" href="assets/images/kabi.png"> 
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/home_style.css?v=<?php echo time(); ?>">
    <style>
    /* Tweet Menu Styles */
    .tweet-menu {
        display: inline-block;
        position: relative;
    }
    .tweet-menu-options {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        background: white;
        border: 1px solid #e1e8ed;
        border-radius: 4px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
        min-width: 150px;
    }
    .tweet-menu-option {
        padding: 10px;
        cursor: pointer;
        border-bottom: 1px solid #e1e8ed;
    }
    .tweet-menu-option:hover {
        background: #f5f8fa;
    }
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
        background-color: white;
        margin: 15% auto;
        padding: 20px;
        border-radius: 10px;
        width: 400px;
        max-width: 90%;
    }
    
    /* Notification Styles - IMPROVED */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10001;
        opacity: 0;
        transform: translateX(100px);
        transition: all 0.3s ease;
        max-width: 400px;
        word-wrap: break-word;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 14px;
        line-height: 1.4;
    }
    .notification.success {
        background: #17bf63;
        border-left: 4px solid #119e4f;
    }
    .notification.error {
        background: #e0245e;
        border-left: 4px solid #c51d5a;
    }
    .notification.info {
        background: #1da1f2;
        border-left: 4px solid #0d8bd9;
    }
    
    /* Loading Spinner */
    .fa-spinner {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Emoji Picker Styles */
    .emoji-grid {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: 5px;
    }

    .emoji {
        cursor: pointer;
        padding: 5px;
        border-radius: 4px;
        text-align: center;
        font-size: 1.2em;
        transition: background-color 0.2s;
    }

    .emoji:hover {
        background-color: #f0f0f0;
    }

    #emoji-picker-button:hover {
        background-color: rgba(29, 161, 242, 0.1) !important;
        border-radius: 50%;
    }

    /* Emoji Picker Container */
    #emoji-picker-container {
        display: none;
        position: absolute;
        background: white;
        border: 1px solid #e1e8ed;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        padding: 10px;
        width: 300px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
    }
    </style>
</head>
<body>
  <!-- This is a modal for welcome the new signup account! -->
  <script src="assets/js/jquery-3.5.1.min.js"></script>
     
    <?php  if (isset($_SESSION['welcome'])) { ?>
      <script>
       $(document).ready(function(){
        // Open modal on page load
        $("#welcome").modal('show');
       });
      </script>
    
      <!-- Modal -->
      <div class="modal fade" id="welcome" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="">
              <div class="text-center">
                <span class="modal-title font-weight-bold text-center" id="exampleModalLongTitle">
                  <span style="font-size: 20px;">Welcome <span style="color:#207ce5"><?php echo htmlspecialchars($user->name); ?></span></span>  
                </span>
              </div>
            </div>
            <div class="modal-body">
              <div class="text-center">
                <h4 style="font-weight: 600;">You've Signed up Successfully!</h4>
              </div>
              <p>This is Twitter clone is developed by <span style="font-weight: 700;">Bidushi</span> for learning purpose.</p>
              <p>The kabi project includes tweet , retweet , quote or even quote the quoted tweet , like tweet and nested comments.
                You can mention or add hashtag to your tweet , change password or username.
                Follow or unfollow people. get notification if any action happen. Search users by name or username. and more!
              </p>
              <p>By default you followed
                <a style="color:#207ce5;" href="bidushi">@bidushi</a> 
                to see their tweets.
              </p>
            </div>
          </div>
        </div>
      </div>
      <?php unset($_SESSION['welcome']); } ?>
      <!-- End welcome -->

    
<div id="mine">
    <div class="wrapper-left">
        <div class="sidebar-left">
          <div class="grid-sidebar" style="margin-top: 12px">
            <div class="icon-sidebar-align">
              <img src="assets/images/kabi.png" alt="" height="30px" width="30px" />
            </div>
          </div>

          <a href="home.php">
          <div class="grid-sidebar bg-active" style="margin-top: 12px">
            <div class="icon-sidebar-align">
              <img src="includes/icons/tweethome.png" alt="" height="26.25px" width="26.25px" />
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
                src="assets/images/icons/tweetnotif.png"
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
              <img src="assets/images/icons/tweetprof.png" alt="" height="26.25px" width="26.25px" />
            </div>
  
            <div class="wrapper-left-elements">
              <a href="<?php echo BASE_URL . $user->username; ?>" style="margin-top: 4px"><strong>Profile</strong></a>
            </div>
          </div>
          </a>
          
          <a href="account.php">
          <div class="grid-sidebar ">
            <div class="icon-sidebar-align">
              <img src="assets/images/icons/tweetsetting.png" alt="" height="26.25px" width="26.25px" />
            </div>
  
            <div class="wrapper-left-elements">
              <a href="account.php" style="margin-top: 4px"><strong>Settings</strong></a>
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
                  src="assets/images/users/<?php echo htmlspecialchars($user->img); ?>"
                  alt="user"
                  class="img-user"
                />
              </div>
              <div>
                <p class="name"><strong><?php echo htmlspecialchars($user->name); ?></strong></p>
                <p class="username">@<?php echo htmlspecialchars($user->username); ?></p>
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
              <div class="input-group-login" id="whathappen">
                <div class="container">
                  <div class="part-1">
                    <div class="header">
                      <div class="home">
                        <h2>Home</h2>
                      </div>
                    </div>
            
                    <div class="text">
                      <form class="" action="handle/handleTweet.php" method="post" enctype="multipart/form-data">
                        <div class="inner">
                          <img src="assets/images/users/<?php echo htmlspecialchars($user->img); ?>" alt="profile photo">
                          <label>
                            <textarea class="text-whathappen" name="status" rows="8" cols="80" placeholder="What's happening?"></textarea>
                          </label>
                        </div> 
                            
                        <!-- tmp image upload place -->
                        <div class="position-relative upload-photo" style="display: none;"> 
                          <img class="img-upload-tmp" src="" alt="">
                          <div class="icon-bg">
                            <i id="upload-delete-tmp" class="fas fa-times position-absolute upload-delete"></i>  
                          </div>
                        </div>

                        <div class="bottom"> 
                          <div class="bottom-container">
                            <label for="tweet_img" class="ml-3 mb-2 uni">
                              <i class="fa fa-image item1-pair"></i>
                            </label>
                            <input class="tweet_img" id="tweet_img" type="file" name="tweet_img">
                            <button type="button" id="emoji-picker-button" class="ml-3 mb-2 uni" style="background: none; border: none; cursor: pointer;">
                            <i class="far fa-smile item1-pair" style="font-size: 1.2em;"></i>
                          </button>    
                          </div>
                          
                         
                          
                          <!-- Simple Emoji Picker Container -->
                          <div id="emoji-picker-container">
                            <div class="emoji-category">
                              <div class="emoji-grid">
                                <span class="emoji" data-emoji="😀">😀</span>
                                <span class="emoji" data-emoji="😃">😃</span>
                                <span class="emoji" data-emoji="😄">😄</span>
                                <span class="emoji" data-emoji="😁">😁</span>
                                <span class="emoji" data-emoji="😆">😆</span>
                                <span class="emoji" data-emoji="😅">😅</span>
                                <span class="emoji" data-emoji="😂">😂</span>
                                <span class="emoji" data-emoji="🤣">🤣</span>
                                <span class="emoji" data-emoji="😊">😊</span>
                                <span class="emoji" data-emoji="😇">😇</span>
                                <span class="emoji" data-emoji="🙂">🙂</span>
                                <span class="emoji" data-emoji="🙃">🙃</span>
                                <span class="emoji" data-emoji="😉">😉</span>
                                <span class="emoji" data-emoji="😌">😌</span>
                                <span class="emoji" data-emoji="😍">😍</span>
                                <span class="emoji" data-emoji="🥰">🥰</span>
                                <span class="emoji" data-emoji="😘">😘</span>
                                <span class="emoji" data-emoji="😗">😗</span>
                                <span class="emoji" data-emoji="😙">😙</span>
                                <span class="emoji" data-emoji="😚">😚</span>
                                <span class="emoji" data-emoji="😋">😋</span>
                                <span class="emoji" data-emoji="😛">😛</span>
                                <span class="emoji" data-emoji="😝">😝</span>
                                <span class="emoji" data-emoji="😜">😜</span>
                                <span class="emoji" data-emoji="🤪">🤪</span>
                                <span class="emoji" data-emoji="🤨">🤨</span>
                                <span class="emoji" data-emoji="🧐">🧐</span>
                                <span class="emoji" data-emoji="🤓">🤓</span>
                                <span class="emoji" data-emoji="😎">😎</span>
                                <span class="emoji" data-emoji="🤩">🤩</span>
                                <span class="emoji" data-emoji="🥳">🥳</span>
                                <span class="emoji" data-emoji="😏">😏</span>
                                <span class="emoji" data-emoji="😒">😒</span>
                                <span class="emoji" data-emoji="😞">😞</span>
                                <span class="emoji" data-emoji="😔">😔</span>
                                <span class="emoji" data-emoji="😟">😟</span>
                                <span class="emoji" data-emoji="😕">😕</span>
                                <span class="emoji" data-emoji="🙁">🙁</span>
                                <span class="emoji" data-emoji="☹️">☹️</span>
                                <span class="emoji" data-emoji="😣">😣</span>
                                <span class="emoji" data-emoji="😖">😖</span>
                                <span class="emoji" data-emoji="😫">😫</span>
                                <span class="emoji" data-emoji="😩">😩</span>
                                <span class="emoji" data-emoji="🥺">🥺</span>
                                <span class="emoji" data-emoji="😢">😢</span>
                                <span class="emoji" data-emoji="😭">😭</span>
                                <span class="emoji" data-emoji="😤">😤</span>
                                <span class="emoji" data-emoji="😠">😠</span>
                                <span class="emoji" data-emoji="😡">😡</span>
                                <span class="emoji" data-emoji="🤬">🤬</span>
                                <span class="emoji" data-emoji="🤯">🤯</span>
                                <span class="emoji" data-emoji="😳">😳</span>
                                <span class="emoji" data-emoji="🥵">🥵</span>
                                <span class="emoji" data-emoji="🥶">🥶</span>
                                <span class="emoji" data-emoji="😱">😱</span>
                                <span class="emoji" data-emoji="😨">😨</span>
                                <span class="emoji" data-emoji="😰">😰</span>
                                <span class="emoji" data-emoji="😥">😥</span>
                                <span class="emoji" data-emoji="😓">😓</span>
                                <span class="emoji" data-emoji="🤗">🤗</span>
                                <span class="emoji" data-emoji="🤔">🤔</span>
                                <span class="emoji" data-emoji="🤭">🤭</span>
                                <span class="emoji" data-emoji="🤫">🤫</span>
                                <span class="emoji" data-emoji="🤥">🤥</span>
                                <span class="emoji" data-emoji="😶">😶</span>
                                <span class="emoji" data-emoji="😐">😐</span>
                                <span class="emoji" data-emoji="😑">😑</span>
                                <span class="emoji" data-emoji="😬">😬</span>
                                <span class="emoji" data-emoji="🙄">🙄</span>
                                <span class="emoji" data-emoji="😯">😯</span>
                                <span class="emoji" data-emoji="😦">😦</span>
                                <span class="emoji" data-emoji="😧">😧</span>
                                <span class="emoji" data-emoji="😮">😮</span>
                                <span class="emoji" data-emoji="😲">😲</span>
                                <span class="emoji" data-emoji="🥱">🥱</span>
                                <span class="emoji" data-emoji="😴">😴</span>
                                <span class="emoji" data-emoji="🤤">🤤</span>
                                <span class="emoji" data-emoji="😪">😪</span>
                                <span class="emoji" data-emoji="😵">😵</span>
                                <span class="emoji" data-emoji="🤐">🤐</span>
                                <span class="emoji" data-emoji="🥴">🥴</span>
                                <span class="emoji" data-emoji="🤢">🤢</span>
                                <span class="emoji" data-emoji="🤮">🤮</span>
                                <span class="emoji" data-emoji="🤧">🤧</span>
                                <span class="emoji" data-emoji="😷">😷</span>
                                <span class="emoji" data-emoji="🤒">🤒</span>
                                <span class="emoji" data-emoji="🤕">🤕</span>
                                <span class="emoji" data-emoji="🤑">🤑</span>
                                <span class="emoji" data-emoji="🤠">🤠</span>
                                <span class="emoji" data-emoji="😈">😈</span>
                                <span class="emoji" data-emoji="👿">👿</span>
                                <span class="emoji" data-emoji="👹">👹</span>
                                <span class="emoji" data-emoji="👺">👺</span>
                                <span class="emoji" data-emoji="🤡">🤡</span>
                                <span class="emoji" data-emoji="💩">💩</span>
                                <span class="emoji" data-emoji="👻">👻</span>
                                <span class="emoji" data-emoji="💀">💀</span>
                                <span class="emoji" data-emoji="☠️">☠️</span>
                                <span class="emoji" data-emoji="👽">👽</span>
                                <span class="emoji" data-emoji="👾">👾</span>
                                <span class="emoji" data-emoji="🤖">🤖</span>
                                <span class="emoji" data-emoji="🎃">🎃</span>
                                <span class="emoji" data-emoji="😺">😺</span>
                                <span class="emoji" data-emoji="😸">😸</span>
                                <span class="emoji" data-emoji="😹">😹</span>
                                <span class="emoji" data-emoji="😻">😻</span>
                                <span class="emoji" data-emoji="😼">😼</span>
                                <span class="emoji" data-emoji="😽">😽</span>
                                <span class="emoji" data-emoji="🙀">🙀</span>
                                <span class="emoji" data-emoji="😿">😿</span>
                                <span class="emoji" data-emoji="😾">😾</span>
                              </div>
                            </div>
                          </div>
                          
                          <div class="hash-box">
                            <ul style="margin-bottom: 0;"></ul>
                          </div>
                          
                          <?php if (isset($_SESSION['errors_tweet'])) { 
                            foreach($_SESSION['errors_tweet'] as $t) { ?>
                            <div class="alert alert-danger">
                              <span class="item2-pair"><?php echo htmlspecialchars($t); ?></span>
                            </div>
                          <?php } 
                            unset($_SESSION['errors_tweet']); 
                          } ?>
                          
                          <div>
                            <!-- COMPLETELY REMOVE THE COUNTER SPAN -->
                            <input id="tweet-input" type="submit" name="tweet" value="Post" class="submit">
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                  <div class="part-2"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="box-fixed" id="box-fixed"></div>
            
          <?php include 'includes/tweets.php'; ?>
        </div>

        <div class="wrapper-right">
          <div style="width: 90%;" class="container">
            <div class="input-group py-2 m-auto pr-5 position-relative">
              <i id="icon-search" class="fas fa-search tryy"></i>
              <input type="text" class="form-control search-input" placeholder="Search kabi">
              <div class="search-result"></div>
            </div>
          </div>

          <div class="box-share">
            <p class="txt-share"><strong>Who to follow</strong></p>
            <?php 
            foreach($who_users as $who_user) { 
              $user_follow = Follow::isUserFollow($user_id, $who_user->id);
            ?>
              <div class="grid-share">
                <a style="position: relative; z-index:5; color:black" href="<?php echo BASE_URL . $who_user->username; ?>">
                  <img
                    src="assets/images/users/<?php echo htmlspecialchars($who_user->img); ?>"
                    alt=""
                    class="img-share"
                  />
                </a>
                <div>
                  <p>
                    <a style="position: relative; z-index:5; color:black" href="<?php echo BASE_URL . $who_user->username; ?>">  
                      <strong><?php echo htmlspecialchars($who_user->name); ?></strong>
                    </a>
                  </p>
                  <p class="username">
                    @<?php echo htmlspecialchars($who_user->username); ?>
                    <?php if (Follow::FollowsYou($who_user->id, $user_id)) { ?>
                      <span class="ml-1 follows-you">Follows You</span>
                    <?php } ?>
                  </p>
                </div>
                <div>
                  <button class="follow-btn follow-btn-m 
                    <?= $user_follow ? 'following' : 'follow' ?>"
                    data-follow="<?php echo $who_user->id; ?>"
                    data-user="<?php echo $user_id; ?>"
                    data-profile="<?php echo $user_id; ?>"
                    style="font-weight: 700;">
                    <?php if($user_follow) { ?>
                      Following 
                    <?php } else { ?>  
                      Follow
                    <?php } ?> 
                  </button>
                </div>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
    
    <script src="assets/js/search.js"></script>
    <script src="assets/js/photo.js?v=<?php echo time(); ?>"></script>
    <script type="text/javascript" src="assets/js/hashtag.js"></script>
    <script type="text/javascript" src="assets/js/like.js"></script>
    <script type="text/javascript" src="assets/js/comment.js?v=<?php echo time(); ?>"></script>
    <script type="text/javascript" src="assets/js/retweet.js?v=<?php echo time(); ?>"></script>
    <script type="text/javascript" src="assets/js/follow.js?v=<?php echo time(); ?>"></script>
    <script src="https://kit.fontawesome.com/38e12cc51b.js" crossorigin="anonymous"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
    <script>
    // Emoji Picker Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const emojiButton = document.getElementById('emoji-picker-button');
        const emojiContainer = document.getElementById('emoji-picker-container');
        const textarea = document.querySelector('.text-whathappen');

        if (emojiButton && emojiContainer && textarea) {
            // Toggle emoji picker
            emojiButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Toggle display
                if (emojiContainer.style.display === 'block') {
                    emojiContainer.style.display = 'none';
                } else {
                    emojiContainer.style.display = 'block';
                    // Position the emoji picker relative to the button
                    const rect = emojiButton.getBoundingClientRect();
                    emojiContainer.style.bottom = 'auto';
                    emojiContainer.style.top = (rect.top + window.scrollY - emojiContainer.offsetHeight - 10) + 'px';
                    emojiContainer.style.left = (rect.left + window.scrollX) + 'px';
                }
            });

            // Handle emoji selection
            emojiContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('emoji')) {
                    const emoji = e.target.getAttribute('data-emoji');
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const text = textarea.value;
                    
                    // Insert emoji at cursor position
                    textarea.value = text.substring(0, start) + emoji + text.substring(end);
                    
                    // Set cursor position after inserted emoji
                    textarea.selectionStart = textarea.selectionEnd = start + emoji.length;
                    
                    // Focus back to textarea
                    textarea.focus();
                    
                    // Hide emoji picker
                    emojiContainer.style.display = 'none';
                    
                    // Trigger input event for any character counters
                    textarea.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });

            // Close emoji picker when clicking outside
            document.addEventListener('click', function(e) {
                if (!emojiContainer.contains(e.target) && e.target !== emojiButton) {
                    emojiContainer.style.display = 'none';
                }
            });

            // Close emoji picker on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    emojiContainer.style.display = 'none';
                }
            });
        }
    });

    // Tweet Menu and Delete Functionality
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
        
        console.log('Submitting report for tweet:', tweetId, 'Reason:', reason, 'Description:', description);
        
        if (!reason) {
            showNotification('Please select a reason for reporting', 'error');
            return;
        }
        
        // Show loading state
        const submitBtn = document.querySelector('#reportForm button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('action', 'report_tweet');
        formData.append('tweet_id', tweetId);
        formData.append('reason', reason);
        formData.append('description', description);
        
        console.log('Sending report data:', {
            action: 'report_tweet',
            tweet_id: tweetId,
            reason: reason,
            description: description
        });
        
        fetch('delete_tweet.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Report response status:', response.status);
            console.log('Report response headers:', response.headers);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text().then(text => {
                console.log('Raw response text:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid JSON response from server');
                }
            });
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
    </script>
</body>
</html>