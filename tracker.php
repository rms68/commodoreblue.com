<?php
// ---------------------------------------------------------
// Simulate the environment
// ---------------------------------------------------------

// List of videos (as provided). In a real scenario, you'd scan the directory.
$videos = [
"005.MP4",
"10 YARD FIGHT (NES).MP4",
"10 YARD FIGHT.AVI",
"1000 MIGLIA 1.AVI",
"1000 MIGLIA 2.AVI",
"1941 COUNTER ATTACK_SGX.MP4",
"1942 (NES).MP4",
"1942 - 1 1942.AVI",
"1942 - 2 1943 THE BATTLE OF MIDWAY.AVI",
"1942 - 3 1943 KAI.AVI",
"1942 - 4 1941 COUNTER ATTACK.AVI",
"1942 - 5 19XX THE WAR AGAINST DESTINY.AVI",
"1942 - 6 1944 THE LOOP MASTER.AVI",
"1943 - THE BATTLE OF MIDWAY (NES).MP4",
"1943 KAI_PCE.MP4",
"1945K III.AVI",
"2 ON 2 OPEN ICE CHALLENGE.AVI",
"2 ON 2 OPEN ICE CHALLENGE_4P.AVI",
"20-EM-1_SEGAMASTERSYSTEM.MP4",
"2020 SUPER BASEBALL.AVI",
"20221201_135933.MP4",
"20221201_140311.MP4",
"20221201_143949.MP4",
"3 COUNT BOUT.AVI",
"3 NINJAS KICK BACK (SNES).MP4",
"3 NINJAS KICK BACK_GEN.MP4",
"3 ON 3 DUNK MADNESS (ARCADE).MP4",
"3-D WORLDRUNNER (NES).MP4",
"4-D WARRIORS.MP4",
"40 LOVE.MP4",
"6 PAK_GEN.MP4",
"688 ATTACK SUB_GEN.MP4",
"720 DEGREES (NES).MP4",
"720 DEGREES.AVI",
"8 EYES (NES).MP4",
"80S ARCADE ATTRACT MODE-SMALL.MP4",
"9-BALL SHOOTOUT 1.MP4",
"9-BALL SHOOTOUT 2 - CHAMPIONSHIP.MP4",
"96 FLAG RALLY.MP4",
"A.B. COP.MP4",
"A.S.P. - AIR STRIKE PATROL (SNES).MP4",
"AAAHH!!! REAL MONSTERS (SNES).MP4",
"AAAHH!!! REAL MONSTERS_GEN.MP4",
"ABADOX - THE DEADLY INNER WAR (NES).MP4",
"ABC MONDAY NIGHT FOOTBALL (SNES).MP4",
"ABSCAM (ARCADE).MP4",
"ACE OF ACES_SEGAMASTERSYSTEM.MP4",
"ACME ANIMATION FACTORY (SNES).MP4",
"ACROBAT MISSION.MP4",
"ACROBATIC DOG-FIGHT.MP4",
"ACT-FANCER CYBERNETICK HYPER WEAPON (ARCADE).AVI",
"ACTION 52 (NES).MP4",
"ACTION 52 _GEN.MP4",
"ACTION FIGHTER.MP4",
"ACTION FIGHTER_SEGAMASTERSYSTEM.MP4",
"ACTION HOLLYWOOD (ARCADE).MP4",
"ACTRAISER (SNES).MP4",
"ACTRAISER 2 (SNES).MP4",
"ADDAMS FAMILY 1 (NES).MP4",
"ADDAMS FAMILY 1_GEN.MP4",
"ADDAMS FAMILY 2 - PUGSLEY'S SCAVENGER HUNT (NES).MP4",
"ADDAMS FAMILY VALUES_GEN.MP4",
"ADVANCED DUNGEONS & DRAGONS - DRAGONSTRIKE (NES).MP4",
"ADVANCED DUNGEONS & DRAGONS - HEROES OF THE LANCE (NES).MP4",
"ADVANCED DUNGEONS & DRAGONS - HILLSFAR (NES).MP4",
"ADVANCED DUNGEONS & DRAGONS - POOL OF RADIANCE (NES).MP4",
"ADVENTURE ISLAND 1 (NES).MP4",
"ADVENTURE ISLAND 2 (NES).MP4",
"ADVENTURE ISLAND 3 (NES).MP4",
"ADVENTURE ISLAND 4 - SUPER (SNES).MP4",
"ADVENTURE ISLAND 5 - SUPER II (SNES).MP4",
"ADVENTURES IN THE MAGIC KINGDOM (NES).MP4",
"ADVENTURES OF BAYOU BILLY THE (NES).MP4",
"ADVENTURES OF DINO RIKI (NES).MP4",
"ADVENTURES OF GILLIGAN'S ISLAND THE (NES).MP4",
"ADVENTURES OF LOLO (NES).MP4",
"ADVENTURES OF LOLO 2 (NES).MP4",
"ADVENTURES OF LOLO 3 (NES).MP4",
"ADVENTURES OF MIGHTY MAX_GEN.MP4",
"ADVENTURES OF RAD GRAVITY THE (NES).MP4",
"ADVENTURES OF ROCKY AND BULLWINKLE AND FRIENDS THE (NES).MP4",
"ADVENTURES OF ROCKY AND BULLWINKLE AND FRIENDS_GEN.MP4",
"ADVENTURES OF TOM SAWYER (NES).MP4",
"ADVENTURES OF YOGI BEAR (SNES).MP4",
"AERIAL ASSAULT_SEGAMASTERSYSTEM.MP4",
"AERO BLASTERS_TG16.MP4",
"AERO FIGHTERS (SNES).MP4",
"AERO FIGHTERS 1.AVI",
"AERO FIGHTERS 2.AVI",
"AERO FIGHTERS 3 - SONIC WINGS 3.AVI",
"AERO THE ACRO BAT 1_GEN.MP4",
"AERO THE ACRO BAT 2_GEN.MP4",
"AERO THE ACRO-BAT (SNES).MP4",
"AERO THE ACRO-BAT 2 (SNES).MP4",
"AEROBIZ (SNES).MP4",
"AEROBIZ SUPERSONIC (SNES).MP4",
"AEROBIZ SUPERSONIC_GEN.MP4",
"AEROBIZ_GEN.MP4",
"AFTER BURNER (NES).MP4",
"AFTER BURNER 1.MP4",
"AFTER BURNER 2.AVI",
"AFTER BURNER II_GEN.MP4",
"AFTER BURNER II_PCE.MP4",
"AFTER BURNER_SEGAMASTERSYSTEM.MP4",
"AGGRESSORS OF DARK KOMBAT.AVI",
"AGRESS - MISSLE DAISENRYAKU (ENGLISH).MP4",
"AIR ATTACK.MP4",
"AIR BUSTER.AVI",
"AIR BUSTER_GEN.MP4",
"AIR CAVALRY (SNES).MP4",
"AIR DIVER_GEN.MP4",
"AIR DUEL.AVI",
"ZZ STREET - A DETECTIVE STORY (ARCADE).AVI"
];

// Get parameters
$page = isset($_GET['page']) ? $_GET['page'] : 'review';
$video_select = isset($_GET['video']) ? $_GET['video'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$playlist = isset($_COOKIE['playlist']) ? explode(',', $_COOKIE['playlist']) : [];

// Handle actions: add to playlist
if ($action === 'add_to_playlist' && $video_select !== '') {
    if (!in_array($video_select, $playlist)) {
        $playlist[] = $video_select;
        setcookie('playlist', implode(',', $playlist), time()+3600);
    }
}

// Simple categorization by first letter/digit
$categories = [];
foreach ($videos as $v) {
    $first_char = strtoupper($v[0]);
    if (!isset($categories[$first_char])) {
        $categories[$first_char] = [];
    }
    $categories[$first_char][] = $v;
}

// ---------------------------------------------------------
// HTML and CSS: LCARS Style
// ---------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<title>LCARS VFX Supervisor Review Interface</title>
<style>
body {
    margin:0; 
    padding:0; 
    font-family: Arial, sans-serif;
    background:#000;
    color:#fff;
    overflow-x:hidden;
}
header {
    background: #FF9966; /* LCARS Orange */
    padding: 10px; 
    font-size:20px;
}
.lcars-bar {
    background:#CC6633; 
    padding:5px 10px;
    display:inline-block; 
    margin-right:10px;
    border-radius:10px;
}
nav a {
    color:#fff;
    text-decoration:none;
    margin-right:15px;
}
.container {
    display:flex;
    min-height:100vh;
}
aside {
    width:250px;
    background:#330000; 
    padding:20px;
}
main {
    flex:1; 
    padding:20px;
    background:#003333;
    overflow-y:auto;
}
.section-title {
    border-bottom:2px solid #FF9966; 
    padding-bottom:5px; 
    margin-bottom:10px;
    font-weight:bold;
}
video {
    display:block; 
    width:80%; 
    max-width:800px; 
    margin-bottom:20px; 
    border:5px solid #FF9966; 
    border-radius:10px;
}
.playlist-item, .video-item {
    margin:5px 0;
    padding:5px;
    background:#002222;
    border-radius:5px;
}
.playlist-item a, .video-item a {
    color:#66FFFF;
}
.video-item a:hover, .playlist-item a:hover {
    color:#FF9966;
}

.ai-note {
    background:#112222;
    border:2px solid #66FFFF;
    border-radius:5px;
    padding:10px;
    margin-top:20px;
    font-size:14px;
}
.lcars-corner {
    background:#FF9966;
    border-radius: 0 50px 50px 0;
    width:20px;
    height:50px;
    margin-bottom:5px;
}
.category-block {
    margin-bottom:20px;
}
.category-block h3 {
    background:#003300;
    padding:5px;
    border-radius:5px;
    margin:0;
}
</style>
</head>
<body>

<header>
    <span class="lcars-bar">LCARS</span>
    VFX Supervisor Interactive Review System
    <nav>
        <a href="?page=review">Review</a>
        <a href="?page=playlist">Playlist</a>
        <a href="?page=browse">Browse</a>
    </nav>
</header>

<div class="container">
    <aside>
        <div class="lcars-corner"></div>
        <div class="lcars-corner"></div>
        <h3>Navigation</h3>
        <ul style="list-style:none; padding:0;">
            <li><a href="?page=review">Review Panel</a></li>
            <li><a href="?page=browse">Browse Videos</a></li>
            <li><a href="?page=playlist">My Playlist</a></li>
        </ul>
        <p style="margin-top:20px;">As a supervisor, use this LCARS-inspired system to review cuts, assign tasks, and plan schedules.</p>
    </aside>
    <main>
        <?php if ($page === 'browse'): ?>
            <h2 class="section-title">Browse Videos</h2>
            <p>Videos located in <strong>movies/</strong> directory. Select a category to view files.</p>
            <?php foreach ($categories as $cat => $vids): ?>
                <div class="category-block">
                    <h3><?php echo htmlspecialchars($cat); ?></h3>
                    <?php foreach ($vids as $file): ?>
                        <div class="video-item">
                            <?php echo htmlspecialchars($file); ?><br>
                            <a href="?page=review&video=<?php echo urlencode($file); ?>">Review</a> | 
                            <a href="?page=browse&action=add_to_playlist&video=<?php echo urlencode($file); ?>">Add to Playlist</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

        <?php elseif ($page === 'playlist'): ?>
            <h2 class="section-title">My Playlist</h2>
            <?php if (empty($playlist)): ?>
                <p>No videos in your playlist.</p>
            <?php else: ?>
                <ul style="list-style:none; padding:0;">
                    <?php foreach ($playlist as $pvid): ?>
                        <li class="playlist-item">
                            <?php echo htmlspecialchars($pvid); ?><br>
                            <a href="?page=review&video=<?php echo urlencode($pvid); ?>">View</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        
        <?php else: // default page: review ?>
            <h2 class="section-title">Review Panel</h2>
            <?php if ($video_select && in_array($video_select, $videos)): ?>
                <p><strong>Now Reviewing:</strong> <?php echo htmlspecialchars($video_select); ?></p>
                <video controls>
                    <source src="movies/<?php echo urlencode($video_select); ?>" type="video/<?php echo (stripos($video_select, '.mp4')!==false?'mp4':'avi'); ?>">
                    Your browser does not support the video tag.
                </video>
                <p>
                    <a href="?page=review&action=add_to_playlist&video=<?php echo urlencode($video_select); ?>">Add to Playlist</a> | 
                    <a href="?page=browse">Browse More</a>
                </p>
                <div class="ai-note">
                    <strong>AI Integration:</strong><br>
                    As a supervisor, AI can automatically analyze the content of this shot, generate QC notes, predict the complexity of upcoming tasks, and suggest which artists or departments need to review it next. It can identify continuity issues, lighting mismatches, or potential compositing errors even before human review.
                </div>
            <?php else: ?>
                <p>Select a video to review from <a href="?page=browse">Browse</a> or from your <a href="?page=playlist">Playlist</a>.</p>
                <div class="ai-note">
                    <strong>AI Integration:</strong><br>
                    On this main review panel, AI can offer personalized recommendations on which shots need your attention first based on delivery deadlines, complexity scoring, and recent feedback from artists and clients. It can prioritize your review queue and help schedule sessions efficiently.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</div>

</body>
</html>
