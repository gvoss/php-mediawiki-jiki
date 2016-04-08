<?php
if(!defined("MEDIAWIKI"))
{
   echo("This file is an extension to the MediaWiki software and cannot be used standalone.\n");
   die(1);
}
/**
 * utility functions for JIRA
 */
class JIRA
{
  /**
   * get the URL for an issue
   *
   * @param string host the hostname of the JIRA instance
   * @param string key the key of the issue
   */
  function getIssueURL($host,$key)
  {
    return "{$host}/browse/{$key}";
  }
  /**
   * get the URL to the filter page using a JQL statement
   *
   * @param string host the hostname of the JIRA instance
   * @param string jql the jql statement
   */
  function getFilterURL($host,$jql)
  {
    return "{$host}/issues/?jql=".urlencode($jql);
  }
}
