<?php
if(!defined("MEDIAWIKI"))
{
   echo("This file is an extension to the MediaWiki software and cannot be used standalone.\n");
   die(1);
}
#define some css to handle style of gems
#TODO: figure out if mediawiki supports some kind of templating
define("JIKI_VIEW_HTML_GREY","background-color:#4a6785;border-color:#4a6785;color:#FFFFFF;border-radius:3px;padding:1px 3px;");
define("JIKI_VIEW_HTML_ORANGE","background-color:#ffd351;border-color:#ffd351;color:#594300;border-radius:3px;padding:1px 3px;");
define("JIKI_VIEW_HTML_GREEN","background-color:#14892c;border-color:#14892c;color:#FFFFFF;border-radius:3px;padding:1px 3px;");
/**
 * an HTML renderer for JIRA data
 */
class Hypertext
{
  /**
   * get the rendered view in HTML format
   *
   * @param array data the array of data to be rendered
   */
  public static function getRenderedView($format,&$data,$args=array())
  {
    global $jikiRenderDefaults;
    $rView = "";#container for the whole view
    $rDefATarget = "_blank";#default for anchor target
    if(isset($jikiRenderDefaults))#handle render configurations
    {
      if(isset($jikiRenderDefaults["html"]["target"]))
      {
        #TODO: also allow setting this inline
        $rDefATarget = "{$jikiRenderDefaults["html"]["target"]}";
      }
    }
    foreach($data["data"] as $issue)
    {
      # TODO: improve this sanitizing
      $issue["fields"]["summary"] = htmlspecialchars($issue["fields"]["summary"]);
      $issue["fields"]["description"] = htmlspecialchars($issue["renderedFields"]["description"]);
      $rIssue = "";#container for an issue
      $rIssue.= self::wrapField($format,"<img title=\"".$issue["fields"]["issuetype"]["name"].": ".$issue["fields"]["issuetype"]["description"]."\" src=\"".$issue["fields"]["issuetype"]["iconUrl"]."\"/>");
      $rIssue.= self::wrapField($format,"<strong>{$issue["key"]}</strong>");
      $rIssue.= self::wrapField($format,"<a href=\"".JIRA::getIssueURL($data["host"],$issue["key"])."\" target=\"{$rDefATarget}\">{$issue["fields"]["summary"]}</a>");
      $statusStyle = JIKI_VIEW_HTML_GREY;
      if(isset($issue["fields"]["status"]["statusCategory"]))#JIRA provides color
      {
        #TODO: identify how to use ids for status names or just configure this
        switch(strtolower($issue["fields"]["status"]["statusCategory"]["colorName"]))
        {
          case "green":
          {
            $statusStyle = JIKI_VIEW_HTML_GREEN;
            break;
          }
          case "yellow":
          {
            $statusStyle = JIKI_VIEW_HTML_ORANGE;
            break;
          }
          default:
          {
            $statusStyle = JIKI_VIEW_HTML_GREY;
            break;
          }
        }
        $rIssue.= self::wrapField($format,"<span style=\"{$statusStyle}\">{$issue["fields"]["status"]["name"]}</span>");
      }
      else#color not found
      {
        $rIssue.= self::wrapField($format,"({$issue["fields"]["status"]["name"]})");
      }
      #if(isset($issue["fixVersions"]))#TODO:refactor this
      #{
      #  foreach($issue["fixVersions"] as $issueFixVersion)
      #  {
      #    $renderedView.= "{$issueFixVersion["name"]} ";
      #    if(isset($issueFixVersion["releaseDate"])&&isset($issueFixVersion["released"])&&$issueFixVersion["released"]===true)
      #    {
      #      $renderedView.= "({$issueFixVersion["releaseDate"]}) ";
      #    }
      #  }
      #}
      if(isset($args["renderDetails"])&&$args["renderDetails"]===true)
      {
        $rIssue.= self::wrapField($format,"<br/><div style=\"text-indent: 20px;\">{$issue["renderedFields"]["description"]}</div>");
      }
      $rView.= self::wrapIssue($format,$rIssue);
    }
    $rView = self::wrapContainer($format,$rView);
    if(isset($args["renderLink"])&&$args["renderLink"]===true&&sizeof($data["data"])>1)#show link to JIRA
    {
      $rView.= "<a href =\"".JIRA::getFilterURL($data["host"],$data["jql"])."\" target=\"{$rDefATarget}\">view in JIRA</a>";
    }
    return $rView;
  }
  /**
   * wrapField - wrap an individual field with markup
   *
   * @param format the format of the html
   * @param markup the markup to wrap
   */
  private static function wrapField($format,$markup)
  {
    switch($format)
    {
      case "html.table":
      {
        if($markup===""){$markup="&nbsp;";}#cleanup
        return "<td>{$markup}</td>";
        break;
      }
      default:
      {
        return "{$markup} ";
        break;
      }
    }
  }
  /**
   * wrapField - wrap an individual issue with markup
   *
   * @param format the format of the html
   * @param markup the markup to wrap
   */
  private static function wrapIssue($format,$markup)
  {
    switch($format)
    {
      case "html.table":
      {
        return "<tr>{$markup}<tr/>";
        break;
      }
      case "html.bullets":
      case "html.numbered":
      {
        return "<li>{$markup}</li>";
      }
      default:
      {
        return "{$markup}<br/>";
        break;
      }
    }
  }
  /**
   * wrapField - wrap a set of issues with markup
   *
   * @param format the format of the html
   * @param markup the markup to wrap
   */
  private static function wrapContainer($format,$markup)
  {
    switch($format)
    {
      case "html.table":
      {
        return "<table><tbody>{$markup}</tbody></table>";
        break;
      }
      case "html.bullets":
      {
        return "<ul>{$markup}</ul>";
        break;
      }
      case "html.numbered":
      {
        return "<ol>{$markup}</ol>";
        break;
      }
      default:
      {
        return "{$markup}";
        break;
      }
    }
  }
}
