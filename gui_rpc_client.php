<?php
//
// $Id: gui_rpc_client.php,v 1.5 2005/04/16 01:25:27 charlie Exp $
//
// $Log: gui_rpc_client.php,v $
// Revision 1.5  2005/04/16 01:25:27  charlie
// clean up
//
// Revision 1.4  2005/04/16 00:37:15  charlie
// core client version added to header and footer
//
//

include_once("parse.php");

class RPC_CLIENT {
    var $socket;

    function RPC_CLIENT() {
       $this->socket = @socket_create(AF_INET, SOCK_STREAM, 0);
    }

    function connect($address="127.0.0.1", $port="1043") {
        return socket_connect($this->socket, $address, $port);
    }

    function close() {
        return socket_close($this->socket);
    }

    function send($cmd) {
        $buf = "<boinc_gui_rpc_request>\n<version>0</version>\n".$cmd."\n</boinc_gui_rpc_request>\n";
        return socket_write($this->socket, $buf, strlen($buf));
    }

    function read() {
              $data="";
        while (($buf = @socket_read($this->socket, 4096)) !== false) {
              $data .= $buf;
              if (preg_match("</boinc_gui_rpc_reply>", $data)) break;
        }
        return $data;
    }

    function get_state() {
        if (!$this->socket) return false;

        $result = $this->send("<get_state/>");
        if (!$result) return false;

        $data = $this->read();

        return $this->parse_state($data);
    }

    function get_file_transfers() {
        if (!$this->socket) return false;

        $result = $this->send("<get_file_transfers/>");
        if (!$result) return false;

        $data = $this->read();

        return $this->parse_transfers($data);
    }

// get_version is not a legal rpc command.  However, the
// client always returns its version for any command
// passed to it.  An illegal command just has an error message
// saying its a bad command and not a lot of other stuff we'd
// have to parse.
//    function get_version() {
//        if (!$this->socket) return false;
//
//        $result = $this->send("<get_version/>");
//        //if (!$result) return false;
//
//        $data = $this->read();
//
//        return $this->parse_version($data);
//    }
    function lookup_workunit($workunits, $wu_name) {
        foreach ($workunits as $workunit) {
            if ($workunit["name"]==$wu_name) return $workunit;
        }
        return false;
    }

    function lookup_result($results, $result_name) {
        foreach ($results as $result) {
            if ($result["name"]==$result_name) return $result;
        }
        return false;
    }

//    function parse_version($version)
//    {
//       $strings = explode("\n", $version);
//       $i = -1;
//       $client_version=0;
//       while ($buf = $strings[++$i])
//       {
//          if (parse_int($buf, "client_version", $client_version)) break;
//       }
//       $client_version = $client_version/100;
//       return $client_version;
//    }
    function parse_state($state) {
        $strings = explode("\n", $state);

        $client_state = array("host_info"=>"", "projects"=>"", "workunits"=>"", "results"=>"");
        $host_info = array();
        $projects = array();
        $workunits = array();
        $results = array();
        $client_major_version=0;
        $client_minor_version=0;
        $client_release="";
        $panic_mode = -1;
//        $work_fetch = -1;

                $i = -1;
        while ($buf = $strings[++$i]) {
            if (match_tag($buf, "<client_state>")) continue;
            if (match_tag($buf, "</client_state>")) break;
            if (match_tag($buf, "<host_info>")) {
                while ($buf = $strings[++$i]) {
                    if (match_tag($buf, "</host_info>")) break;
                    if (parse_int($buf, "time_zone", $host_info["timezone"])) continue;
                    if (parse_string($buf, "domain_name", $host_info["domain_name"])) continue;
                    if (parse_string($buf, "ip_addr", $host_info["ip_addr"])) continue;
                    if (parse_int($buf, "p_ncpus", $host_info["p_ncpus"])) continue;
                    if (parse_string($buf, "p_vendor", $host_info["p_vendor"])) continue;
                    if (parse_string($buf, "p_model", $host_info["p_model"])) continue;
                    if (parse_double($buf, "p_fpops", $host_info["p_fpops"])) continue;
                    if (parse_double($buf, "p_iops", $host_info["p_iops"])) continue;
                    if (parse_double($buf, "p_membw", $host_info["p_membw"])) continue;
                    if (parse_int($buf, "p_fpops_err", $host_info["p_fpops_err"])) continue;
                    if (parse_int($buf, "p_iops_err", $host_info["p_iops_err"])) continue;
                    if (parse_int($buf, "p_membw_err", $host_info["p_membw_err"])) continue;
                    if (parse_double($buf, "p_calculated", $host_info["p_calculated"])) continue;
                    if (parse_string($buf, "os_name", $host_info["os_name"])) continue;
                    if (parse_string($buf, "os_version", $host_info["os_version"])) continue;
                    if (parse_double($buf, "m_nbytes", $host_info["m_nbytes"])) continue;
                    if (parse_double($buf, "m_cache", $host_info["m_cache"])) continue;
                    if (parse_double($buf, "m_swap", $host_info["m_swap"])) continue;
                    if (parse_double($buf, "d_total", $host_info["d_total"])) continue;
                    if (parse_double($buf, "d_free", $host_info["d_free"])) continue;
                }
            }
            if (match_tag($buf, "<project>")) {
                $project = array("suspended_via_gui"=>false, "frozen"=>false, "sched_rpc_pending"=>false);
                while ($buf = $strings[++$i]) {
                    if (match_tag($buf, "</project>")) break;
                    if (parse_int($buf, "resource_share", $project["resource_share"])) echo "???"; // where is it?
                    if (parse_string($buf, "master_url", $project["master_url"])) continue;
                    if (parse_string($buf, "project_name", $project["project_name"])) continue;
                    if (parse_string($buf, "user_name", $project["user_name"])) continue;
                    if (parse_string($buf, "team_name", $project["team_name"])) continue;
                    if (parse_double($buf, "user_total_credit", $project["user_total_credit"])) continue;
                    if (parse_double($buf, "host_total_credit", $project["host_total_credit"])) continue;
                    if (parse_double($buf, "user_expavg_credit", $project["user_expavg_credit"])) continue;
                    if (parse_double($buf, "host_expavg_credit", $project["host_expavg_credit"])) continue;
                    if (parse_double($buf, "short_term_debt", $project["debt"])) continue;
                    if (parse_double($buf, "long_term_debt", $project["ltdebt"])) continue;
                    if (parse_double($buf, "resource_share", $project["resource_share"])) continue;
                    if (parse_double($buf, "min_rpc_time", $project["min_rpc_time"])) continue;
                    if (parse_double($buf, "duration_correction_factor", $project["duration_correction_factor"])) continue;
                    if (match_tag($buf, "<sched_rpc_pending/>")) $project["sched_rpc_pending"] = true;
                    if (match_tag($buf, "<suspended_via_gui/>")) $project["suspended_via_gui"] = true;
                    if (match_tag($buf, "<dont_request_more_work/>")) $project["frozen"] = true;
                }
                array_push($projects, $project);
            }
            if (match_tag($buf, "<workunit>")) {
                $workunit = array();
                while ($buf = $strings[++$i]) {
                    if (match_tag($buf, "</workunit>")) break;
                    if (parse_string($buf, "name", $workunit["name"])) continue;
                    if (parse_string($buf, "app_name", $workunit["app_name"])) continue;
                    if (parse_int($buf, "version_num", $workunit["version_num"])) continue;
                    if (parse_double($buf, "rsc_fpops_est", $workunit["rsc_fpops_est"])) continue;
                    if (parse_double($buf, "rsc_fpops_bound", $workunit["rsc_fpops_bound"])) continue;
                    if (parse_double($buf, "rsc_memory_bound", $workunit["rsc_memory_bound"])) continue;
                    if (parse_double($buf, "rsc_disk_bound", $workunit["rsc_disk_bound"])) continue;
                }
                $workunit["project"] = $project;
                array_push($workunits, $workunit);
            }
            if (match_tag($buf, "<result>")) {
                $result = array("ready_to_report"=>false, "got_server_ack"=>false, "suspended_via_gui"=>false,
                                "aborted_via_gui"=>false);
                while ($buf = $strings[++$i]) {
                    if (match_tag($buf, "</result>")) break;
                    if (parse_string($buf, "name", $result["name"])) continue;
                    if (parse_string($buf, "wu_name", $result["wu_name"])) continue;
                    if (parse_string($buf, "project_url", $result["project_url"])) continue;
                    if (match_tag($buf, "<suspended_via_gui/>")) {
                        $result["suspended_via_gui"] = true;
                        continue;
                    }
                    if (match_tag($buf, "<aborted_via_gui/>")) {
                        $result["aborted_via_gui"] = true;
                        continue;
                    }
                    if (parse_double($buf, "report_deadline", $result["report_deadline"])) continue;
                    if (match_tag($buf, "<ready_to_report/>")) {
                        $result["ready_to_report"] = true; 
                        continue;
                    }
                    if (match_tag($buf, "<got_server_ack/>")) {
                        $result["got_server_ack"] = true;
                        continue;
                    }
                    if (parse_double($buf, "final_cpu_time", $result["final_cpu_time"])) continue;
                    if (parse_double($buf, "estimated_cpu_time_remaining", $result["estimated_cpu_time_remaining"])) continue;
                    if (parse_int($buf, "state", $result["state"])) continue;
                    if (parse_int($buf, "exit_status", $result["exit_status"])) continue;
                    if (parse_int($buf, "signal", $result["signal"])) continue;
                    if (parse_int($buf, "active_task_state", $result["active_task_state"])) continue;
                    if (match_tag($buf, "<stderr_out>")) {
                        while ($buf = $strings[++$i]) {
                            if (match_tag($buf, "</stderr_out>")) break;
                        }
                    }
                    if (match_tag($buf, "<edf_scheduled/>")) { $panic_mode=1; }
                    $active_task = array();
                    if (match_tag($buf, "<active_task>")) {
                        while ($buf = $strings[++$i]) {
                            if (match_tag($buf, "</active_task>")) break;
                            if (parse_string($buf, "project_master_url", $active_task["project_master_url"])) continue;
                            if (parse_string($buf, "result_name", $active_task["result_name"])) continue;
                            if (parse_int($buf, "app_version_num", $active_task["app_version_num"])) continue;
                            if (parse_string($buf, "slot", $active_task["slot"])) continue;
                            if (parse_double($buf, "checkpoint_cpu_time", $active_task["checkpoint_cpu_time"])) continue;
                            if (parse_double($buf, "current_cpu_time", $active_task["current_cpu_time"])) continue;
                            if (parse_double($buf, "fraction_done", $active_task["fraction_done"])) continue;
                            if (parse_int($buf, "scheduler_state", $active_task["scheduler_state"])) continue;
                        }
                    }
                    $result["active_task"] = $active_task;
                }
                $result["project"] = $project;
                $result["workunit"] = $this->lookup_workunit($workunits, $result["wu_name"]);
                array_push($results, $result);
            }
            if (parse_int($buf, "core_client_major_version", $client_major_version)) continue;
            if (parse_int($buf, "core_client_minor_version", $client_minor_version)) continue;
            if (parse_int($buf, "core_client_release", $client_release)) continue;

# deadline and workfetch tags changed form 4.x to 5.x clients.
# This should take care of both.
# Note: deadline tag removed in 5.8 client.  workfetch is supposed
# to be there.  Need to make sure.

//            if (parse_int($buf, "cpu_earliest_deadline_first", $panic_mode)) continue;
//            if (match_tag($buf, "<cpu_earliest_deadline_first/>")) { $panic_mode=1; }
//            if (parse_int($buf, "work_fetch_no_new_work", $work_fetch)) continue;
//            if (match_tag($buf, "<work_fetch_no_new_work/>")) { $work_fetch=1; };
        }
        $client_state["projects"] = $projects;
        $client_state["workunits"] = $workunits;
        $client_state["results"] = $results;
        $client_state["host_info"] = $host_info;

        $client_version = $client_major_version . "." . $client_minor_version;
        if ($client_release != "")
        {
          $client_version = $client_version . "." . $client_release;
        }

        $client_state["client_version"] = $client_version;
        $client_state["panic_mode"] = $panic_mode;
//        $client_state["work_fetch"] = $work_fetch;

        return $client_state;
    }

    function parse_transfers($transfers) {
        $strings = explode("\n", $transfers);
        $file_xfers = array();

        while ($buf = $strings[++$i]) {
            if (match_tag($buf, "<file_transfers>")) continue;
            if (match_tag($buf, "</file_transfers>")) break;
            if (match_tag($buf, "<file_transfer>")) {
                $file_xfer = array("bytes_xferred"=>"0", "file_offset"=>"0", "xfer_speed"=>"0", "hostname"=>"");
                while ($buf = $strings[++$i]) {
                    if (match_tag($buf, "</file_transfer>")) break;
                    if (parse_string($buf, "project_url", $file_xfer["project_url"])) continue;
                    if (parse_string($buf, "name", $file_xfer["name"])) continue;
                    if (parse_double($buf, "nbytes", $file_xfer["nbytes"])) continue;
                    if (parse_double($buf, "max_nbytes", $file_xfer["max_nbytes"])) continue;
                    if (parse_int($buf, "status", $file_info["status"])) continue;
                    if (match_tag($buf, "<generated_locally/>")) $file_xfer["generated_locally"] = true;

                    if (match_tag($buf, "<persistent_file_xfer>")) {
                        while ($buf = $strings[++$i]) {
                            if (match_tag($buf, "</persistent_file_xfer>")) break;
                            if (parse_int($buf, "num_retries", $file_xfer["num_retries"])) continue;
                            if (parse_double($buf, "first_request_time", $file_xfer["first_request_time"])) continue;
                            if (parse_double($buf, "next_request_time", $file_xfer["next_request_time"])) continue;
                            if (parse_double($buf, "time_so_far", $file_xfer["time_so_far"])) continue;
                        }
                    }
                    if (match_tag($buf, "<file_xfer>")) {
                        while ($buf = $strings[++$i]) {
                            if (match_tag($buf, "</file_xfer>")) break;
                            if (parse_double($buf, "bytes_xferred", $file_xfer["bytes_xferred"])) continue;
                            if (parse_double($buf, "file_offset", $file_xfer["file_offset"])) continue;
                            if (parse_double($buf, "xfer_speed", $file_xfer["xfer_speed"])) continue;
                            if (parse_string($buf, "hostname", $file_xfer["hostname"])) continue;
                        }
                        $file_xfer["active"] =true;
                    }
//                    else {
//                        $file_xfer["active"] =false;
//                    }
                }

                array_push($file_xfers, $file_xfer);
            }
        }

        return $file_xfers;
    }

    function run_benchmarks() {

        $result = $this->send("<run_benchmarks/>");
        if (!$result) return false;

        if (($buf = $this->$read()) !== false) {
            return (preg_match("<success/>", $buf));
        }
    }

    function show_graphics() {

        if (!$this->socket) return false;

        $cmd = "<result_show_graphics>\n";
//        $result = @socket_write($this->socket, $cmd, strlen($cmd));
        $result = $this->send($cmd);
        if (!$result) return false;

//         if (($buf = @socket_read($this->socket, 128)) !== false) {
         if (($buf = $this->read()) !== false) {
         return (preg_match("<success/>", $buf));
        }
    }

    function project_update($url) {

        if (!$this->socket) return false;
        if (!$url) return false;

        $cmd =
            "<project_update>\n".
            "<project_url>".$url."</project_url>\n".
            "</project_update>\n";
        $result = $this->send($cmd);
        if (!$result) return false;

        if (($buf = $this->read()) !== false) {
            return (preg_match("<success/>", $buf));
        }
    }

    function project_suspend($url) {

        if (!$this->socket) return false;
        if (!$url) return false;

        $cmd =
            "<project_suspend>\n".
            "<project_url>".$url."</project_url>\n".
            "</project_suspend>\n";
        $result = $this->send($cmd);
        if (!$result) return false;

        if (($buf = $this->read()) !== false) {
            return (preg_match("<success/>", $buf));
        }
    }

    function project_resume($url) {

        if (!$this->socket) return false;
        if (!$url) return false;

        $cmd =
            "<project_resume>\n".
            "<project_url>".$url."</project_url>\n".
            "</project_resume>\n";
        $result = $this->send($cmd);
        if (!$result) return false;

        if (($buf = $this->read()) !== false) {
            return (preg_match("<success/>", $buf));
        }
    }

    function project_freeze($url) {

        if (!$this->socket) return false;
        if (!$url) return false;

        $cmd =
            "<project_nomorework>\n".
            "<project_url>".$url."</project_url>\n".
            "</project_nomorework>\n";
        $result = $this->send($cmd);
        if (!$result) return false;

        if (($buf = $this->read()) !== false) {
            return (preg_match("<success/>", $buf));
        }
    }

    function project_thaw($url) {

        if (!$this->socket) return false;
        if (!$url) return false;

        $cmd =
            "<project_allowmorework>\n".
            "<project_url>".$url."</project_url>\n".
            "</project_allowmorework>\n";
        $result = $this->send($cmd);
        if (!$result) return false;

        if (($buf = $this->read()) !== false) {
            return (preg_match("<success/>", $buf));
        }
    }

    function result_suspend($rslt, $url) {

        if (!$this->socket) return false;
        if (!$url) return false;

        $cmd =
            "<suspend_result>\n".
            "<project_url>".$url."</project_url>\n".
            "<name>".$rslt."</name>\n".
            "</suspend_result>\n";
        $result = $this->send($cmd);
        if (!$result) return false;

        if (($buf = $this->read()) !== false) {
            return (preg_match("<success/>", $buf));
        }
    }

    function result_resume($rslt, $url) {

        if (!$this->socket) return false;
        if (!$url) return false;

        $cmd =
            "<resume_result>\n".
            "<project_url>".$url."</project_url>\n".
            "<name>".$rslt."</name>\n".
            "</resume_result>\n";
        $result = $this->send($cmd);
        if (!$result) return false;

        if (($buf = $this->read()) !== false) {
            return (preg_match("<success/>", $buf));
        }
    }

    function result_abort($rslt, $url) {

        if (!$this->socket) return false;
        if (!$url) return false;

        $cmd =
            "<abort_result>\n".
            "<project_url>".$url."</project_url>\n".
            "<name>".$rslt."</name>\n".
            "</abort_result>\n";
        $result = $this->send($cmd);
        if (!$result) return false;

        if (($buf = $this->read()) !== false) {
            return (preg_match("<success/>", $buf));
        }
    }

    function retry_transfer($file, $url) {

        if (!$this->socket) return false;
        if (!$url) return false;

        $cmd =
            "<retry_file_transfer>\n".
            "<project_url>".$url."</project_url>\n".
            "<filename>".$file."</filename\n".
            "</retry_file_transfer>\n";

        $result = $this->send($cmd);
        if (!$result) return false;

        if (($buf = $this->read()) !== false) {
            return (preg_match("<success/>", $buf));
        }
    }


    function project_reset($url) {

        if (!$this->socket) return false;
        if (!$url) return false;

        $cmd =
            "<project_reset>\n".
            "<project_url>".$url."</project_url>\n".
            "</project_reset>\n";
        $result = $this->send($cmd);
        if (!$result) return false;

        if (($buf = $this->$read()) !== false) {
            return (preg_match("<success/>", $buf));
        }
    }

    function project_detach($url) {

        if (!$this->socket) return false;
        if (!$url) return false;

        $cmd =
            "<project_detach>\n".
            "<project_url>".$url."</project_url>\n".
            "</project_detach>\n";
        $result = $this->send($cmd);
        if (!$result) return false;

        if (($buf = $this->$read()) !== false) {
            return (preg_match("<success/>", $buf));
        }
    }

    function project_attach($url, $key) {

        if (!$this->socket) return false;
        if (!$url||!$key) return false;

        $cmd =
            "<project_attach>\n".
            "<project_url>".$url."</project_url>\n".
            "<authenticator>".$key."</authenticator>\n".
            "</project_attach>\n";
        $result = $this->send($cmd);
        if (!$result) return false;

        if (($buf = $this->$read()) !== false) {
            return (preg_match("<success/>", $buf));
        }
    }

    function set_run_mode($mode) {

        if (!$this->socket) return false;

        switch ($mode) {
            case "0":
                $cmd = "<always>";
                break;
            case "1":
                $cmd = "<auto>";
                break;
            case "2":
                $cmd = "<never>";
                break;
            default:
                return false;
        }

        $result = $this->send("<set_run_mode>\n".$cmd."\n</set_run_mode>\n");
        if (!$result) return false;

        if (($buf = $this->$read()) !== false) {
            return (preg_match("<success/>", $buf));
        }
    }

    function set_network_mode($mode) {

        if (!$this->socket) return false;

        switch ($mode) {
            case "0":
                $cmd = "<always>";
                break;
            case "1":
                $cmd = "<auto>";
                break;
            case "2":
                $cmd = "<never>";
                break;
            default:
                return false;
        }
        $result = $this->send("<set_network_mode>\n".$cmd."\n</set_network_mode>\n");
        if (!$result) return false;

        if (($buf = $this->$read()) !== false) {
            return (preg_match("<success/>", $buf));
        }
    }

    function get_messages($seqno) {

        if (!$this->socket) return false;

        $result = $this->send("<get_messages>\n<seqno>".$seqno."</seqno>\n</get_messages>\n");
        if (!$result) return false;

        // receive data
        $data = $this->read();

        // parse data
        $msgs = array();
        $i = -1;
        $strings = explode("\n", $data);
        while ($buf = $strings[++$i]) {
            if (match_tag($buf, "<msgs>")) continue;
            if (match_tag($buf, "</msgs>")) break;
            if (match_tag($buf, "<msg>")) {
                $md = array("project"=>"---");
                while ($buf = $strings[++$i]) {
                    if (match_tag($buf, "</msg>")) break;
                    if (parse_string($buf, "project", $md["project"])) continue;
                    if (match_tag($buf, "<body>")) {
                        $md["body"] = "";
                        while ($buf = $strings[++$i]) {
                            if (match_tag($buf, "</body>")) break;
                            $md["body"] .= $buf;
                        }
                        continue;
                    }
                    if (parse_int($buf, "pri", $md["priority"])) continue;
                    if (parse_int($buf, "time", $md["timestamp"])) continue;
                    if (parse_int($buf, "seqno", $md["seqno"])) continue;
                }
                array_push($msgs, $md);
            }
        }
        return $msgs;
    }

}

?>
