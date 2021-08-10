/*
* @Author: Timi Wahalahti
* @Date:   2021-08-10 14:12:02
* @Last Modified by:   Timi Wahalahti
* @Last Modified time: 2021-08-10 17:51:58
*/

console.log('hello');

window.addEventListener( 'load', function () {
  var cc = initCookieConsent();

  cc.run({
    theme_css: airCookie.assets.css,
    autorun : true,
    delay : 0,
    current_lang : airCookie.settings.current_lang,
    languages: airCookie.translations,
    gui_options: {
      consent_modal: {
        layout: 'box',
        position: 'bottom left',
      }
    }
  });
});
