### 1.2.4: 2023-02-12

* Change changelog format
* Fix bunch of typos
* Update PHP_Codesniffer rule excludes
* Add .editorconfig
* Use $wpdb->prepare() for SQL queries
* Check for $data->visitorid and $data->revision before recording them to database

### 1.2.3: 2023-10-27

* Support for activating all categories with `data-aircookie-accept` using value `all`

### 1.2.2: 2023-08-03

* Update CookieConsent CSS to version 2.9.1
* Filters to modify inline scripts

### 1.2.1: 2023-06-28

* Update CookieConsent to version 2.9.1

### 1.2.0: 2022-08-24

* Update CookieConsent to version 2.8.5
* Elements with `data-aircookie-accept` attribute are listened for clicks, causing the category specified in the value to be accepted
* Elements with `data-aircookie-remove-on` attribute are removed when the category specified is accepted

### 1.1.4: 2021-12-14

* Fix JS loading issue by changing the inject priority

### 1.1.3: 2021-12-08

* Fix JS error, undefined manager after embed group is accepted
* Fix JS error, undefined CC element after consent

### 1.1.2: 2021-11-01

* Fix string register if Polylang is not active

### 1.1.1:  2021-10-07

* Cookie expiry time saving

### 1.1.0: 2021-10-07

* Run the cookie category JS also when user changes the cookie settings (CookieConsent onChange event)
* Updated CookieConsent to version 2.6.0
* Save the visitor id on main cookie
* Functionality related to handling the visitor id in separate cookie

### 1.0.0: 2021-09-29

* First release
