# Cloud Provider Control Panel
Cloud Computing 
Sommersemester 2021
# API Documentation

# Endpoints
- create.php - Create user
- login.php - Login
- provider.php - List providers
- token.php - Manage provider API Keys
- sshkey.php - Manage SSH Keys
- script.php - Manage startup shell scripts
- location.php - Get server locations of providers
- plan.php - Get server plans of providers
- os.php - Get server operating systems of providers
- create.php - Create server(s)
- delete.php - Delete a server
- server.php - List server(s)
- control.php - Boot/reboot/shutdown a server

**Create User**
----
  Creates a new user in the database

* **URL**

  /register.php

* **Method:**

  `POST`
* **URL Params**

  None
  
*  **Data Params**

   **Required:**
 
   `username=[string]`
   `email=[string]`
   `password=[string]`


* **Success Response:**

  * **Code:** 200 OK<br />
    **Content:** `{
    "error": false,
    "message": "User was successfully registered"
}`
 
* **Error Response:**

  * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": ""}`


* **Sample Call:**

  ```curl
  curl --location --request POST 'http://localhost:8001/api/register.php' \
--header 'Content-Type: application/json' \
--form 'username="test"' \
--form 'email="test@test.de"' \
--form 'password="123456"'
  ```

**Login**
----
  Returns json data with a jwt token after a successful login

* **URL**

  /login.php

* **Method:**

  `POST`
* **URL Params**

  None
  
*  **Data Params**

   **Required:**
 
   `username=[string]` OR  `email=[string]`
   `password=[string]`


* **Success Response:**

  * **Code:** 200 OK<br />
    **Content:** `{
    "error": false,
    "message": "Successful login",
    "jwt": "",
    "expireAt": 
}`
 
* **Error Response:**

  * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": ""}`


* **Sample Call:**

  ```curl
  curl --location --request POST 'http://localhost:8001/api/login.php' \
--header 'Content-Type: application/json' \
--form 'username="test"' \
--form 'password="123456"'
  ```
  
  OR
  
    ```curl
  curl --location --request POST 'http://localhost:8001/api/login.php' \
--header 'Content-Type: application/json' \
--form 'email="test@test.de"' \
--form 'password="123456"'
  ```
  
  
 **List providers**
----
  Returns json data with a list or just one integrated cloud provider

* **URL**

  /proivder.php

* **Method:**

  `GET`
* **URL Params**

     **Optional:**
 
   `name=[string]` Provider name
  
*  **Data Params**

      **Required:**
	  
    `Authorization: Bearer <jwt token>`


* **Success Response:**

  * **Code:** 200 OK<br />
    **Content:** {
    "error": false,
    "proivders": [
        {
            "name": "Hetzner",
            "baseurl": "https://api.hetzner.cloud/v1/",
            "enabled": true
        },
        {
            "name": "Vultr",
            "baseurl": "https://api.vultr.com/v2/",
            "enabled": true
        }
    ]
}
 
* **Error Response:**

  * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": ""}`


* **Sample Call:**
  ```curl
curl --location --request GET 'http://localhost:8001/api/provider.php' \
--header 'Content-Type: application/json' \
--header 'Authorization: Barear <jwt token>' \

  ```
OR
  ```curl
curl --location --request GET 'http://localhost:8001/api/provider.php?name=vultr' \
--header 'Content-Type: application/json' \
--header 'Authorization: Barear <jwt token>' \

  ```

**Manage provider API Keys**
----
  Allows to get/add/update/delete provider API Keys from the database

* **URL**

  /token.php

* **Method:**

  `GET` Get provider API Key(s) from database
  `PUT` Update provider API Key in database / Insert provider API Key to database
  `DELETE` Delete provider API Key from database
  
* **URL Params**

     **Optional:**
 
   `provider=[string]` Provider name
    `token=[string]` Provider API Key
	`enable=[bool]` Enable/disable provider
  
*  **Data Params**

      **Required:**
	  
    `Authorization: Bearer <jwt token>`


* **Success Response:**

  * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "Provider token successfully saved"}`

	OR
 * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "Provider token successfully updated"}`

	OR
 * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "Token status successfully updated"}`
	   
 	OR
 * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "Token successfully deleted"}`
 
 
* **Error Response:**

  * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": ""}`


* **Sample Call:**
  ```curl
curl --location --request GET 'http://localhost:8001/api/token.php' \
--header 'Content-Type: application/json' \
--header 'Authorization: Barear <jwt token>' \

  ```


  
  
**Manage SSH Keys**
----
  Allows to get/add/update/delete SSH Keys from the database

* **URL**

  /sshkey.php

* **Method:**

  `GET` Get SSH Keys from database
   `POST`   Insert SSH Keys to database
  `PUT` Update SSH Keys in database 
  `DELETE` Delete SSH Keys from database
  
* **URL Params**

     **Optional:**
 
   `name=[string]` Unique name
  
*  **Data Params**

      **Required:**
	  
    `Authorization: Bearer <jwt token>`
	
	 **Optional:**
	 
	 `<ssh key>`


* **Success Response:**

  * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "SSH key successfully saved"}`

	OR
 * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "SSH key successfully updated"}`

	OR
 * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "SSH key successfully deleted"}`
	   
 
 
* **Error Response:**

  * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": ""}`


* **Sample Call:**
  ```curl
curl --location --request GET 'http://localhost:8001/api/sshkey.php?name=test' \
--header 'Content-Type: application/json' \
--header 'Authorization: Barear <jwt token>' \
--data-raw '<ssh key>'
  ```
  
  
**Manage startup shell scripts**
----
  Allows to get/add/update/delete startup shell scripts from the database

* **URL**

  /script.php

* **Method:**

  `GET` Get startup shell scripts from database
   `POST`   Insert startup shell scripts to database
  `PUT` Update startup shell scripts in database 
  `DELETE` Delete startup shell scripts from database
  
* **URL Params**

     **Optional:**
 
   `name=[string]` Unique name
  
*  **Data Params**

      **Required:**
	  
    `Authorization: Bearer <jwt token>`
	
	 **Optional:**
	 
	 `<ssh key>`


* **Success Response:**

  * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "Startup script successfully saved"}`

	OR
 * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "Startup script successfully updated"}`

	OR
 * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "tartup script successfully deleted"}`
	   
 
 
* **Error Response:**

  * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": ""}`


* **Sample Call:**
  ```curl
curl --location --request GET 'http://localhost:8001/api/script.php?name=test' \
--header 'Content-Type: application/json' \
--header 'Authorization: Barear <jwt token>' \
--data-raw '<shell script>'
  ```

  
**Manage SSH Keys**
----
  Allows to get/add/update/delete SSH Keys from the database

* **URL**

  /sshkey.php

* **Method:**

  `GET` Get SSH Keys from database
   `POST`   Insert SSH Keys to database
  `PUT` Update SSH Keys in database 
  `DELETE` Delete SSH Keys from database
  
* **URL Params**

     **Optional:**
 
   `name=[string]` Unique name
  
*  **Data Params**

      **Required:**
	  
    `Authorization: Bearer <jwt token>`
	
	 **Optional:**
	 
	 `<ssh key>`


* **Success Response:**

  * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "SSH key successfully saved"}`

	OR
 * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "SSH key successfully updated"}`

	OR
 * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "SSH key successfully deleted"}`
	   
 
 
* **Error Response:**

  * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": ""}`


* **Sample Call:**
  ```curl
curl --location --request GET 'http://localhost:8001/api/sshkey.php?name=test' \
--header 'Content-Type: application/json' \
--header 'Authorization: Barear <jwt token>' \
--data-raw '<ssh key>'
  ```
  
  
**Get server locations of providers**
----
  Get available server location(s) of one or all providers

* **URL**

  /location.php

* **Method:**

  `GET` Get location(s) of all providers or one specific provider
  
* **URL Params**

     **Optional:**
 
  `provider=[string]` Provider name
   `id=[string]` ID (name) of the location
  
*  **Data Params**

      **Required:**
	  
    `Authorization: Bearer <jwt token>`
	



* **Success Response:**

  * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "Startup script successfully saved"}`

	OR
 * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "Startup script successfully updated"}`

	OR
 * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "tartup script successfully deleted"}`
	   
 
 
* **Error Response:**

  * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": ""}`


* **Sample Call:**
  ```curl
curl --location --request GET 'http://localhost:8001/api/location.php?provider=hetzner&id=fsn1' \
--header 'Content-Type: application/json' \
--header 'Authorization: Barear <jwt token>' \
  ```
  
    
**Get server plans of providers**
----
  Get available server plan(s) of one or all providers

* **URL**

  /plan.php

* **Method:**

  `GET` Get server plan(s) of all providers or one specific provider
  
* **URL Params**

     **Optional:**
 
  `provider=[string]` Provider name
   `id=[string]` ID (name) of the server plan
  
*  **Data Params**

      **Required:**
	  
    `Authorization: Bearer <jwt token>`
	



* **Success Response:**

  * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "Startup script successfully saved"}`

	OR
 * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "Startup script successfully updated"}`

	OR
 * **Code:** 200 OK<br />
       **Content:** `{ "error": true, "message": "tartup script successfully deleted"}`
	   
 
 
* **Error Response:**

  * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": ""}`


* **Sample Call:**
  ```curl
curl --location --request GET 'http://localhost:8001/api/plan.php?provider=hetzner&id=cx11' \
--header 'Content-Type: application/json' \
--header 'Authorization: Barear <jwt token>' \
  ```
