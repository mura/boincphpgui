<?php
// Allow the user to change the behavior of the gui.

// $reverse_messages will allow the user to specify the display order of
// the messages in the message tab.  Setting it equal to 1 will cause 
// the message to be listed with the newest message at the top and the
// oldest at the bottom.  Setting it to 0 will do the opposite.

$reverse_messages = 0;

// This is the port the core client listens to for the rpc connection.

$port = "31416";

// This is the tab to display if it is not specified in the URL

$default_tab = "work";

// Default machine name to query.  This is prettier than locahost or
// 127.0.0.1

$default_address = "oak";

// Default number of seconds to refresh the display

$default_refresh = "60"

?>
