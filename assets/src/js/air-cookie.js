/*
* @Author: Timi Wahalahti
* @Date:   2021-08-10 14:12:02
* @Last Modified by:   Timi Wahalahti
* @Last Modified time: 2021-08-17 10:58:25
*/

console.log('hello');

window.addEventListener( 'load', function () {
  var cc = initCookieConsent();

  cc.run( airCookieSettings );
});
