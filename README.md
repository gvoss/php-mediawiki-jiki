JIKI
====
Embed a list of issues from a JIRA instance into a Mediawiki wiki page.
Configuration
=============
Specify the JIRA Hostname:
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
