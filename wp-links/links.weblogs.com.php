<?php
require_once('b2config.php');
require_once($abspath.'/wp-links/links.config.php');
require_once($abspath.$b2inc.'/b2functions.php');

// globals to hold state
$updated_timestamp = 0;
$all_links = array();

/**
 ** preload_links()
 ** Pre-load the visible, non-blank, links into an associative array $all_links
 ** key is url, value is array of link_id and update_time
 ** Note: update time is initialised to 0. That way we only have to update (in
 ** the db) the ones which have been updated (on weblogs.com).
 **/
function preload_links() {
    global $tablelinks, $all_links, $wpdb;
    $links = $wpdb->get_results("SELECT link_id, link_url FROM $tablelinks WHERE link_visible = 'Y' AND link_url <> ''");
    foreach ($links as $link) {
        //echo("link_url $link->link_url ");
        $link_url = str_replace('www.', '', $link->link_url);
        $link_url = preg_replace('/(?:index|default)\.[a-z]{2,}/i', '', $link_url);
        //echo(" now equals $link_url\n");
        $all_links[$link_url] = array($link->link_id, 0);
    }
}

/**
 ** update_links()
 ** Update in the db the links which have been updated ($all_links[url][1] != 0)
 **/
function update_links() {
    global $tablelinks, $all_links, $wpdb;
    reset($all_links);
    while (list($id, $val) = each($all_links)) {
        if ($val[1]) {
            //echo("executing: $sql\n");
            $wpdb->query("UPDATE $tablelinks SET link_updated = '$val[1]' WHERE link_id = $val[0]");
        }
    } // end while
}

/**
 ** get_weblogs_updatedfile()
 ** Retrieves and caches a copy of the weblogs.com changed blogs xml file.
 ** If the file exists check it's age, get new copy if old.
 ** If a new or updated file has been written return true (needs processing)
 ** otherwise return false (nothing to do)
 **/
function get_weblogs_updatedfile() {
    global $weblogs_cache_file, $weblogs_xml_url, $weblogs_cacheminutes;
    $update = false;

    if (file_exists($weblogs_cache_file)) {
        // is it old?
        $modtime = filemtime($weblogs_cache_file);
        if ((time() - $modtime) > ($weblogs_cacheminutes * 60)) {
            $update = true;
        }
    } else { // doesn't exist
        $update = true;
    }

    if ($update) {
        // get a new copy
        $contents = implode('', file($weblogs_xml_url)); // file_get_contents not available < 4.3
        $cachefp = fopen($weblogs_cache_file, "w");
        fwrite($cachefp, $contents);
        fclose($cachefp);
    }
    return $update;
}

/**
 ** startElement()
 ** Callback function. Called at the start of a new xml tag.
 **/
function startElement($parser, $tagName, $attrs) {
    global $updated_timestamp, $all_links;
    if ($tagName == 'WEBLOGUPDATES') {
        //convert 'updated' into php date variable
        $updated_timestamp = strtotime($attrs['UPDATED']);
        //echo('got timestamp of ' . gmdate('F j, Y, H:i:s', $updated_timestamp) . "\n");
    } else if ($tagName == 'WEBLOG') {
        // is this url in our links?
        //echo("wblogs.com link " .$attrs['URL']);
        $link_url = str_replace('www.', '', $attrs['URL']);
        $link_url = preg_replace('/(?:index|default)\.[a-z]{2,}/i', '', $link_url);
        //echo(" now equals $link_url\n");
        if (isset($all_links[$link_url])) {
            $all_links[$link_url][1] = gmdate('YmdHis', $updated_timestamp - $attrs['WHEN']);
            //echo('set link id ' . $all_links[$link_url][0] . ' to date ' . $all_links[$link_url][1] . "\n");
        }
    }
}

/**
 ** endElement()
 ** Callback function. Called at the end of an xml tag.
 **/
function endElement($parser, $tagName) {
    // nothing to do.
}


// get/update the cache file.
// true return means new copy 
if (get_weblogs_updatedfile()) {

    //echo('<pre>');
    // pre-load the links
    preload_links();

    // Create an XML parser
    $xml_parser = xml_parser_create();

    // Set the functions to handle opening and closing tags
    xml_set_element_handler($xml_parser, "startElement", "endElement");

    // Open the XML file for reading
    $fp = fopen($weblogs_cache_file, "r")
          or die("Error reading XML data.");

    // Read the XML file 4KB at a time
    while ($data = fread($fp, 4096)) {
        // Parse each 4KB chunk with the XML parser created above
        xml_parse($xml_parser, $data, feof($fp))
                // Handle errors in parsing
                or die(sprintf("XML error: %s at line %d",
                               xml_error_string(xml_get_error_code($xml_parser)),
                               xml_get_current_line_number($xml_parser)));
    }
    
    // Close the XML file
    fclose($fp);

    // Free up memory used by the XML parser
    xml_parser_free($xml_parser);

    // now update the db with latest times
    update_links();

    //echo('</pre>');
} // end if updated cache file
?>
