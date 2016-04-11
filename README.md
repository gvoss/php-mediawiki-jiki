JIKI
====
Embed a list of issues from a JIRA instance into a Mediawiki wiki page.
Configuration
=============
In LocalSettings specify the JIRA Hostname:
```php
$jikiJiraHost     = "https://jira.atlassian.com";
```
Specify the JIRA User Name:
```php
$jikiJiraUser     = "user@example.com";
```
Specify the JIRA User Password:
```php
$jikiJiraPassword = "ILoveC@ts";
```
Specify any custom fields you have in your JIRA:
```php
$jikiExtraParams = array
  (
    array("param"=>"myParamNameInWiki","jql"=>"\"My Field Name in JIRA\""),
  );
```
Basic Use
=========
In any wiki page include the following tag:
```
<jira></jira>
```
Filtering
=========
As long as the field is a basic JIRA field or you have configured it in LocalSettings:
```
<jira project="GO,FP,HELP"></jira>
```
```
<jira>text to search for</jira>
```
```
<jira project="GO" reporter="gvoss"></jira>
```
Or for an advanced user or where the JIKI implementation of JQL is insufficient:
```
<jira jql="project = GO AND reporter in (gvoss)"></jira>
```
