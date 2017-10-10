<?php
require_once "config.php";

class SendTo2 extends Plugin {
  private $host;

  function about() {
    return array(1.1,
		 "Adds support for SendTo2",
		 "Nomis");
  }

  function init($host) {
    $this->host = $host;

    $host->add_hook($host::HOOK_ARTICLE_BUTTON, $this);
    $host->add_hook($host::HOOK_PREFS_TAB, $this);
  }

  function save() {
    $sendto2_url = db_escape_string($_POST["sendto2_url"]);
    $this->host->set($this, "sendto2", $sendto2_url);
    echo "Value set to $sendto2_url";
  }

  function get_js() {
    return file_get_contents(dirname(__FILE__) . "/sendto2.js");
  }

  function hook_prefs_tab($args) {
    if ($args != "prefPrefs") return;

    print "<div dojoType=\"dijit.layout.AccordionPane\" title=\"".__("SendTo2")."\">";

    print "<br/>";

    $value = $this->host->get($this, "sendto2");
    print "<form dojoType=\"dijit.form.Form\">";

    print "<script type=\"dojo/method\" event=\"onSubmit\" args=\"evt\">
           evt.preventDefault();
           if (this.validate()) {
               console.log(dojo.objectToQuery(this.getValues()));
               new Ajax.Request('backend.php', {
                                    parameters: dojo.objectToQuery(this.getValues()),
                                    onComplete: function(transport) {
                                         notify_info(transport.responseText);
                                    }
                                });
           }
           </script>";

    print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"op\" value=\"pluginhandler\">";
    print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"method\" value=\"save\">";
    print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"plugin\" value=\"sendto2\">";
    print "<table width=\"100%\" class=\"prefPrefsList\">";
        print "<tr><td width=\"40%\">".__("SendTo2 url")."</td>";
	print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" required=\"1\" name=\"sendto2_url\" regExp='^(http|https)://.*' value=\"$value\"></td></tr>";
    print "</table>";
    print "<p><button dojoType=\"dijit.form.Button\" type=\"submit\">".__("Save")."</button>";

    print "</form>";

    print "</div>"; #pane

  }

  function hook_article_button($line) {
    return "<img src=\"plugins/sendto2/sendto2.png\"
             style=\"cursor : pointer\" style=\"cursor : pointer\"
             onclick=\"st2Article(".$line["id"].")\"
             class='tagsPic' title='".__('SendTo2 ')."'>";
  }

  function getSendTo2() {
    $id = db_escape_string($_REQUEST['id']);

    $result = db_query("SELECT title, link
		      FROM ttrss_entries, ttrss_user_entries
		      WHERE id = '$id' AND ref_id = id AND owner_uid = " .$_SESSION['uid']);

    if (db_num_rows($result) != 0) {
      $title = truncate_string(strip_tags(db_fetch_result($result, 0, 'title')),
			       100, '...');
      $article_link = db_fetch_result($result, 0, 'link');
    }

    $st2_url = $this->host->get($this, "sendto2");

    print json_encode(array("title" => $title, "link" => $article_link,
			    "id" => $id, "st2url" => $st2_url));
  }
    function api_version() {
	return 2;
    }

}
?>
