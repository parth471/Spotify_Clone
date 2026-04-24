<?php
$conn = new mysqli("localhost","root","","spotify_db");
if ($conn->connect_error) die("❌ Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8mb4");


// ================= GENRES TABLE =================
$conn->query("CREATE TABLE IF NOT EXISTS genres (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    color1      VARCHAR(20)  NOT NULL DEFAULT '#1DB954',
    color2      VARCHAR(20)  NOT NULL DEFAULT '#121212',
    emoji       VARCHAR(10)  NOT NULL DEFAULT '🎵',
    description TEXT
)");


// ================= SONGS TABLE (UPDATED) =================
$conn->query("CREATE TABLE IF NOT EXISTS songs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    artist      VARCHAR(200) NOT NULL,
    album       VARCHAR(200) NOT NULL DEFAULT '',
    genre_slug  VARCHAR(100) NOT NULL,

    file_path   VARCHAR(255) DEFAULT 'audio/sample.mp3',   -- NEW
    artist_id   INT DEFAULT NULL,                          -- NEW

    views       BIGINT DEFAULT 0,
    trending    TINYINT(1) DEFAULT 0,
    duration    VARCHAR(10) DEFAULT '3:30',
    cover_seed  INT DEFAULT 1,
    year        INT DEFAULT 2024
)");


// ================= SAFE ALTER (FOR EXISTING DB) =================
$conn->query("ALTER TABLE songs ADD COLUMN IF NOT EXISTS album VARCHAR(200) NOT NULL DEFAULT ''");
$conn->query("ALTER TABLE songs ADD COLUMN IF NOT EXISTS file_path VARCHAR(255) DEFAULT 'audio/sample.mp3'");
$conn->query("ALTER TABLE songs ADD COLUMN IF NOT EXISTS artist_id INT DEFAULT NULL");


// ================= INSERT GENRES =================
$genres = [
    ['Pop','pop','#FF6B9D','#2D0B1F','🎤','Catchy melodies'],
    ['Hip-Hop','hiphop','#FF8C00','#1A0A00','🎧','Beats & rap'],
    ['Rock','rock','#E63946','#1A0000','🎸','Guitar energy'],
    ['Electronic','electronic','#4CC9F0','#001B2E','⚡','EDM vibes'],
    ['R&B','rnb','#9B5DE5','#1A0030','🎷','Smooth vibes'],
];

$g = $conn->prepare("INSERT IGNORE INTO genres(name,slug,color1,color2,emoji,description) VALUES(?,?,?,?,?,?)");

foreach ($genres as $r) {
    $g->bind_param("ssssss",$r[0],$r[1],$r[2],$r[3],$r[4],$r[5]);
    $g->execute();
}
$g->close();


// ================= INSERT SAMPLE SONGS =================
$songs = [
 // POP
 ["As It Was","Harry Styles","Harry's House","pop",9800000,1,"3:47",101,2022],
 ["Flowers","Miley Cyrus","Endless Summer Vacation","pop",8700000,1,"3:21",102,2023],
 ["Blinding Lights","The Weeknd","After Hours","pop",15000000,0,"3:20",103,2020],
 ["Anti-Hero","Taylor Swift","Midnights","pop",11000000,1,"3:21",104,2022],
 ["Shape of You","Ed Sheeran","÷","pop",14000000,0,"3:54",105,2017],
 ["Stay","The Kid LAROI","F*CK LOVE 3","pop",9200000,0,"2:21",106,2021],
 ["Levitating","Dua Lipa","Future Nostalgia","pop",10100000,1,"3:23",107,2020],
 ["Bad Guy","Billie Eilish","WHEN WE ALL FALL ASLEEP","pop",12300000,0,"3:14",108,2019],
 ["Heat Waves","Glass Animals","Dreamland","pop",9600000,1,"3:59",109,2020],
 ["Unholy","Sam Smith","Gloria","pop",8400000,1,"2:37",110,2022],
 // HIP-HOP
 ["God's Plan","Drake","Scorpion","hiphop",12000000,1,"3:19",201,2018],
 ["HUMBLE.","Kendrick Lamar","DAMN.","hiphop",10000000,0,"2:57",202,2017],
 ["Rockstar","Post Malone","beerbongs & bentleys","hiphop",9500000,0,"3:38",203,2017],
 ["Sicko Mode","Travis Scott","ASTROWORLD","hiphop",8900000,1,"5:12",204,2018],
 ["Lucid Dreams","Juice WRLD","Goodbye & Good Riddance","hiphop",9100000,0,"3:59",205,2018],
 ["Rich Flex","Drake","Her Loss","hiphop",8800000,1,"3:08",206,2022],
 ["Goosebumps","Travis Scott","Birds in the Trap","hiphop",9800000,1,"4:04",207,2016],
 ["Essence","Wizkid","Made in Lagos","hiphop",7600000,1,"4:13",208,2020],
 ["Rap God","Eminem","The Marshall Mathers LP2","hiphop",8200000,0,"6:04",209,2013],
 ["Big Rings","Drake","What a Time to Be Alive","hiphop",7400000,0,"3:21",210,2015],
 // ROCK
 ["Bohemian Rhapsody","Queen","A Night at the Opera","rock",18000000,0,"5:55",301,1975],
 ["Smells Like Teen Spirit","Nirvana","Nevermind","rock",14000000,0,"5:01",302,1991],
 ["Hotel California","Eagles","Hotel California","rock",13000000,0,"6:30",303,1977],
 ["Master of Puppets","Metallica","Master of Puppets","rock",10000000,1,"8:35",304,1986],
 ["Seven Nation Army","The White Stripes","Elephant","rock",9000000,0,"3:52",305,2003],
 ["Mr. Brightside","The Killers","Hot Fuss","rock",8500000,1,"3:43",306,2003],
 ["Stairway to Heaven","Led Zeppelin","Led Zeppelin IV","rock",11000000,0,"8:02",307,1971],
 ["Back in Black","AC/DC","Back in Black","rock",9700000,0,"4:15",308,1980],
 ["Sweet Child O' Mine","Guns N' Roses","Appetite for Destruction","rock",10500000,0,"5:56",309,1987],
 ["Eye of the Tiger","Survivor","Eye of the Tiger","rock",8900000,1,"4:05",310,1982],
 // ELECTRONIC
 ["Levels","Avicii","True","electronic",11000000,0,"3:18",401,2011],
 ["Animals","Martin Garrix","Gold Skies EP","electronic",9800000,0,"4:02",402,2013],
 ["Titanium","David Guetta","Nothing but the Beat","electronic",10500000,0,"4:05",403,2011],
 ["Wake Me Up","Avicii","True","electronic",12000000,1,"4:07",404,2013],
 ["Lean On","Major Lazer","Peace is the Mission","electronic",13000000,0,"2:57",405,2015],
 ["Clarity","Zedd","Clarity","electronic",8600000,0,"4:00",406,2012],
 ["Don't You Worry Child","Swedish House Mafia","Until Now","electronic",9200000,1,"3:43",407,2012],
 ["Ghost","Justin Bieber","Justice","electronic",7800000,1,"2:33",408,2021],
 ["Beautiful Now","Zedd","True Colors","electronic",8100000,1,"3:37",409,2015],
 ["Greyhound","Swedish House Mafia","Until One","electronic",7400000,0,"5:44",410,2012],
 // R&B
 ["Leave the Door Open","Silk Sonic","An Evening with Silk Sonic","rnb",9200000,0,"4:02",501,2021],
 ["Peaches","Justin Bieber","Justice","rnb",8100000,1,"3:18",502,2021],
 ["Good Days","SZA","Good Days","rnb",7200000,0,"4:39",503,2020],
 ["Watermelon Sugar","Harry Styles","Fine Line","rnb",10000000,0,"2:54",504,2020],
 ["Best Part","Daniel Caesar","Freudian","rnb",8300000,1,"3:30",505,2017],
 ["Mood","24kGoldn","El Dorado","rnb",8900000,1,"2:21",506,2020],
 ["Location","Khalid","American Teen","rnb",7600000,0,"3:52",507,2017],
 ["Sweet","Brent Faiyaz","Wasteland","rnb",6800000,1,"3:27",508,2022],
 ["Fair Trade","Drake","Certified Lover Boy","rnb",7100000,1,"5:11",509,2021],
 ["Essence","Wizkid","Made in Lagos","rnb",8500000,1,"3:49",510,2020],
 // JAZZ
 ["So What","Miles Davis","Kind of Blue","jazz",4200000,0,"9:22",601,1959],
 ["Take Five","Dave Brubeck Quartet","Time Out","jazz",5100000,0,"5:24",602,1959],
 ["Fly Me to the Moon","Frank Sinatra","It Might as Well Be Swing","jazz",6700000,1,"2:28",603,1964],
 ["Round Midnight","Thelonious Monk","Genius of Modern Music","jazz",2900000,0,"5:58",604,1947],
 ["My Favorite Things","John Coltrane","My Favorite Things","jazz",3500000,0,"13:41",605,1961],
 ["Autumn Leaves","Chet Baker","Chet Baker Sings","jazz",3800000,1,"4:08",606,1954],
 ["Blue in Green","Miles Davis","Kind of Blue","jazz",3900000,1,"5:37",607,1959],
 ["Summertime","Miles Davis","Porgy and Bess","jazz",4400000,0,"3:50",608,1959],
 ["A Love Supreme","John Coltrane","A Love Supreme","jazz",3100000,0,"32:00",609,1965],
 ["Body and Soul","Coleman Hawkins","Body and Soul","jazz",2700000,0,"3:16",610,1939],
 // CLASSICAL
 ["Moonlight Sonata","Beethoven","Piano Sonatas","classical",7800000,0,"15:00",701,1801],
 ["Canon in D","Pachelbel","Classical Essentials","classical",9200000,1,"4:58",702,1680],
 ["Four Seasons - Spring","Vivaldi","The Four Seasons","classical",6500000,0,"10:00",703,1725],
 ["Symphony No.9","Beethoven","Beethoven: Symphonies","classical",8100000,1,"1:04:00",704,1824],
 ["Clair de Lune","Debussy","Suite bergamasque","classical",7400000,0,"5:00",705,1905],
 ["Eine Kleine Nachtmusik","Mozart","Serenades","classical",6900000,0,"20:00",706,1787],
 ["Ode to Joy","Beethoven","Symphony No.9","classical",8900000,1,"3:45",707,1824],
 ["Gymnopédie No.1","Erik Satie","Trois Gymnopédies","classical",7200000,0,"3:07",708,1888],
 ["The Nutcracker Suite","Tchaikovsky","The Nutcracker","classical",5800000,1,"22:00",709,1892],
 ["Ride of the Valkyries","Wagner","Die Walküre","classical",5500000,0,"9:00",710,1870],
 // LATIN
 ["Despacito","Luis Fonsi","Vida","latin",18000000,0,"3:47",801,2017],
 ["Taki Taki","DJ Snake","Carte Blanche","latin",9500000,0,"3:33",802,2018],
 ["Con Calma","Daddy Yankee","Con Calma","latin",8700000,1,"3:16",803,2019],
 ["Hawái","Maluma","Papi Juancho","latin",7900000,1,"3:25",804,2020],
 ["BZRP Session 53","Bizarrap x Shakira","Music Sessions","latin",10200000,1,"3:43",805,2023],
 ["Dákiti","Bad Bunny","El Último Tour del Mundo","latin",9100000,1,"2:44",806,2020],
 ["Pepas","Farruko","La 167","latin",7700000,0,"3:18",807,2021],
 ["Pa Ti","J Balvin","Colores","latin",7300000,1,"3:22",808,2020],
 ["Tiktak","Rosalía","Motomami","latin",6800000,0,"2:55",809,2022],
 ["Wapo Traketero","Daddy Yankee","Barrio Fino","latin",6200000,0,"3:11",810,2004],
 // KPOP
 ["Dynamite","BTS","BE","kpop",11000000,1,"3:19",901,2020],
 ["Butter","BTS","Butter","kpop",10500000,0,"2:44",902,2021],
 ["LALISA","LISA","LALISA","kpop",8900000,1,"3:08",903,2021],
 ["Fearless","Le Sserafim","FEARLESS","kpop",7400000,0,"2:57",904,2022],
 ["Pink Venom","BLACKPINK","BORN PINK","kpop",9600000,1,"3:06",905,2022],
 ["Cupid","FIFTY FIFTY","The Fifty","kpop",8200000,1,"2:57",906,2023],
 ["Next Level","aespa","Next Level","kpop",7800000,1,"3:51",907,2021],
 ["TOMBOY","(G)I-DLE","I NEVER DIE","kpop",8500000,1,"3:07",908,2022],
 ["After LIKE","IVE","After LIKE","kpop",7900000,1,"3:12",909,2022],
 ["Savage","aespa","Savage","kpop",7200000,0,"3:54",910,2021],
 // LOFI
 ["Chill Study Beats","ChilledCow","Lo-Fi Hip Hop","lofi",6200000,0,"3:12",1001,2020],
 ["Rainy Day","Idealism","Daydream","lofi",5800000,1,"2:48",1002,2021],
 ["Coffee Shop","Jinsang","Solitude","lofi",7100000,0,"3:22",1003,2019],
 ["Snowfall","Øneheart","Snowfall EP","lofi",8300000,1,"3:05",1004,2022],
 ["Sleepy Fish","Potsu","for you","lofi",5400000,0,"2:55",1005,2020],
 ["Beautiful","Ikson","Horizon","lofi",6600000,1,"3:18",1006,2021],
 ["Porcelain","Moods","Slow Radio","lofi",5100000,0,"3:44",1007,2022],
 ["Evening Tea","Saib","Orange Soda","lofi",4900000,1,"2:59",1008,2021],
 ["Serenade","L'indécis","Cosy","lofi",5700000,0,"3:23",1009,2020],
 ["midnight run","Philanthrope","midnight run","lofi",6300000,1,"3:11",1010,2022],
];

$s = $conn->prepare("
INSERT IGNORE INTO songs
(title,artist,album,genre_slug,views,trending,duration,cover_seed,year)
VALUES (?,?,?,?,?,?,?,?,?)
");

foreach ($songs as $r) {
    $s->bind_param("ssssiisii",$r[0],$r[1],$r[2],$r[3],$r[4],$r[5],$r[6],$r[7],$r[8]);
    $s->execute();
}
$s->close();

$conn->close();
?>


<!DOCTYPE html>
<html>
<body style="background:#111;color:#1db954;font-family:sans-serif;text-align:center;padding:40px;">

<h2>✅ Setup Complete</h2>
<p style="color:white;">Database updated with new columns + sample data</p>

<a href="genre.php" style="background:#1db954;color:black;padding:12px 30px;border-radius:20px;text-decoration:none;">
Go to Genres →
</a>

<p style="color:#555;margin-top:20px;">Delete this file after use</p>

</body>
</html>