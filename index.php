<?php

   include_once("gui_rpc_client.php");
   include_once("utils.php");
   include_once("config.php");

   if (!$_GET["index"])
   {
      $index = $default_tab;
   }
   else
   {
      $index = $_GET["index"];
   }

   if (!$_GET["address"])
   {
      $address = $default_address;
   }
   else
   {
      $address = $_GET["address"];
   }

   if (!$_GET["refresh"])
   {
      $refresh = $default_refresh;
   }
   else
   {
      $refresh = $_GET["refresh"];
   }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Boinc GUI - <?php echo $address; ?></title>
<?php
     if ($index!="tools" && $index!="help") {
        echo "    <meta http-equiv=\"refresh\" content=\"".$refresh."\">\n";
     }
?>
    <link rel="stylesheet" type="text/css" href="default.css">
  </head>

  <body>
    <?php

            echo "<div id=\"header\">\n";
            echo "  <span class=\"client\">Host ".$address." - </span>\n";
            echo "  <span class=\"status\">";
            $client = new RPC_CLIENT;
            if ($client < 0) {
                echo "no sockets available";
            } else {
                if ($client->connect($address, $port)) {
                    $client_state = $client->get_state();
                    echo "online - Using Core Client ";
                    echo $client_state["client_version"];
                    if ($client_state["panic_mode"] >= 1) {
                      echo "  --  <span class=\"statusEDF\">Panic Mode</span>";
                    } else {
                      echo "  --  Highest Debt";
                    }
//                    if ($client_state["work_fetch"] == 1) {
//                      echo "  --  No New Work Allowed";
//                    } else {
//                      echo "  --  New Work Allowed";
//                    }
                } else {
                    echo "offline";
                }
            }
            echo "</span>\n";
            echo "</div>\n";

            echo "<ul id=\"menu\">\n";
            echo "  <li"; if ($index=="projects") echo " class=\"active\""; echo "><a href=\"".$_SERVER["PHP_SELF"]."?index=projects&amp;address=".$address."\" title=\"Projects\">projects</a></li>\n";
            echo "  <li"; if ($index=="work") echo " class=\"active\""; echo "><a href=\"".$_SERVER["PHP_SELF"]."?index=work&amp;address=".$address."\" title=\"Work\">work</a></li>\n";
            echo "  <li"; if ($index=="transfers") echo " class=\"active\""; echo "><a href=\"".$_SERVER["PHP_SELF"]."?index=transfers&amp;address=".$address."\" title=\"Transfers\">transfers</a></li>\n";
            echo "  <li"; if ($index=="messages") echo " class=\"active\""; echo "><a href=\"".$_SERVER["PHP_SELF"]."?index=messages&amp;address=".$address."\" title=\"Messages\">messages</a></li>\n";
            echo "  <li"; if ($index=="tools") echo " class=\"active\""; echo "><a href=\"".$_SERVER["PHP_SELF"]."?index=tools&amp;address=".$address."\" title=\"Tools\">options</a></li>\n";
            echo "  <li"; if ($index=="help") echo " class=\"active\""; echo "><a href=\"".$_SERVER["PHP_SELF"]."?index=help&amp;address=".$address."\" title=\"Help\">help</a></li>\n";
            echo "</ul>\n";

            echo "<div id=\"content\">\n";
            // fetch data
            switch ($index) {
                case "projects":
                case "work":
//                    $client_state = $client->get_state();
                    break;
                case "transfers":
                    $transfers = $client->get_file_transfers();
                    break;
                case "messages":
                    $msgs = array();
                    $seqno = 0;
                    if (!$reverse_messages) {
                       $msgs = $client->get_messages($seqno);
                    } else {
                       $msgs = array_reverse($client->get_messages($seqno));
                    }
                    break;
                case "tools":
                    if ($_GET["cmd"]) {
                        echo "<div class=\"information\">\n";
                        switch ($_GET["cmd"]) {
                            case "benchmarks":
                                echo "<div class=\"last\">Last action: Running Benchmarks</div>\n";
                                $retval = $client->run_benchmarks();
                                break;
                            case "show_graphics":
                                echo "<div class=\"last\">Last action: Running Benchmarks</div>\n";
                                $retval = $client->show_graphics();
                                break;
                            case "project_update":
                                echo "<div class=\"last\">Last action: Update of project ".urldecode($_GET["url"])."</div>\n";
                                $retval = $client->project_update(urldecode($_GET["url"]));
                                break;
                            case "project_reset":
                                echo "<div class=\"last\">Last action: Reset of project ".urldecode($_GET["url"])."</div>\n";
                                $retval = $client->project_reset(urldecode($_GET["url"]));
                                break;
                            case "project_detach":
                                echo "<div class=\"last\">Last action: Detach from project ".urldecode($_GET["url"])."</div>\n";
                                $retval = $client->project_detach(urldecode($_GET["url"]));
                                break;
                            case "project_attach":
                                echo "<div class=\"last\">Last action: Attach to project ".urldecode($_GET["url"])."</div>\n";
                                $retval = $client->project_attach(urldecode($_GET["url"]), $_GET["authenticator"]);
                                break;
                            case "project_suspend":
                                echo "<div class=\"last\">Last action: Suspend project ".urldecode($_GET["url"])."</div>\n";
                                $retval = $client->project_suspend(urldecode($_GET["url"]));
                                break;
                            case "project_resume":
                                echo "<div class=\"last\">Last action: Resume project ".urldecode($_GET["url"])."</div>\n";
                                $retval = $client->project_resume(urldecode($_GET["url"]));
                                break;
                            case "project_freeze":
                                echo "<div class=\"last\">Last action: Freeze project ".urldecode($_GET["url"])."</div>\n";
                                $retval = $client->project_freeze(urldecode($_GET["url"]));
                                break;
                            case "project_thaw":
                                echo "<div class=\"last\">Last action: Thaw project ".urldecode($_GET["url"])."</div>\n";
                                $retval = $client->project_thaw(urldecode($_GET["url"]));
                                break;
                            case "result_suspend":
                                echo "<div class=\"last\">Last action: Suspend result ".$_GET["result"]."</div>\n";
                                $retval = $client->result_suspend($_GET["result"], $_GET["url"]);
                                break;
                            case "result_resume":
                                echo "<div class=\"last\">Last action: Resume result ".$_GET["result"]."</div>\n";
                                $retval = $client->result_resume($_GET["result"], $_GET["url"]);
                                break;
                            case "result_abort":
                                echo "<div class=\"last\">Last action: Abort result ".$_GET["result"]."</div>\n";
                                $retval = $client->result_abort($_GET["result"], $_GET["url"]);
                                break;
                            case "retry_transfer":
                                echo  "<div class=\"last\">Last action: Retry File Transfer ".$_GET["file"]."</div>\n";
                                $retval = $client->retry_transfer($_GET["file"], $_GET["url"]);
                                break;
                            case "setrunmode":
                                echo "<div class=\"last\">Last action: Setting the run mode</div>\n";
                                $retval = $client->set_run_mode($_GET["mode"]);
                                break;
                            case "setnetmode":
                                echo "<div class=\"last\">Last action: Setting the network mode</div>\n";
                                $retval = $client->set_network_mode($_GET["mode"]);
                                break;
                            case "switched":
                                echo "<div class=\"last\">Last action: Switched to host ".$address."</div>\n";
                                $retval = true;
                                break;
                            case "phpinfo":
                                phpinfo();
                                $retval = true;
                                break;
                        }
                        if ($retval) {
                            echo "<div class=\"result\">Finished: successful</div>\n";
                        } else {
                            echo "<div class=\"result\">Finished: not successful</div>\n";
                        }
                        echo "</div>\n";
                    }
                    break;
            }

            // display data
            switch ($index) {
                case "projects":
                    print_projects($client, $client_state, $address);
                    break;
                case "work":
                    print_work($client, $client_state, $address);
                    break;
                case "transfers":
                    print_transfers($client, $transfers, $address);
                    break;
                case "messages":
                    echo "<table class=\"messages\" summary=\"Status messages\">\n";
//                    echo " <colgroup>\n";
//                    echo "  <col width=\"1*\" />\n";
//                    echo "  <col width=\"1*\" />\n";
//                    echo "  <col width=\"6*\" />\n";
//                    echo " </colgroup>\n";
                    echo " <tr>\n";
                    echo "  <th>Project</th>\n";
                    echo "  <th>Time</th>\n";
                    echo "  <th>Messages</th>\n";
                    echo " </tr>\n";
                    foreach ($msgs as $msg) {
                        switch ($msg["priority"]) {
                            case 1:
                                 echo " <tr>\n";
                                 break;
                            case 2:
                                 echo " <tr class=\"error\">\n";
                                 break;
                            default:
                                 echo " <tr>\n";
                        }
                        echo "  <td>".$msg["project"]."</td>\n";
                        echo "  <td>".date("j M Y G:i:s", $msg["timestamp"])."</td>\n";
                        echo "  <td>".$msg["body"]."</td>\n";
                        echo " </tr>\n";
                    }
                    echo "</table>\n";
                    break;
                case "tools":
                    echo "<ul id=\"tools\">\n";
                    echo "  <li>";
                    echo "    <form action=\"".$_SERVER["PHP_SELF"]."\" method=\"get\" name=\"switch_host\" id=\"switch_host\" target=\"_self\">";
                    echo "      <input type=\"hidden\" name=\"index\" value=\"tools\" />";
                    echo "      <input type=\"hidden\" name=\"cmd\" value=\"switched\" />";
                    echo "      <h1>Switch host</h1><input value=\"".$address."\" name=\"address\" /><input type=\"submit\" value=\"go\" name=\"submit\" />";
                    echo "    </form>";
                    echo "  </li>\n";
                    echo "  <li>";
                    echo "    <form action=\"".$_SERVER["PHP_SELF"]."\" method=\"get\" name=\"switch_net\" id=\"switch_net\" target=\"_self\">";
                    echo "      <input type=\"hidden\" name=\"index\" value=\"tools\" />";
                    echo "      <input type=\"hidden\" name=\"address\" value=\"".$address."\" />";
                    echo "      <input type=\"hidden\" name=\"cmd\" value=\"setnetmode\" />";
                    echo "      <h1>Set network mode</h1>";
                    echo "      <select name=\"mode\"><option value=\"0\">Enable</option><option value=\"2\">Disable</option></select>";
                    echo "      <input type=\"submit\" value=\"go\" name=\"submit\" />";
                    echo "    </form>";
                    echo "  </li>\n";
                    echo "  <li>";
                    echo "    <form action=\"".$_SERVER["PHP_SELF"]."\" method=\"get\" name=\"switch_run\" id=\"switch_run\" target=\"_self\">";
                    echo "      <input type=\"hidden\" name=\"index\" value=\"tools\" />";
                    echo "      <input type=\"hidden\" name=\"address\" value=\"".$address."\" />";
                    echo "      <input type=\"hidden\" name=\"cmd\" value=\"setrunmode\" />";
                    echo "      <h1>Set run mode</h1>";
                    echo "      <select name=\"mode\"><option value=\"0\">Run always</option><option value=\"1\">Run based on preferences</option><option value=\"2\">Suspend</option></select>";
                    echo "      <input type=\"submit\" value=\"go\" name=\"submit\" />";
                    echo "    </form>";
                    echo "  </li>\n";
                    echo "  <li>";
                    echo "    <form action=\"".$_SERVER["PHP_SELF"]."\" method=\"get\" name=\"attach\" id=\"attach\" target=\"_self\">\n";
                    echo "      <input type=\"hidden\" name=\"index\" value=\"tools\" />\n";
                    echo "      <input type=\"hidden\" name=\"address\" value=\"".$address."\" />\n";
                    echo "      <input type=\"hidden\" name=\"cmd\" value=\"project_attach\" />\n";
                    echo "      <h1>Attach to project</h1>\n";
                    echo "      <div class=\"url\">Project URL: <input type=\"text\" value=\"\" name=\"url\" /></div>\n";
                    echo "      <div class=\"authenticator\">Account key: <input type=\"text\" value=\"\" name=\"authenticator\" /></div>\n";
                    echo "      <input type=\"submit\" value=\"Attach\" name=\"submit\" />\n";
                    echo "    </form>\n";
                    echo "  </li>\n";
                    echo "  <li><a href=\"".$_SERVER["PHP_SELF"]."?index=tools&address=".$address."&cmd=benchmarks\" title=\"Run benchmarks\">Run benchmarks</a></li>\n";
                    echo "  <li><a href=\"".$_SERVER["PHP_SELF"]."?index=tools&address=".$address."&cmd=show_graphics\" title=\"Show Graphics\">Show Graphics</a></li>\n";
                    echo "  <li><a href=\"".$_SERVER["PHP_SELF"]."?index=tools&address=".$address."&cmd=phpinfo\" title=\"PHP Info\">PHP Info</a></li>\n";
                  #  echo "  <li><a href=\"#\" title=\"Set proxy settings\">Set proxy settings</a></li>\n";
                    echo "</ul>\n";
                    break;
                case "help":
                    print_help();
                    break;
            }

            echo "</div>\n";

            echo "<div id=\"footer\">\n";
            echo "Updated on ".date("j M Y G:i:s", time()); #.", conform to XHTML 1.0 and CSS 2.0";
            echo "</div>\n";

            $client->close();
        ?>
  </body>
</html>
