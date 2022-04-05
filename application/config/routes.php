<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

$route['default_controller'] = "login";
$route['404_override'] = 'error_404';
$route['translate_uri_dashes'] = FALSE;


/*********** USER DEFINED ROUTES *******************/

$route['loginMe'] = 'login/loginMe';
$route['dashboard'] = 'user';
$route['logout'] = 'user/logout';
$route['servers'] = 'server/listing';
$route['servers/(:num)'] = "server/listing/$1";
$route['proxy'] = 'proxy/listing';
$route['proxy/(:num)'] = "proxy/listing/$1";
$route['schedule'] = 'schedule/listing';
$route['schedule/(:num)'] = "schedule/listing/$1";
$route['schedule-detail'] = "schedule/detail";
$route['schedule-detail/(:num)'] = "schedule/detail/$1";
$route['client'] = 'client/listing';
$route['client/(:num)'] = "client/listing/$1";
$route['account'] = 'account/listing';
$route['account/(:num)'] = "account/listing/$1";
$route['app'] = 'app/listing';
$route['app/(:num)'] = "app/listing/$1";
$route['gmail-auth/add'] = 'gmailauth/add';
$route['gmail-auth'] = 'gmailauth/listing';
$route['gmail-auth/(:num)'] = "gmailauth/listing/$1";
$route['gmail-auth/delete'] = 'gmailauth/delete';
$route['userListing'] = 'user/userListing';
$route['userListing/(:num)'] = "user/userListing/$1";
$route['addNew'] = "user/addNew";
$route['addNewUser'] = "user/addNewUser";
$route['editOld'] = "user/editOld";
$route['editOld/(:num)'] = "user/editOld/$1";
$route['editUser'] = "user/editUser";
$route['deleteUser'] = "user/deleteUser";
$route['profile'] = "user/profile";
$route['profile/(:any)'] = "user/profile/$1";
$route['profileUpdate'] = "user/profileUpdate";
$route['profileUpdate/(:any)'] = "user/profileUpdate/$1";

$route['loadChangePass'] = "user/loadChangePass";
$route['changePassword'] = "user/changePassword";
$route['changePassword/(:any)'] = "user/changePassword/$1";
$route['pageNotFound'] = "user/pageNotFound";
// $route['checkEmailExists'] = "app/checkEmailExists";
$route['login-history'] = "user/loginHistoy";
$route['login-history/(:num)'] = "user/loginHistoy/$1";
$route['login-history/(:num)/(:num)'] = "user/loginHistoy/$1/$2";

$route['forgotPassword'] = "login/forgotPassword";
$route['resetPasswordUser'] = "login/resetPasswordUser";
$route['resetPasswordConfirmUser'] = "login/resetPasswordConfirmUser";
$route['resetPasswordConfirmUser/(:any)'] = "login/resetPasswordConfirmUser/$1";
$route['resetPasswordConfirmUser/(:any)/(:any)'] = "login/resetPasswordConfirmUser/$1/$2";
$route['createPasswordUser'] = "login/createPasswordUser";

$route['roleListing'] = "roles/roleListing";
$route['roleListing/(:num)'] = "roles/roleListing/$1";
$route['roleListing/(:num)/(:num)'] = "roles/roleListing/$1/$2";

$route['comment'] = 'comment/listing';
$route['comment/(:num)'] = "comment/listing/$1";
$route['schedule-comment-detail'] = "schedule/commentdetail";
$route['schedule-comment-detail/(:num)'] = "schedule/commentdetail/$1";
$route['schedule-like-detail'] = "schedule/likedetail";
$route['schedule-like-detail/(:num)'] = "schedule/likedetail/$1";
$route['schedule-subscribe-detail'] = "schedule/subscribedetail";
$route['schedule-subscribe-detail/(:num)'] = "schedule/subscribedetail/$1";

$route['proxyapi'] = 'proxyapi';
$route['scheduleapi'] = 'scheduleapi';

$route['authorization'] = 'authorization';
$route['oauth2callback'] = 'oauth2callback';
$route['yt'] = 'yt';

/* End of file routes.php */
/* Location: ./application/config/routes.php */
