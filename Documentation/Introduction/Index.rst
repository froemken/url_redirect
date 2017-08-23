.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../_IncludedDirectives.rst

.. _introduction:

============
Introduction
============

Releases
--------

* Currently the source code is available at `Github <https://github.com/froemken/url_redirect>`_
* I have tagged all versions at Github and added a composer.json,
  so you can install different versions of |extension_key| with composer.

Bugs and Known Issues
---------------------

If you found a bug, it would be cool if you notify me
about that over the `Bug Tracker <https://github.com/froemken/url_redirect/issues>`_ of Github.

What does it do?
----------------

Do you know the 301 redirects of your .htaccess? Do you know how
heavy it is to manage more than 1000 of these lines? Do you know how heavy it is
to find the other .htaccess configuration between all these redirect lines?
You don't want to give your editors SSH access to manage redirect in .htaccess?

If you don't have theses problems, you don't need this extension.

But, if you know that problem, this extension is a little helper
to manage redirects in TYPO3 Backend. All redirects are configured as database records
and can be managed over the backend module "URI Redirect". As an admin
you can manage these records also over pid 0.

You can change HTTP status of each redirect. The only limit is: Your HTTP status
must be configured as constant in TYPO3s HttpUtility. I have added a selectbox in
backend module with all allowed HTTP status.

**Little problem:**

A redirect, realized with this extension, will **never** be as fast as a
redirect realized with .htaccess. The additional timing seems to be within 20 - 60
milliseconds. Please test theses timings on your own machine and decide, if this extension
will match your needs.

You can not use that extension to redirect from HTTP to HTTPS or vise versa.
