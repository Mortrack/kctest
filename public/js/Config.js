let APP_ENV = true,
    APP_URL,
    URL_Home = '',
    URL_Userlist = 'Userlist/index',
    URL_CloseSession = 'Userlist/logout';

if (APP_ENV) {
    APP_URL = 'http://localhost/';
}
if (!APP_ENV) {
    APP_URL = '';
}