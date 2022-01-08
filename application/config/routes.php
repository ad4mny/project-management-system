<?php
defined('BASEPATH') or exit('No direct script access allowed');

$route['default_controller'] = 'RestController';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['login'] = 'RestController/login';
$route['register'] = 'RestController/register';

$route['getWorkspace'] = 'RestController/getWorkspace';
$route['viewWorkspace'] = 'RestController/viewWorkspace';
$route['removeWorkspace'] = 'RestController/removeWorkspace';
$route['setWorkspace'] = 'RestController/setWorkspace';
$route['addTask'] = 'RestController/addTask';

$route['getAssigned'] = 'RestController/getAssigned';
$route['viewAssigned'] = 'RestController/viewAssigned';

$route['getFriendList'] = 'RestController/getFriendList';
$route['getFriendRequest'] = 'RestController/getFriendRequest';
$route['addFriend'] = 'RestController/addFriend';
$route['approveFriend'] = 'RestController/approveFriend';
$route['removeFriend'] = 'RestController/removeFriend';
$route['searchUser'] = 'RestController/searchUser';

$route['updateProfile'] = 'RestController/updateProfile';