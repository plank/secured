<?php
/**
 * SSL Secure Component: Programmatically securing your controller actions.
 *
 * @copyright     Copyright 2010, Plank Design (http://plankdesign.com)
 * @license       http://opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * SSL Component
 *
 * This SSL component allows you to programmatically define which controller actions
 * should be served only under a secure HTTPS connection.
 *
 * Most of the time, this functionality is acheived through judicous use of rewrite/redirect
 * rules in your webserver (Apache, Lighhtpd, Nginx, etc.). Defining this logic in your webserver
 * is advantageous - an incorrect request never hits your application code, and it could be handled
 * by a proxy to ensure that your application servers are not bothered with requests they cannot serve.
 *
 * However, there are cases where the programmatic definition of which controllers & actions
 * is desirable - 1) during development, 2) sitations where you do not have access to .htaccess
 * or the webserver configuration, 3) when static definitions of secured URLs do not suffice.
 *
 * This very simple component attempts to address the above issues. See the README for a sample
 * configuration.
 *
 * @todo Test cases
 */

class SslComponent extends Object {

	/**
	 * Associative array of controllers & actions that need
	 * to be served from HTTPS instead of regular HTTP.
	 *
	 * @var array
	 */
	public $secured = array();

	/**
	 * If the current request comes through SSL,
	 * this variable is set to true.
	 *
	 * @var boolean True if request was made through SSL, false otherwise.
	 */
	public $https = false;

    /**
     * Whether or not to secure the entire admin route.
     * Can take either string with the prefix, or an array of the prefixii?
     *
     * @var string || array
     **/
    public $prefixes = array();

	/**
	 * Use this component if this variable is set to true.
	 *
	 * @var boolean Redirect if this is true, otherwise do nothing.
	 */
	public $autoRedirect = true;

	/**
	 * Component initialize method.
	 * Is called before the controller beforeFilter method. All local component initialization
	 * is done here.
	 *
	 * @param object $controller A reference to the controller which initialized this component.
	 * @param array $settings Optional component configurations.
	 * @return void
	 * @todo Perhaps move logic to startup() to allow more fine-grained programmatic control.
	 * @todo Change Configure::read('debug') check to a $this->autoRedirect check.
	 */
	public function initialize(&$controller, $settings = array()) {
		$this->controller = $controller;
		$this->_set($settings);

		if (env('HTTPS') === 'on' || env('HTTPS') === true) {
			$this->https = true;
		}

		if ($this->autoRedirect === true) {
			$secured = $this->ssled($this->controller->params);

			if ($secured && !$this->https) {
				$this->forceSSL();
			}
			elseif (!$secured && $this->https) {
				$this->forceNoSSL();
			}
		}
	}

	/**
	 * Determines whether the request (based on passed params)
	 * should be ssl'ed or not.
	 *
	 * @param array $params Parameters containing 'controller' and 'action'
	 * @return boolean True if request should be ssl'ed, false otherwise.
	 */
	public function ssled($params) {
        //Prefix Specific Check - allow securing of entire admin in one swoop
        if( !empty($this->prefixes) &&  !empty($params['prefix']) && (in_array($params['prefix'], (array)$this->prefixes)) ) {
            return true;
        }

		if (!array_key_exists($params['controller'], $this->secured)) {
			return false;
		}
		$actions = (array) $this->secured[$params['controller']];

		if ($actions === array('*')) {
			return true;
		}
		return (in_array($params['action'], $actions));
	}

	/**
	 * Redirects current request to be SSL secured
	 *
	 * @return void
	 * @todo Make protocol & subdomain ('https' & 'www' configurable)
	 * @todo allow conditional passing of server identifier
	 */
	public function forceSSL() {
		$server = env('SERVER_NAME');
		$this->controller->redirect("https://$server{$this->controller->here}");
	}

	/**
	 * Symmetric method to forceSSL, which redirects the current
	 * executing request to non-SSL.
	 *
	 * @return void
	 * @todo Make protocol & subdomain ('https' & 'www' configurable)
	 * @todo allow conditional passing of server identifier
	 */
	public function forceNoSSL() {
		$server = env('SERVER_NAME');
		$this->controller->redirect("http://$server{$this->controller->here}");
	}

}
?>