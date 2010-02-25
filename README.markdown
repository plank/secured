SSL Component
=============

This Secured component allows you to programmatically define which controller actions
should be served under a secure HTTPS connection.

Most of the time, this functionality is acheived through judicous use of rewrite/redirect
rules in your webserver (Apache, Lighhtpd, Nginx, etc.). Defining this logic in your webserver
is advantageous - an incorrect request never hits your application code, and it could be handled
by a proxy to ensure that your application servers are not bothered with requests they cannot serve.

However, there are cases where the programmatic definition of which controllers & actions
is desirable - 1) during development, 2) sitations where you do not have access to `.htaccess`
or the webserver configuration, 3) when static definitions of secured URLs do not suffice.

This very simple component attempts to address the above issues, and allows for a very intuitive
and straightforward configuration. Here is a sample config, where we desire that the `login` action
within the `users` controller to be served via HTTPS, and _all_ `store` actions to be served via HTTPS:


    /* app_controller.php */

    /**
     * Components for all controllers.
     *
     * @var array Components, with optional configuration directives.
     */
    public $components = array(
    	'Secured.Ssl' => array(
    		'secured' => array(
    			'users' => 'login',
    			'store' => '*'
    		)
    	),
    	'autoRedirect' => false  // temporarily disable this component
    );

Requirements:
-------------
 - A valid and properly installed/configured SSL certificate.
 - This component.
 - CakePHP 1.2/1.3 (the latter has not been tested, but should work without issue).

Things that need to be done:
----------------------------
 - Test cases
 - Add more configurability for specifying the protocol (defaults to http/https) as well
   as the subdomain (defaults to 'www' for secured connections).