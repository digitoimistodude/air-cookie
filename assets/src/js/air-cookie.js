/*
* @Author: Timi Wahalahti
* @Date:   2021-08-10 14:12:02
* @Last Modified by:   Timi Wahalahti
* @Last Modified time: 2021-08-20 14:48:26
*/

console.log('hello');

window.addEventListener( 'load', function () {
  var cc = initCookieConsent();

  airCookieSettings.onAccept = function() {
    airCookieOnAccept;
  }

  cc.run( airCookieSettings );
});
