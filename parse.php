<?php

//
// $Id: parse.php,v 1.2 2005/04/16 00:40:07 charlie Exp $
//
// $Log: parse.php,v $
// Revision 1.2  2005/04/16 00:40:07  charlie
// added RCS headers
//
//

// return true if the tag appears in the line
//
function match_tag($buf, $tag) {
    return strstr($buf, $tag);
}

// parse an integer of the form <tag>1234</tag>
// return true if it's there
// Note: this doesn't check for the end tag
//
function parse_int($buf, $tag, &$res) {
    if (eregi("<".$tag.">([0-9]+)</".$tag.">", $buf, $x)) {
		$res = $x[1];
		return true;
	}
	return false;
}

// Same, for doubles
//
function parse_double($buf, $tag, &$res) {
    if (eregi("<".$tag.">(-?[0-9]*.?[0-9]+)</".$tag.">", $buf, $x)) {
		$res = $x[1];
		return true;
	}
	return false;
}

// parse a string of the form ...<tag attrs>string</tag>...;
// returns the "string" part.
// Does XML unescaping (replace &lt; with <)
// "string" may not include '<'
// Strips white space from ends.
// Use "<tag", not "<tag>", if there might be attributes
//
function parse_string($buf, $tag, &$res) {
    if (eregi("<".$tag.".*>([^<]+)</".$tag.">", $buf, $str)) {
		$res = $str[1];
		return true;
	}
	return false;
}

?>
