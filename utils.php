<?php

//
// $Id: utils.php,v 1.6 2006/01/26 00:06:35 charlie Exp $
//
// $Log: utils.php,v $
// Revision 1.6  2006/01/26 00:06:35  charlie
// corrected estimated cpu time remaining
// general html/css cleanup
//
// Revision 1.2  2005/04/16 00:41:08  charlie
// added RCS headers
//
//

function timestr($seconds) {
    $hour = (integer)($seconds / 3600);
    $min = (integer)($seconds / 60) % 60; if ($min < 10) $min = "0".$min;
    $sec = (integer)($seconds) % 60; if ($sec < 10) $sec = "0".$sec;

    return $hour.":".$min.":".$sec;
}

function print_projects($client, $client_state, $address) {
    echo "<table summary=\"List of attached projects\">\n";
//    echo "<table class=\"projects\" summary=\"List of attached projects\">\n";
//    echo " <colgroup>\n";
//    echo "  <col width=\"2*\" />\n";
//    echo "  <col width=\"2*\" />\n";
//    echo "  <col width=\"1*\" />\n";
//    echo "  <col width=\"1*\" />\n";
//    echo "  <col width=\"1*\" />\n";
//    echo "  <col width=\"1*\" />\n";
//    echo "  <col width=\"1*\" />\n";
//    echo "  <col width=\"2*\" />\n";
//    echo " </colgroup>\n";
    echo " <tr>\n";
    echo "  <th>Project</th>\n";
    echo "  <th>Account</th>\n";
    echo "  <th>Total Credit</th>\n";
    echo "  <th>Avg. Credit</th>\n";
    echo "  <th>Debt</th>\n";
    echo "  <th>LT Debt</th>\n";
    echo "  <th>Share</th>\n";
    echo "  <th>Percent</th>\n";
    echo "  <th>DCF</th>\n";
    echo "  <th>Retry In</th>\n";
    echo "  <th>Tools</th>\n";
    echo " </tr>\n";
    $total_share = 0;
    foreach ($client_state["projects"] as $project) {
      $total_share = $total_share + $project["resource_share"];
    }
    foreach ($client_state["projects"] as $project) {
      if ($project["suspended_via_gui"] == "true")
      {
        echo " <tr class=\"suspend\">\n";
      }
      else if ($project["frozen"] == "true")
      {
        echo " <tr class=\"paused\">\n";
      }
      else
      {
        echo " <tr>\n";
      }
      echo "  <td><a href=\"".$project["master_url"]."\" target=\"_blank\" title=\"".$project["project_name"]."\">".$project["project_name"]."</a></td>\n";
      echo "  <td>".$project["user_name"]."</td>\n";
      printf ("  <td class=right> %01.2f </td>\n", $project["user_total_credit"]);
      printf ("  <td class=right> %01.2f </td>\n", $project["user_expavg_credit"]);
      printf ("  <td class=right> %01.2f </td>\n", $project["debt"]);
      printf ("  <td class=right> %01.2f </td>\n", $project["ltdebt"]);
      printf ("  <td class=right> %01.2f </td>\n", $project["resource_share"]);
      printf ("  <td class=right> %01.2f </td>\n", $project["resource_share"] / $total_share * 100.0);
      printf ("  <td class=right> %01.3f </td>\n", $project["duration_correction_factor"]);
      echo " <td>";
      $deltatime = $project["min_rpc_time"] - time();
      if ($deltatime > 0) {
        $retry_in = timestr($deltatime);
        echo $retry_in;
      }
      echo " </td>\n";
      echo "  <td>".
      "<a href=\"".$_SERVER["PHP_SELF"]."?index=tools&amp;address=".$address."&amp;cmd=project_update&amp;url=".urlencode($project["master_url"])."\" title=\"Update project\" class=\"normal\">update</a>".
      "<span class=\"normal\">&nbsp;-&nbsp;</span>".
      "<a href=\"".$_SERVER["PHP_SELF"]."?index=tools&amp;address=".$address."&amp;cmd=project_reset&amp;url=".urlencode($project["master_url"])."\" title=\"Reset project\" class=\"normal\">reset</a>".
      "<span class=\"normal\">&nbsp;-&nbsp;</span>".
      "<a href=\"".$_SERVER["PHP_SELF"]."?index=tools&amp;address=".$address."&amp;cmd=project_detach&amp;url=".urlencode($project["master_url"])."\" title=\"Detach from project\" class=\"normal\">detach</a>".
      "<span class=\"normal\">&nbsp;-&nbsp;</span>";

      if ($project["suspended_via_gui"] == "true")
      {
        echo "<a href=\"".$_SERVER["PHP_SELF"]."?index=tools&amp;address=".$address."&amp;cmd=project_resume&amp;url=".urlencode($project["master_url"])."\" title=\"Resume project\" class=\"normal\">resume</a>";
//      "<span class=\"normal\">/</span>".
      }
      else
      {
        echo "<a href=\"".$_SERVER["PHP_SELF"]."?index=tools&amp;address=".$address."&amp;cmd=project_suspend&amp;url=".urlencode($project["master_url"])."\" title=\"Suspend project\" class=\"suspend\">suspend</a>";
      }

      echo "<span class=\"normal\">&nbsp;-&nbsp;</span>";

      if ($project["frozen"] == "true")
      {
        echo "<a href=\"".$_SERVER["PHP_SELF"]."?index=tools&amp;address=".$address."&amp;cmd=project_thaw&amp;url=".urlencode($project["master_url"])."\" title=\"Thaw project\" class=\"normal\">thaw</a>";
      }
//      "<span class=\"normal\">/</span>".
      else
      {
        echo "<a href=\"".$_SERVER["PHP_SELF"]."?index=tools&amp;address=".$address."&amp;cmd=project_freeze&amp;url=".urlencode($project["master_url"])."\" title=\"Freeze project\" class=\"paused\">freeze</a>";
      }
      echo "</td>\n";
      echo " </tr>\n";
    }
    echo "</table>\n";
    return;
}

function print_work($client, $client_state, $address) {
//    echo "<table  summary=\"Current work\">\n";
    echo "<table class=\"work\" summary=\"Current work\">\n";
//    echo " <colgroup>\n";
//    echo "  <col width=\"1*\" />\n";
//    echo "  <col width=\"1*\" />\n";
//    echo "  <col width=\"1*\" />\n";
//    echo "  <col width=\"1*\" />\n";
//    echo "  <col width=\"1*\" />\n";
//    echo "  <col width=\"1*\" />\n";
//    echo "  <col width=\"2*\" />\n";
//    echo "  <col width=\"1*\" />\n";
//    echo "  <col width=\"1*\" />\n";
//    echo " </colgroup>\n";
    echo " <tr>\n";
    echo "  <th>#</th>\n";
    echo "  <th>Project</th>\n";
    echo "  <th>Name</th>\n";
    echo "  <th>Application</th>\n";
    echo "  <th>CPU Time</th>\n";
    echo "  <th>Progress</th>\n";
    echo "  <th>Finished In</th>\n";
    echo "  <th>Report Deadline</th>\n";
    echo "  <th>Status</th>\n";
    echo "  <th>Tools</th>\n";
    echo " </tr>\n";
		$line=1;
    foreach ($client_state["results"] as $result) {
      $active_task = $result["active_task"];
      if ($result["suspended_via_gui"] == "true") {
        echo " <tr class=\"suspend\">\n";
      } else {
        switch ($result["state"]) {
          case 2:
            if ($active_task) {
              if ($active_task["scheduler_state"] == 1) {
                  echo " <tr class=\"paused\">\n";
              } else {
                  echo " <tr class=\"running\">\n";
              }
            } else {
                echo " <tr class=\"normal\">\n";
            }
            break;
          case 5:
            echo "<tr class=\"ready\">\n";
            break;
          case 1:
          case 4:
            echo "<tr class=\"transferring\">\n";
            break;
          default:
            if ($result["suspended_via_gui"] == "true") {
              echo " <tr class=\"suspend\">\n";
            } else {
              echo " <tr class=\"normal\">\n";
            }
            break;
        }
      }
			echo "<td>".$line."</td>\n";
			$line = $line + 1;
      // project
      echo "  <td>".$result["project"]["project_name"]."</td>\n";
      // name
      echo "  <td>".$result["name"]."</td>\n";
      // application
      //echo "  <td>".$result["workunit"]["app_name"]." ".($result["workunit"]["version_num"]/100)."</td>\n";
      echo "  <td>".$result["workunit"]["app_name"];
			printf (" %01.2f </td>\n",$result["workunit"]["version_num"]/100);
      //cpu time
      if ($active_task) {
        echo "  <td class=center>".timestr($active_task["current_cpu_time"])."</td>\n";
      } else {
        if ($result["ready_to_report"]||($result["state"] > 3)) {
          echo "  <td class=center>".timestr($result["final_cpu_time"])."</td>\n";
        } else {
          echo "  <td class=center>---</td>\n";
        }
      }
      // progress
      if ($active_task) {
        printf ("  <td class=right> %01.2f%% </td>\n", $active_task["fraction_done"]*100)."%</td>\n";
      } else {
        if ($result["state"] > 3) {
          echo "  <td class=right>100.00%</td>\n";
        } else {
          echo "  <td class=center>---</td>\n";
        }
      }
      // to completion
      if (!$active_task) {
        if ($result["state"] < 4) {
          $tocomp =  $result["workunit"]["rsc_fpops_est"] / $client_state["host_info"]["p_fpops"];
        } else {
          $tocomp = 0;
        }
      } else {
        $tocomp = ($active_task["current_cpu_time"] / $active_task["fraction_done"]) - $active_task["current_cpu_time"];
        if ($tocomp < 0) {
          $tocomp =  $result["workunit"]["rsc_fpops_est"] / $client_state["host_info"]["p_fpops"];
        }
      }
					//$tocomp = $tocomp * $result["project"]["duration_correction_factor"];
          $tocomp = $result["estimated_cpu_time_remaining"];
          //$tocomp = $result["estimated_cpu_time_remaining"] * $result["project"]["duration_correction_factor"];
      if ($result["ready_to_report"]) {
        echo "  <td class=center>---</td>\n";
      } else {
				//printf ("  <td class=right> %01.4f </td>\n", $result["project"]["duration_correction_factor"]);
        //echo "  <td class=center>".$client_state["projects"]["duration_correction_factor"]."</td>\n";
        echo "  <td class=center>".timestr($tocomp)."</td>\n";
      }

      // deadline
      echo "  <td>".date("j M Y G:i:s", $result["report_deadline"])."</td>\n";

      // state
      switch ($result["state"]) {
        case 0:
          echo "  <td>Ready to download</td>\n";
          break;
        case 1:
          echo "  <td>Downloading</td>\n";
          break;
        case 2:
          if ($active_task) {
            if ($active_task["scheduler_state"] == 1) {
                echo "  <td>Paused</td>\n";
            } else {
                echo "  <td>Running</td>\n";
            }
          } elseif ($result["aborted_via_gui"]){
								echo "  <td>Aborted</td>\n";
          } else {
            echo "  <td>Rdy to run</td>\n";
          }
          break;
        case 3:
          echo "  <td>Computation Error</td>\n";
          break;
        case 4:
          echo "  <td>Uploading</td>\n";
          break;
        case 5:
          if ($result["ready_to_report"]) {
            if ($result["project"]) {
              echo "  <td><a href=\"".$_SERVER["PHP_SELF"]."?index=tools&amp;address=".$address."&amp;cmd=project_update&amp;url=".urlencode($result["project"]["master_url"])."\" title=\"Update ".$result["project"]["project_name"]."\">Rdy to rpt</td>\n";
            } else {
              echo "  <td>Ready to report</td>\n";
            }
          } else {
            echo "  <td>Result uploaded</td>\n";
          }
          break;
				case 6:
					echo "  <td>Aborted</td>\n";
					break;
        default:
          echo "  <td>".$result["state"]."</td>\n";
      }
            //Tools
      echo "  <td>";

      if ($result["suspended_via_gui"] == "true")
      {
        echo "<a href=\"".$_SERVER["PHP_SELF"]."?index=tools&amp;address=".$address."&amp;cmd=result_resume&amp;result=".$result["name"]."&amp;url=".urlencode($result["project"]["master_url"])."\" title=\"Resume result\" class=\"normal\">resume</a>";
//            "<span class=\"normal\">/</span>".
      }
      else
      {
      echo "<a href=\"".$_SERVER["PHP_SELF"]."?index=tools&amp;address=".$address."&amp;cmd=result_suspend&amp;result=".$result["name"]."&amp;url=".urlencode($result["project"]["master_url"])."\" title=\"Suspend result\" class=\"suspend\">suspend</a>";
      }

      echo "<span class=\"normal\">&nbsp;-&nbsp;</span>".
      "<a href=\"".$_SERVER["PHP_SELF"]."?index=tools&amp;address=".$address."&amp;cmd=result_abort&amp;result=".$result["name"]."&amp;url=".urlencode($result["project"]["master_url"])."\" title=\"Abort result\" class=\"normal\">abort</a>".
      "</td>";
      echo " </tr>\n";
    }
    echo "</table>\n";
    return;
}

function print_transfers($client, $transfers, $address) {
    echo "<table class=\"transfers\" summary=\"Ongoing file transfers\">\n";
    echo " <colgroup>\n";
    echo "  <col width=\"2*\" />\n";
    echo "  <col width=\"3*\" />\n";
    echo "  <col width=\"1*\" />\n";
    echo "  <col width=\"1*\" />\n";
    echo "  <col width=\"1*\" />\n";
    echo "  <col width=\"1*\" />\n";
    echo "  <col width=\"1*\" />\n";
    echo "  <col width=\"1*\" />\n";
    echo "  <col width=\"1*\" />\n";
    echo "  <col width=\"1*\" />\n";
    echo "  <col width=\"1*\" />\n";
    echo " </colgroup>\n";
    echo " <tr>\n";
    echo "  <th>Project</th>\n";
    echo "  <th>File</th>\n";
    echo "  <th>Progress</th>\n";
    echo "  <th>Size</th>\n";
    echo "  <th>So Far</th>\n";
    echo "  <th>To Go</th>\n";
    echo "  <th>Time</th>\n";
    echo "  <th>Speed</th>\n";
    echo "  <th>Status</th>\n";
    echo "  <th>Retry In</th>\n";
    echo "  <th>Tools</th>\n";
    echo " </tr>\n";
    foreach ($transfers as $file_xfer) {
      echo " <tr>\n";
      // project
      echo "  <td>".$file_xfer["project_url"]."</td>\n";
      // file
      echo "  <td>".$file_xfer["name"]."</td>\n";
      // progress
      echo sprintf("  <td>%0.2f%%</td>\n", ($file_xfer["bytes_xferred"] / $file_xfer["nbytes"])*100);
      // size
      echo sprintf("  <td>%0.0f B</td>\n", $file_xfer["nbytes"]);
      // So Far
      echo sprintf("  <td>%0.0f B</td>\n", $file_xfer["bytes_xferred"]);
      // To Go
      echo sprintf("  <td>%0.0f B</td>\n", $file_xfer["nbytes"] - $file_xfer["bytes_xferred"]);
      // time
      echo "  <td>".timestr($file_xfer["time_so_far"])."</td>\n";
      // speed
      echo sprintf("  <td>%0.2f Bps</td>\n", $file_xfer["xfer_speed"]);
      // status
      if ($file_xfer["generated_locally"] && $file_xfer["active"]) {
        echo "  <td>Uploading</td>\n";
      } elseif($file_xfer["generated_locally"] && !$file_xfer["active"]) {
        echo "  <td>Upload pending</td>\n";
      } elseif(!$file_xfer["generated_locally"] && !$file_xfer["active"]) {
        echo "  <td>Download pending</td>\n";
      } else {
        echo "  <td>Downloading</td>\n";
      }
      $deltatime = $file_xfer["next_request_time"] - time();
			if ($deltatime > 0) {
        $retry_in = timestr($deltatime);
        echo "  <td>$retry_in</td>\n";
      } else {
        echo "  <td></td>\n";
      }

            //Tools
      echo "  <td>".
      "<a href=\"".$_SERVER["PHP_SELF"]."?index=tools&amp;address=".$address."&amp;cmd=retry_transfer&amp;file=".$file_xfer["name"]."&amp;url=".urlencode($file_xfer["project_url"])."\" title=\"Retry File Transfer\" class=\"normal\">Retry</a>".
      "</td>\n";
      echo " </tr>\n";
    }
    echo "</table>\n";
    return;
}

function print_help()
{
?>
<ul>
  <li><strong>Project Tab</strong>
  <dl>
    <dt>Project</dt>
      <dd>The name of the project</dd>
    <dt>Account</dt>
      <dd>The name of the account</dd>
    <dt>Total Credit</dt>
      <dd>Number of cobblestones for this project as
          of the most recent update of the project.</dd>
    <dt>Avg Credit</dt>
      <dd>The recent average credit for this project as of the most recent 
          update of the project.</dd>
    <dt>Debt</dt>
      <dd>The resource debt for this project.  When it is time to switch
          to a different project, the project with the highest resource debt 
          will be run.  Updating a project will recalculate the debt and 
          switch projects is a different project than the one currently running
          has a higher debt.</dd>
    <dt>Share</dt>
      <dd>The resource share for the project</dd>
    <dt>Percent</dd>
      <dd>The percent of the total resoruce shares for all projects.  The
          resource shares for all projects are totaled and the percent for the 
          current project is displayed.</dd>
    <dt>Retry In</dt>
      <dd>The time in HH:MM:SS format until the next time the core client will
          contact the project's scheduler.</dd>
    <dt>Tools</dt>
      <dd><li>Update - Force an update to the project.  The core client will
          contact the project's scheduler.
      <dd><li>Reset - Reset a project.  All workunits will be discarded.
          New workunits will be downloaded.
      <dd><li>Detach - Detach the core client from the project.  All workunits 
          will be discarded.  The project will be removed from the list of
          projects.
      <dd><li>Suspend/Resume - Only the appropriate action will be displayed.
          Suspending a project will cause any running workunit for the project
          to stop.  No workunits for the project will run as long as the 
          project is suspended.  A suspended project will be displayed with
          a red colored background.  No work already done is lost.  A 
          suspended project can be resumed by clicking RESUME (when a project
          is suspended, SUSPEND changes to RESUME).  A workunit will be resumed
          when the resource debt for the project is higher than any other 
          project. 
      <dd><li>Freeze/Thaw - Only the appropriate action will be displayed.
          Freezing a project allows all currently downloaded workunits to 
          be processed as normal.  Results will be uploaded.  However, 
          results will not be reported to the project's scheduler, nor will
          any more workunits be downloaded from the project's scheduler.  This
          allows the user to drain the project's queue without the need to
          discard any workunits.  A frozen project is displayed with a
          yellow background.  Thaw will undo the action of Freeze.  Uploaded
          workunits can be reported by using the UPDATE function for the 
          project.
  </dl>
  </li>
  <li><strong>Work Tab</strong>
  <ul>
    <li>Project - The name of the project</li>
    <li>Name - The result name</li>
  </ul>
  </li>
</ul>
<?php
}
?>
