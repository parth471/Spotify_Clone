<form action="upload_song.php" method="POST">

<input type="hidden" name="genre_slug" value="<?= $_GET['genre'] ?>">

<input type="text" name="title" placeholder="Song Title" required><br><br>

<input type="text" name="artist" placeholder="Artist Name" required><br><br>

<button type="submit">Add Song</button>

</form>