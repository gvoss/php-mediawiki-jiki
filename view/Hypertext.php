<?php
if(!defined("MEDIAWIKI"))
{
   echo("This file is an extension to the MediaWiki software and cannot be used standalone.\n");
   die(1);
}
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
  function getRenderedView(&$data,$args=array())
  {
    $renderedView = "";
    foreach($data["data"] as $issue)
    {
      $renderedView.= "<img title=\"".$issue["fields"]["issuetype"]["name"].": ".$issue["fields"]["issuetype"]["description"]."\" src=\"".$issue["fields"]["issuetype"]["iconUrl"]."\"/> ";
      $renderedView.= "<strong>{$issue["key"]}</strong> ";
      $renderedView.= "<a href=\"".JIRA::getIssueURL($data["host"],$issue["key"])."\" target=\"_BLANK\">{$issue["fields"]["summary"]}</a> ";
      $renderedView.= "({$issue["fields"]["status"]["name"]}) ";
      if(isset($issue["fixVersions"]))
      {
        foreach($issue["fixVersions"] as $issueFixVersion)
        {
          $renderedView.= "{$issueFixVersion["name"]} ";
          if(isset($issueFixVersion["releaseDate"])&&isset($issueFixVersion["released"])&&$issueFixVersion["released"]===true)
          {
            $renderedView.= "({$issueFixVersion["releaseDate"]}) ";
          }
        }
      }
      if(isset($args["renderDetails"])&&$args["renderDetails"]===true)
      {
        $renderedView.= "<br/><div style=\"text-indent: 20px;\">{$issue["renderedFields"]["description"]}</div>";
      }
      $renderedView.= "<br/>";
    }
    if(isset($args["renderLink"])&&$args["renderLink"]===true)
    {
      $renderedView.= "<a href =\"".JIRA::getFilterURL($data["host"],$data["jql"])."\" target=\"_BLANK\">view in JIRA</a>";
    }
    return $renderedView;
  }
}
