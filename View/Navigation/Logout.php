<?php

// View/Navigation/Logout.php
session_start();
session_unset();
session_destroy();
header("Location: Index.php?page=Connexion");
exit();