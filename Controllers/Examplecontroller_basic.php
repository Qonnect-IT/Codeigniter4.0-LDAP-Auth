<?php
  
namespace App\Controllers;
use App\Libraries\Ldap;
use CodeIgniter\HTTP\IncomingRequest;

class Examplecontroller extends BaseController {

  protected $ldap;

  public function __construct()
  {
    $this->ldap = new Ldap();
  }

  public function index()
  {
    $request = service('request');

    $username = "test";
    $password = "test";

    $this->ldap->login($username, $password);
  }
}
