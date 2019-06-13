<?php

session_start();

unset($_SESSION['userid']);
unset($_SESSION['username']);
unset($_SESSION['acc_type']);
unset($_SESSION['session_type']);
unset($_SESSION['played_trivia']);

header("Location:index.php?login=false");
