<?php  
        include 'core/init.php';

        $user_id = $_SESSION['user_id'];
        $user = User::getData($user_id);
        $who_users = Follow::whoToFollow($user_id);

        // update notification count
        User::updateNotifications($user_id);
  
        $notify_count = User::CountNotification($user_id);
        $notofication = User::notification($user_id);

        if (User::checkLogIn() === false) 
            header('location: index.php');    
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | Kabi</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/profile_style.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" type="image/png" href="assets/images/kabi.png"> 
</head>
<body>

<script src="assets/js/jquery-3.5.1.min.js"></script>

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

      <div class="main-content">
        <div class="notification-section">
          <div class="notification-header">
            <div class="header-content">
              <a href="javascript: history.go(-1);" class="back-arrow"> 
                <i class="fas fa-arrow-left"></i> 
              </a>
              <h2 class="page-title">Notifications</h2>
            </div>
          </div>

          <div class="notification-list">
            <?php foreach($notofication as $notify) { 
              $user = User::getData($notify->notify_from);
              $timeAgo = Tweet::getTimeAgo($notify->time);
              
              if ($notify->type == 'like') { 
                $icon = "<i class='fas fa-heart notification-icon like'></i>";
                $msg = "Liked Your Tweet";
              } else if ($notify->type == 'retweet') { 
                $icon = "<i class='fas fa-retweet notification-icon retweet'></i>";
                $msg = "Retweeted Your Tweet";
              } else if ($notify->type == 'qoute') { 
                $icon = "<i class='fas fa-retweet notification-icon quote'></i>";
                $msg = "Quoted Your Tweet";
              } else if ($notify->type == 'comment') { 
                $icon = "<i class='far fa-comment notification-icon comment'></i>";
                $msg = "Commented on your Tweet";
              } else if ($notify->type == 'reply') { 
                $icon = "<i class='far fa-comment notification-icon reply'></i>";
                $msg = "Replied to your comment";
              } else if ($notify->type == 'follow') { 
                $icon = "<i class='fas fa-user notification-icon follow'></i>";
                $msg = "Followed you";
              } else if ($notify->type == 'mention') { 
                $icon = "<i class='fas fa-at notification-icon mention'></i>";
                $msg = "Mentioned you in a Tweet";
              } else if ($notify->type == 'flag') { 
                $icon = "<i class='fas fa-flag notification-icon flag'></i>";
                $msg = "Flagged your tweet" . ($notify->reason ? ": " . htmlspecialchars($notify->reason) : "");
              } else {
                $icon = "<i class='fas fa-bell notification-icon default'></i>";
                $msg = "Sent you a notification";
              }
            ?>
            
            <div class="notification-item">
              <a href="<?php echo ($notify->type == 'follow' || $notify->type == 'flag') ? $user->username : 'status/' . $notify->target; ?>" class="notification-link"></a>
              
              <div class="notification-content">
                <div class="notification-icon-container">
                  <?php echo $icon; ?>
                </div>
                
                <div class="notification-details">
                  <div class="user-avatar">
                    <a href="<?php echo $user->username; ?>">
                      <img src="assets/images/users/<?php echo $user->img ?>" alt="<?php echo $user->name; ?>" class="avatar-img">
                    </a>
                  </div>
                  
                  <div class="notification-text">
                    <a href="<?php echo $user->username; ?>" class="user-name">
                      <?php echo $user->name; ?>
                    </a>
                    <span class="notification-message"><?php echo $msg; ?></span>
                    <span class="notification-time"><?php echo $timeAgo; ?></span>
                  </div>
                </div>
              </div>
            </div>
            <?php } ?>
            
            <?php if (empty($notofication)) { ?>
              <div class="no-notifications">
                <p>No notifications yet</p>
              </div>
            <?php } ?>
          </div>
        </div>

        <div class="sidebar-right">
          <div class="search-container">
            <div class="input-group">
              <i class="fas fa-search search-icon"></i>
              <input type="text" class="form-control search-input" placeholder="Search ">
              <div class="search-result"></div>
            </div>
          </div>

          <div class="who-to-follow">
            <h3 class="follow-title">Who to follow</h3>
            
            <?php foreach($who_users as $user) { 
              $user_follow = Follow::isUserFollow($user_id, $user->id);
            ?>
            <div class="follow-user">
              <a href="<?php echo $user->username; ?>" class="user-avatar-link">
                <img src="assets/images/users/<?php echo $user->img; ?>" alt="<?php echo $user->name; ?>" class="avatar-img">
              </a>
              
              <div class="user-info">
                <a href="<?php echo $user->username; ?>" class="user-name">
                  <strong><?php echo $user->name; ?></strong>
                </a>
                <p class="user-handle">
                  @<?php echo $user->username; ?>
                  <?php if (Follow::FollowsYou($user->id, $user_id)) { ?>
                    <span class="follows-you">Follows you</span>
                  <?php } ?>
                </p>
              </div>
              
              <div class="follow-action">
                <button class="follow-btn <?php echo $user_follow ? 'following' : 'follow'; ?>" 
                        data-follow="<?php echo $user->id; ?>"
                        data-user="<?php echo $user_id; ?>"
                        data-profile="<?php echo $user_id; ?>">
                  <?php echo $user_follow ? 'Following' : 'Follow'; ?>
                </button>
              </div>
            </div>
            <?php } ?>
          </div>
        </div>
      </div>
</div>

<script src="assets/js/search.js"></script>
<script src="assets/js/photo.js"></script>
<script src="assets/js/follow.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/users.js?v=<?php echo time(); ?>"></script>
<script type="text/javascript" src="assets/js/hashtag.js"></script>
<script type="text/javascript" src="assets/js/like.js"></script>
<script type="text/javascript" src="assets/js/comment.js?v=<?php echo time(); ?>"></script>
<script type="text/javascript" src="assets/js/retweet.js?v=<?php echo time(); ?>"></script>
<script src="https://kit.fontawesome.com/38e12cc51b.js" crossorigin="anonymous"></script>
<script src="assets/js/jquery-3.5.1.min.js"></script>
<script src="assets/js/popper.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>

<style>
* {
  box-sizing: border-box;
}

#mine {
  display: flex;
  min-height: 100vh;
}

.main-content {
  display: flex;
  flex: 1;
  margin-left: 250px;
}

.notification-section {
  flex: 1;
  max-width: 600px;
  border-left: 1px solid #e6ecf0;
  border-right: 1px solid #e6ecf0;
}

.notification-header {
  position: sticky;
  top: 0;
  background: white;
  border-bottom: 1px solid #e6ecf0;
  padding: 15px;
  z-index: 100;
  backdrop-filter: blur(10px);
}

.header-content {
  display: flex;
  align-items: center;
  gap: 20px;
}

.back-arrow {
  color: #1da1f2;
  font-size: 20px;
  text-decoration: none;
}

.page-title {
  font-size: 20px;
  font-weight: 700;
  margin: 0;
  color: #0f1419;
}

.notification-list {
  padding: 0;
}

.notification-item {
  position: relative;
  padding: 15px;
  border-bottom: 1px solid #e6ecf0;
  transition: background-color 0.2s;
}

.notification-item:hover {
  background-color: #f7f9fa;
}

.notification-link {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1;
}

.notification-content {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  position: relative;
  z-index: 2;
}

.notification-icon-container {
  flex-shrink: 0;
}

.notification-icon {
  font-size: 20px;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.notification-icon.like {
  color: #f91880;
  background-color: rgba(249, 24, 128, 0.1);
}

.notification-icon.retweet,
.notification-icon.quote {
  color: #00ba7c;
  background-color: rgba(0, 186, 124, 0.1);
}

.notification-icon.comment,
.notification-icon.reply {
  color: #1da1f2;
  background-color: rgba(29, 161, 242, 0.1);
}

.notification-icon.follow {
  color: #1da1f2;
  background-color: rgba(29, 161, 242, 0.1);
}

.notification-icon.mention {
  color: #ffad1f;
  background-color: rgba(255, 173, 31, 0.1);
}

.notification-icon.flag {
  color: #ffad1f;
  background-color: rgba(255, 173, 31, 0.1);
}

.notification-details {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  flex: 1;
}

.avatar-img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.notification-text {
  flex: 1;
}

.user-name {
  font-weight: 700;
  color: #0f1419;
  text-decoration: none;
  margin-right: 5px;
}

.user-name:hover {
  text-decoration: underline;
}

.notification-message {
  color: #536471;
  display: block;
  margin-top: 2px;
}

.notification-time {
  color: #536471;
  font-size: 14px;
  display: block;
  margin-top: 4px;
}

.no-notifications {
  padding: 40px 20px;
  text-align: center;
  color: #536471;
}

.sidebar-right {
  width: 350px;
  padding: 15px;
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
}

.search-container {
  margin-bottom: 20px;
}

.input-group {
  position: relative;
}

.search-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #536471;
  z-index: 10;
}

.search-input {
  padding-left: 45px;
  border-radius: 20px;
  background-color: #eff3f4;
  border: none;
}

.search-input:focus {
  background-color: white;
  border: 1px solid #1da1f2;
  box-shadow: none;
}

.who-to-follow {
  background-color: #f7f9fa;
  border-radius: 15px;
  padding: 15px;
}

.follow-title {
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 15px;
  color: #0f1419;
}

.follow-user {
  display: flex;
  align-items: center;
  padding: 10px 0;
  gap: 10px;
}

.user-avatar-link {
  flex-shrink: 0;
}

.user-info {
  flex: 1;
  min-width: 0;
}

.user-handle {
  color: #536471;
  margin: 0;
  font-size: 14px;
}

.follows-you {
  color: #536471;
  font-size: 12px;
  background-color: #eff3f4;
  padding: 1px 4px;
  border-radius: 4px;
}

.follow-action {
  flex-shrink: 0;
}

.follow-btn {
  border: 1px solid #0f1419;
  background: #0f1419;
  color: white;
  padding: 6px 16px;
  border-radius: 20px;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
}

.follow-btn.following {
  background: white;
  color: #0f1419;
}

.follow-btn:hover {
  background: #272c30;
}

.follow-btn.following:hover {
  background: #f4212e1a;
  color: #f4212e;
  border-color: #f4212e;
}

@media (max-width: 1024px) {
  .sidebar-right {
    display: none;
  }
  
  .main-content {
    margin-left: 0;
  }
  
  .notification-section {
    max-width: 100%;
  }
}

@media (max-width: 768px) {
  .wrapper-left {
    display: none;
  }
  
  .main-content {
    margin-left: 0;
  }
}
</style>

</html>