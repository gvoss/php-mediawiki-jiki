<?php
if(!defined("MEDIAWIKI"))
{
   echo("This file is an extension to the MediaWiki software and cannot be used standalone.\n");
   die(1);
}
define("JIKI_JQL_COND_ANDIN","andIn");
define("JIKI_JQL_COND_NOTIN","notIn");
define("JIKI_JQL_COND_IS","is");
define("JIKI_JQL_COND_ISNOT","isNot");
/**
 * utility class for JQL related logic
 */
class JQL
{
  /**
   * build a JQL statement based on arguments from the wiki
   *
   * @param string input the value inside the <jira>value</jira> tag
   * @param array args the arguments provided on the <jira> tag
   * @param array params the acceptable arguments to consider on the <jira> tag
   */
  function getJQL(&$input,&$args,$params)
  {
    $jql = "";
    $conditions = array();
    foreach($params as $param)
    {
      $argParam = "";#passed by wiki user in arguments
      $jqlParam = "";#passed to JIRA in jql
      if(is_array($param))
      {
        $argParam = "{$param["param"]}";
        $jqlParam = "{$param["jql"]}";
      }
      else
      {
        $argParam = $jqlParam = "{$param}";
      }
      if(!isset($args["{$argParam}"]))#this parameter is not set
      {
        continue;
      }
      $argConditions = explode(",","{$args["{$argParam}"]}");
      $jqlConditions = array
        (
          JIKI_JQL_COND_ANDIN => array(),
          JIKI_JQL_COND_NOTIN => array(),
        );
      foreach($argConditions as $anArgCondition)
      {
        $anArgCondition = trim($anArgCondition);
        $anArgOperator = substr($anArgCondition,0,1);

        switch($anArgOperator)
        {
          case "!":#NOT condition
          {
            $anArgCondition = substr($anArgCondition,1);
            array_push($jqlConditions[JIKI_JQL_COND_NOTIN],self::escapeCondition($anArgCondition));
            break;
          }
          default:
          {
            array_push($jqlConditions[JIKI_JQL_COND_ANDIN],self::escapeCondition($anArgCondition));
            break;
          }
        }
      }
      foreach($jqlConditions as $type => $value)
      {
        if(sizeof($value)===0)
        {
          continue;
        }
        switch($type)
        {
          case JIKI_JQL_COND_ANDIN:
          {
            array_push($conditions,"({$jqlParam} in (".implode(",",$value)."))");
            break;
          }
          case JIKI_JQL_COND_NOTIN:
          {
            array_push($conditions,"({$jqlParam} not in (".implode(",",$value)."))");
            break;
          }
        }
      }
    }
    if(isset($input)&&"{$input}"!=="")
    {
      array_push($conditions,"(text ~ \"{$input}\")");#TODO: test when a crafty user shoves in a "
    }
    if(!isset($args["jikiAllowSubtasks"]))
    {
      array_push($conditions,"(issuetype not in subTaskIssueTypes())");
    }
    $jql.= implode(" AND ",$conditions);
    if(!isset($args["orderby"]))
    {
      $args["orderby"] = "createdDate DESC";
    }
    if(isset($args["orderby"]))#handle Order By
    {
      if($jql!=="")
      {
        $jql.= " ";
      }
      $jql.= "ORDER BY {$args["orderby"]}";
    }
    return $jql;
  }
  private function escapeCondition($condition)
  {
    if(preg_match("/\s/",$condition)>0)
    {
      return "\"$condition\"";
    }
  }
}
