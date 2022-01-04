<?php
defined('BASEPATH') or exit('No direct script access allowed');

$route['default_controller'] = 'RestController';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['login'] = 'RestController/login';
$route['register'] = 'RestController/register';

$route['getWorkspace'] = 'RestController/getWorkspace';
$route['viewWorkspace'] = 'RestController/viewWorkspace';
$route['setWorkspace'] = 'RestController/setWorkspace';
$route['addTask'] = 'RestController/addTask';

$route['getAssigned'] = 'RestController/getAssigned';
$route['viewAssigned'] = 'RestController/viewAssigned';

$route['setTeam'] = 'RestController/setTeam';
$route['getTeam'] = 'RestController/getTeam';
$route['getRequest'] = 'RestController/getRequest';
$route['addMember'] = 'RestController/addMember';
$route['approveMember'] = 'RestController/approveMember';
$route['removeMember'] = 'RestController/removeMember';
$route['searchUser'] = 'RestController/searchUser';
