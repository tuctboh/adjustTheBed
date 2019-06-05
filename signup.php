<!DOCTYPE HTML>
<html>
<head>
<title>Alexa Adjust The Bed sign-up</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body id="body-color">
<?php
require_once "adjustTheBed-main.php";

if ( isset($_SERVER['QUERY_STRING'])) {
  $passstring=$_SERVER['QUERY_STRING'];
  parse_str($passstring,$output);
}

if (! isset($output['access']))
{
  echo  'Please use the full url provided by the \'Adjust The Bed\' application';
} else {
  $indb=accessdb("signup","get",$output['access']);
  if (isset($indb)){
    if (isset ($_POST['email']) && isset($_POST['pass']) && isset($_POST['cpass'])) {
      if ($_POST['pass'] != $_POST['cpass']) {
        print 'Passwords did not match. Please re-access the URL';
      } else {
        accessdb("signup","delete",$output['access']);
        accessdb("signup","delete",$indb);
        accessdb("password","delete",$indb);
        accessdb("password","put",$indb,$_POST['email']."|".$_POST['pass']);
        print "You've been added!";
      }
    } elseif (isset ($_POST['proxyurl'])) {
        accessdb("signup","delete",$output['access']);
        accessdb("signup","delete",$indb);
        accessdb("password","put",$indb,$_POST['proxyurl']);
        print "You've been added!";
    } else {
      echo '<div id="Sign-Up">
<fieldset style="width:30%"><legend>Registration Form</legend>
<table border="0">
<tr>
<form method="POST" action="signup.php?access=';
      print $output['access'];
      echo '">
<tr>
<td>Access code :</td><td>';
      print $output['access'];
      echo '</td>
</tr>
<tr>
<td>SLEEP NUMBER(r) Email</td><td> <input type="text" name="email"></td>
</tr>
<tr>
<td>SLEEP NUMBER(r) Password</td><td> <input type="password" name="pass"></td>
</tr>
<tr>
<td>Confirm Password </td><td><input type="password" name="cpass"></td>
</tr>
<tr>
<td>OR</td><td>OR</td>
</tr>
</tr>
<td>AdjustTheBedPassProxy URL</td><td><input type="text" name="proxyurl"></td>
<tr>
<td><input id="button" type="submit" name="submit" value="Sign-Up"></td>
</tr>
</form>
</table>
</fieldset>
</div>';
    }
  } else {
    print $output['access'];
    print ' is an invalid access code, please use the full url provided by the `Adjust The Bed` Alexa skill';
  }
}
?>
</body>
</html>
