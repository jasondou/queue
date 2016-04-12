<?php
require_once "../../config.php";

// The Tsugi PHP API Documentation is available at:
// http://do1.dr-chuck.com/tsugi/phpdoc/namespaces/Tsugi.html

use \Tsugi\Core\Settings;
use \Tsugi\Core\LTIX;

// No parameter means we require CONTEXT, USER, and LINK
$LTI = LTIX::requireData();

// Model
$p = $CFG->dbprefix;
$old_code = Settings::linkGet('code', '');

if(isset($_POST['join'])){
  $username = $_POST["username"];
  $user_location = $_POST["userlocation"];

  $results = $PDOX->allRowsDie("SELECT * FROM {$p}queue
        WHERE user_id = :ID",
		array(':ID' => $USER->id
        )
	);
  echo(sizeof($results));
    // var_dump($rows);
  if(sizeof($results) > 0){
    $_SESSION['success'] = 'You are alreadying waiting';
  }else{
    if(strlen($username) == 0 || strlen($user_location) == 0){
      // NEED to change color here
      $_SESSION['success'] = 'Please enter valid info';
    }else{
      $PDOX->queryDie("INSERT INTO {$p}queue
          (link_id, user_id, name, location, waiting_at)
          VALUES ( :LI, :UI, :NA, :LC, NOW())
          ON DUPLICATE KEY UPDATE  name = :NA, location = :LC",
          array(
            ':LI' => $LINK->id,
            ':UI' => $USER->id,
            ':NA' => $username,
            ':LC' => $user_location
            ));

        $_SESSION['success'] = 'Waiting :)';
    }
  }
    header('Location: '.addSession('index.php') ) ;

}

if(isset($_POST['update'])){
  header('Location: '.addSession('index.php') ) ;
}

if(isset($_POST["remove"])){
  $PDOX->queryDie("DELETE FROM {$p}queue ORDER BY waiting_at ASC LIMIT 1");
  header('Location: '.addSession('index.php') ) ;
}

// View
$OUTPUT->header();
$OUTPUT->bodyStart();
$OUTPUT->flashMessages();
// $OUTPUT->welcomeUserCourse();

// We could use the settings form - but we will keep this simple
echo('<form method="post">');
echo(_("Name:")."\n");

if ( $USER->instructor ) {

    echo(' <input type="text" name="username" value="'.htmlent_utf8($old_code).'"> ');
} else {
    echo(' <input type="text" name="username" value=""> ');
}
echo('<br><br>');
echo(_("Location:")."\t"."\n");

if ( $USER->instructor ) {
    echo(' <input type="text" name="userlocation" value="'.htmlent_utf8($old_code).'"> ');
    echo('<br><br>');
    echo('<input type="submit" class="btn btn-info" name="join" value="'._('Join Queue').'"><br/>');
    echo('<br><br>');
    echo('<input type="submit" class="btn btn-warning" name="update" value="'._('Update Queue').'"> ');
    echo('<input type="submit" class="btn btn-warning" name="remove" value="'._('Romove First').'"><br/>');
} else {
    echo(' <input type="text" name="userlocation" value=""> ');
    echo('<br><br>');
    echo('<input type="submit" class="btn btn-info" name="join" value="'._('Join Queue').'"><br/>');
}
echo("\n</form>\n");
echo('<br><br>');
if ( $USER->instructor ) {
    $rows = $PDOX->allRowsDie("SELECT name,location, waiting_at FROM {$p}queue
             ORDER BY waiting_at ASC");
    echo('<table border="1">'."\n");
    echo("<tr><th>"._("User")."</th><th>"._("Location")."</th><th>"._("Time")."</th></tr>\n");
    foreach ( $rows as $row ) {
        echo "<tr><td>";
        echo($row['name']);
        echo("</td><td>");
        echo($row['location']);
        echo("</td><td>");
        echo(htmlent_utf8($row['waiting_at']));
        echo("</td></tr>\n");
    }
    echo("</table>\n");
}

$OUTPUT->footer();
