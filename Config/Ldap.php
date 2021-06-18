<?php
  
namespace Config;

use CodeIgniter\Config\BaseConfig;

class Ldap extends BaseConfig {

    /* LDAP Server */
    public $serverlist = array(
      "server1.testdomain.org",
    );

    /* Use TLS for bind */
    public $useTLS = True;

    /* Port numbers */
    public $ports = array(
      "default" => 389,
      "TLS" => 636,
    );

    /* Bind User, if entered, we perform authenticated binds */
    public $binduser = "";

    /* Bind Password, if entered, we perform authenticated binds */
    public $bindpassword = "";

    /* Base DN */
    public $basedn = "DC=domain,DC=tld";

    /* Username Attribute
     * uid for most Linux LDAP servers
     * sAMAccountName for most Windows LDAP servers
     */
    public $usernameattr = "sAMAccountName";

    /* Group Membership Attribute
     * cn for most Linux LDAP servers
     * memberof or primarygroupid for most Windows LDAP servers
     */
    public $groupmembershipattr = "memberof";

    /* Role Mapping as Array
     * Role mapping is used to define privilege level in the application
     * 15 is highed number, 0 lowest
     * NOTICE, First match determined privilege level if multi matches apply
     */
    public $rolemappingarray = array(
      15 => "CN=TestGroup,OU=Security Groups,DC=domain,DC=tld",
    );
}

?>
