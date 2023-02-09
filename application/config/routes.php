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
|	https://codeigniter.com/userguide3/general/routing.html
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
$route['default_controller'] = 'Welcome/landing';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Auth
$route['login']                             = 'AuthController/vLogin';
$route['logout']                            = 'AuthController/logout';
$route['flogin']                            = 'AuthController/login';

// Notif
// TRAMSACTION
$route['notif/transaction']                 = 'NotificationController/getTransactionAll';
$route['notif/readTransaction/(:any)']      = 'NotificationController/readTransaction/$1';
$route['notif/readTransactionAll']          = 'NotificationController/readTransactionAll';
$route['notif/rAjxTransactionAll']          = 'NotificationController/ajxReadTransactionAll';
// -- DEBITNOTE
$route['notif/debitnote']                   = 'NotificationController/getDebitnoteAll';
$route['notif/readDebitnote/(:any)']        = 'NotificationController/readDebitnote/$1';
$route['notif/readDebitnoteAll/(:any)']    = 'NotificationController/readDebitnoteAll/$1';
$route['notif/rAjxDebitnoteAll']            = 'NotificationController/ajxReadDebitnoteAll';

// Welcome
$route['register']                          = 'Welcome/register';
$route['landing']                           = 'Welcome/landing';

// Dashboard
$route['dashboard']                         = 'DashboardController/vDashboard';

//Setting
$route['setting']                           = 'SettingController/vSetting';
$route['setting/store']                     = 'SettingController/store';
$route['setting/update']                    = 'SettingController/update';
$route['setting/destroy']                   = 'SettingController/destroy';

// Week
$route['week']                              = 'WeekController/vWeek';
$route['week/store']                        = 'WeekController/store';
$route['week/update']                       = 'WeekController/update';
$route['week/destroy']                      = 'WeekController/destroy';

// User
$route['user']                              = 'UserController/vUser';
$route['user/edit/(:any)']                  = 'UserController/vUserEdit/$1';
$route['user/store']                        = 'UserController/store';
$route['user/update']                       = 'UserController/update';
$route['user/reset-password']               = 'UserController/resetPassword';
$route['user/destroy']                      = 'UserController/destroy';
$route['user/verif']                        = 'UserController/verif';
$route['user/register']                     = 'UserController/register';
$route['user/changeActive']                 = 'UserController/changeActive';
$route['user/ajxGetData']                   = 'UserController/ajxGetData';

// Form
$route['form']                              = 'FormController/vForm';
$route['form/edit/(:any)']                  = 'FormController/vFormEdit/$1';
$route['form/store']                        = 'FormController/store';
$route['form/update']                       = 'FormController/update';
$route['form/destroy']                      = 'FormController/destroy';
$route['form/flow/(:any)']                  = 'FormController/vFlow/$1';
$route['form/flow/reset']                   = 'FormController/flowReset';
$route['form/deleteFlow']                   = 'FormController/deleteFlow';
$route['form/updateFlow']                   = 'FormController/updateFlow';
$route['form/editFlow']                     = 'FormController/editFlow';
$route['form/ajxGetData']                   = 'FormController/ajxGetData';

// Transaction
$route['transaction']                       = 'TransactionController/vTrans';
$route['transaction/approve']               = 'TransactionController/approve';
$route['transaction/reject']                = 'TransactionController/reject';
$route['transaction/ajxGetData']            = 'TransactionController/ajxGetData';

// DebitNote
$route['debitnote']                         = 'DebitNoteController/vDN';
$route['debitnote/generated']               = 'DebitNoteController/vDNGenerated';
$route['debitnote/approved']                = 'DebitNoteController/vDNApproved';
$route['debitnote/rejected']                = 'DebitNoteController/vDNRejected';
$route['debitnote/progress']                = 'DebitNoteController/vDNProgress';
$route['debitnote/overdue']                 = 'DebitNoteController/vDNOverdue';
$route['debitnote/finished']                = 'DebitNoteController/vDNFinished';
$route['debitnote/reversed']                = 'DebitNoteController/vDNReversed';
$route['debitnote/edit/(:any)']             = 'DebitNoteController/edit/$1';
$route['debitnote/update']                  = 'DebitNoteController/update';
$route['debitnote/dashboard']               = 'DebitNoteController/vDNMonitor';
$route['debitnote/store']                   = 'DebitNoteController/store';
$route['debitnote/downloadTemplate']        = 'DebitNoteController/downloadTemplate';
$route['debitnote/generateDN']              = 'DebitNoteController/generateDN';
$route['debitnote/generateMultiDN']         = 'DebitNoteController/generateMultiDN';
$route['debitnote/downloadMultiDN']         = 'DebitNoteController/downloadMultiDN';
$route['debitnote/finish']            	    = 'DebitNoteController/finish';
$route['debitnote/finishMulti']            	= 'DebitNoteController/finishMulti';
$route['debitnote/downloadPdf']             = 'DebitNoteController/downloadPdf';
$route['debitnote/destroyDN']               = 'DebitNoteController/destroyDN';
$route['debitnote/destroyMultiDN']          = 'DebitNoteController/destroyMultiDN';
$route['debitnote/reverseDN']               = 'DebitNoteController/reverseDN';
$route['debitnote/reverseMultiDN']          = 'DebitNoteController/reverseMultiDN';
$route['debitnote/monthlyDNChart']          = 'DebitNoteController/MonthlyDNChart';
$route['debitnote/paymentDNChart']          = 'DebitNoteController/PaymentDNChart';
$route['debitnote/monthlyTable']            = 'DebitNoteController/MonthlyTable';
$route['debitnote/yearlyTable']             = 'DebitNoteController/YearlyTable';
$route['debitnote/yearlyDetailTable']       = 'DebitNoteController/YearlyDetailTable';
$route['debitnote/downloadExcel']           = 'DebitNoteController/downloadExcel';
$route['debitnote/ajxGetData']              = 'DebitNoteController/ajxGetData';


// -- DebitNote Cron Job
$route['debitnote/updateProgress']          = 'DebitNoteCronJobController/updateProgress';
$route['debitnote/updateReport/(:any)']     = 'DebitNoteCronJobController/updateReport/$1';
$route['debitnote/updateYearActive']        = 'DebitNoteCronJobController/updateYearActive';
$route['debitnote/updateYearFinished']      = 'DebitNoteCronJobController/updateYearFinished';

// Emailing Cron Job
$route['email/sendEmail']                   = 'EmailingController/sendEmail';
$route['email/sendEmailMulti']              = 'EmailingController/sendEmailMulti';
$route['email/remindProgress/(:any)']       = 'EmailingController/paymentProgress/$1';
$route['email/remindOverdue/(:any)']        = 'EmailingController/paymentOverdue/$1';

// Nudge Controller
$route['nudge/dnd']     = 'NudgeController/dnd';
$route['nudge/tender']  = 'NudgeController/tender';

// Tenant Display
$route['tenant/(:any)/(:any)']              = 'TenantDisplayController/display/$1/$2';
$route['tenant/ajxStatus']                  = 'TenantDisplayController/ajxStatus';

//Snack
$route['snack/(:any)']                      = 'SnackController/vSnack/$1';



// ========= GA SYS 2 =========
$route['project/downloadCOP']                       = 'ProjectController/downloadCOP';
$route['project/downloadTemplate']                  = 'ProjectController/download';
$route['project/downloadFormTender/(:any)']         = 'ProjectController/downloadFormTender/$1';
$route['project/downloadFormContract/(:any)']       = 'ProjectController/downloadFormContract/$1';
$route['project/signed/(:any)']                     = 'ProjectController/signed/$1';