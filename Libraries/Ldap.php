<?php namespace App\Libraries;

class Ldap
{
  /* Initialize Class */
  function __construct() {
    $this->check_dependencies();
    $this->config = new \Config\Ldap();
  }

  /* Check Class Dependencies */
  function check_dependencies() {
    if(!extension_loaded('ldap')) {
      throw new \Exception("PHP Ldap Module is not installed, Module is required for this class");
    }
  }

  /* Login into LDAP */
  function login($username, $password) {

    $userinfo = $this->__authenticate($username, $password);

    if(!$userinfo) {
      return FALSE;
    } else {
      return $userinfo;
    }
  }


  /* Internal Authentication function */
  function __authenticate($username, $password)
  {
    if($this->config->useTLS) {
      $ldapprefix = "ldaps://";
      $ldapport = $this->config->ports["TLS"];
    } else {
      $ldapprefix = "ldap://";
      $ldapport = $this->config->ports["default"];
    }

    foreach($this->config->serverlist as $server) {
      $ldapconnectionstring = $ldapprefix . $server . ":" . $ldapport;

      $this->ldapconnection = ldap_connect($ldapconnectionstring);
      if($this->ldapconnection) {
        $this->ldapserver = $ldapconnectionstring;
        break;
      }else {
        throw new \Exception("PHP LDAP Module: Error connecting to " . $ldapconnectionstring);
      }
    }

    /* Check if LDAP Connection is made succesfully */
    if(!$this->ldapconnection)
    {
      throw new \Exception("PHP LDAP Module: No LDAP Connection was established");
    }

    /* LDAP Connection is succesfully connected */
    ldap_set_option($this->ldapconnection, LDAP_OPT_REFERRALS, 0);
    ldap_set_option($this->ldapconnection, LDAP_OPT_PROTOCOL_VERSION, 3);

    if($this->config->binduser && $this->config->bindpassword)
    {
      $this->ldapbind = ldap_bind($this->ldapconnection, $this->config->binduser, $this->config->bindpassword);
      $this->ldapanonbind = false;
    } else {
      $this->ldapbind = ldap_bind($this->ldapconnection);
      $this->ldapanonbind = true;
    }

    /* If bind is not succesfully, we are not able to authenticate further */
    if(!$this->ldapbind) {
      if($this->ldapanonbind) {
        throw new \Exception("PHP LDAP Module: LDAP Bind failed with anonymous bind");
      } else {
        throw new \Exception("PHP LDAP Module: LDAP Bind failed with bind user");
      }
    }

    /* Bind is succesfull at this moment, performing DN Lookup*/
    $ldapfilter = '('.$this->config->usernameattr.'='.$username.')';
    $ldapsearch = ldap_search($this->ldapconnection, $this->config->basedn, $ldapfilter, array('dn', $this->config->usernameattr, 'cn'));

    $entries = ldap_get_entries($this->ldapconnection, $ldapsearch);

    $this->binddn = $entries[0]['dn'];

    $this->ldapbind = @ldap_bind($this->ldapconnection, $this->binddn, $password);
    if(!$this->ldapbind) {
      throw new \Exception("PHP LDAP Module: LDAP Authenticaton failed for user: " . $this->binddn);
      return FALSE;
    }

    $userdata['cn'] = $entries[0]['cn'][0];
    $userdata['dn'] = stripslashes($entries[0]['dn']);
    $userdata['id'] = $entries[0][strtolower($this->config->usernameattr)][0];

    $userdata['role'] = $this->__getuserrole($userdata['id']);
    return $userdata;
  }


  /* Internal User Role mapping function */
  function __getuserrole($username)
  {
    $ldapfilter = '('.$this->config->usernameattr.'='.$username.')';
    $ldapsearch = ldap_search($this->ldapconnection, $this->config->basedn, $ldapfilter, array($this->config->groupmembershipattr));

    if(!$ldapsearch) {
      throw new \Exception("PHP LDAP Module: LDAP Group Lookup failed for user: " . $username);
      return FALSE;
    }

    $entries = ldap_get_entries($this->ldapconnection, $ldapsearch);

    if($entries['count'] != 0) {
      for($i = 0; $i < $entries['count']; $i++) {
        $role = array_search($entries[$i][$this->config->groupmembershipattr][0], $this->config->rolemappingarray);
        if($role !== FALSE) {
          return $role;
        }
      }
    }
    return FALSE;
  }


  /* This function is used for troubleshooting */
  function echo_config()
  {
    return $this->config;
  }
}

?>
