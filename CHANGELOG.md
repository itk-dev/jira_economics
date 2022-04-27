# CHANGELOG

## 1.9.0

* Merged: https://github.com/itk-dev/jira_economics/pull/109
  - Changed project billing to look for 'status=lukket' instead of 'status=done'.
  - Added new fields to external invoices.
  - Fixed invoice preview table styling.
  - Added replace to remove (DEVSUPP-*) texts for ProjectBilling.

## 1.8.0

* Changed color scheme to match 2 week sprints.

## 1.6.0

* Changed spreadsheet export to use temporary files.
* Expense: Made description optional.
* Expense: Added select2 to selects.
* Expense: Use previous project selection

## 1.5.0

* Upgraded to Symfony 4.4.*.
* Fixed billing app naming.
* Removed requirement for description for invoice entries when recording invoice.
* Moved sidebar to separate file.
* Fixed select all recorded invoices.

## 1.4.0

* Added permissions checks to Jira apps.
* Updated menus to reflect available apps in both Jira and portal areas.

## 1.3.2

* Billing: Fixed date filter for worklogs.
* Billing: Removed description.
* Billing: Added product max length of 40 characters.
* Billing: Stop marking worklogs as billed in Jira.
* ProjectBilling: Only include INTERN or EKSTERN accounts in project billing.
