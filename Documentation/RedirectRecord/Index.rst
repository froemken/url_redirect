.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../_IncludedDirectives.rst

Redirect record
===============

The URL redirect record is the most important record in this extension.

.. t3-field-list-table::
 :header-rows: 1
 - :Field:
         Field
   :Description:
         Description
 - :Field:
         use_reg_exp
   :Description:
         Use regular expressions to realize your redirect.

         Example:
         request_uri: ^\?id=(\d+)$
         target_uri: http://typo376:8080/index.php?id=${1}
         Redirects all requests without index.php to an URI with
         index.php while keeping the page UID.
         Access first subpattern (\d+) with ${1} in target_uri.
         Access second subpattern with ${2} in target_uri and so on.

         Be carefully, with that option you can create unlimited redirects very easily.
 - :Field:
         domain
   :Description:
         Select domain, for which this record should be valid
 - :Field:
         request_uri
   :Description:
         The path and query part of URI that has to match. Instead of
         http://www.typo3lexikon.de/lesson/chapter1.html?query=bla
         you have to insert /lesson/chapter1.html?query=bla
         Without activated use_reg_exp option you should prepend a slash.
 - :Field:
         target_uri
   :Description:
         Full target URI. Including domain and scheme:
         https://www.typo3lexikon.de/lesson/chapter1.html?query=bla
 - :Field:
         http_status
   :Description:
         Define your preferred HTTP status. Default is 301.

