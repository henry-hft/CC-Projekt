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
  `POST` Insert SSH Keys to database
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
  `POST` Insert startup shell scripts to database
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
  `POST` Insert SSH Keys to database
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
  Get available server location(s) of one specific or all providers

* **URL**

  /location.php

* **Method:**

  `GET`
  
* **URL Params**

     **Optional:**
 
  `provider=[string]` Provider name
  `id=[string]` ID (name) of the location
  
*  **Data Params**

      **Required:**
	  
    `Authorization: Bearer <jwt token>`


* **Success Response:**

  * **Code:** 200 OK<br />
       **Content:** `{"error":false,"locations":{"id":"fsn1","country":"DE","city":"Falkenstein","provider":"Hetzner"}}`

 
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
  Get available server plan(s) of one specific or all providers

* **URL**

  /plan.php

* **Method:**

  `GET` 
  
* **URL Params**

     **Optional:**
 
  `provider=[string]` Provider name
  `id=[string]` ID (name) of the server plan
  
*  **Data Params**

      **Required:**
	  
    `Authorization: Bearer <jwt token>`

* **Success Response:**

  * **Code:** 200 OK<br />
       **Content:** `{"error":false,"plans":{"id":"cx11","cores":1,"memory":2048,"disk":20000,"bandwidth":20480000}}`

 
 
* **Error Response:**

  * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": ""}`


* **Sample Call:**
  ```curl
  curl --location --request GET 'http://localhost:8001/api/plan.php?provider=hetzner&id=cx11' \
	--header 'Content-Type: application/json' \
	--header 'Authorization: Barear <jwt token>' \
  ```
  
  
    
  
**Get server locations of providers**
----
  Get available server location(s) of one specific or all providers

* **URL**

  /location.php

* **Method:**

  `GET` 
  
* **URL Params**

     **Optional:**
 
  `provider=[string]` Provider name
  `id=[string]` ID (name) of the location
  
*  **Data Params**

      **Required:**
	  
    `Authorization: Bearer <jwt token>`
	
* **Success Response:**

  * **Code:** 200 OK<br />
       **Content:** `{"error":false,"locations":{"id":"fsn1","country":"DE","city":"Falkenstein","provider":"Hetzner"}}`

 
* **Error Response:**

  * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": ""}`


* **Sample Call:**
  ```curl
  curl --location --request GET 'http://localhost:8001/api/plan.php?provider=hetzner&id=fsn1' \
	--header 'Content-Type: application/json' \
	--header 'Authorization: Barear <jwt token>' \
  ```
  
    
**Get operating systems of providers**
----
  Get available operating systems(s) of one or all providers

* **URL**

  /os.php

* **Method:**

  `GET` 
  
* **URL Params**

     **Optional:**
 
  `provider=[string]` Provider name
  `id=[string]` ID (name) of the operating system
  `family=[string]` Operating system family (e.g. debian, ubuntu)
   
  
*  **Data Params**

      **Required:**
	  
    `Authorization: Bearer <jwt token>`
	
* **Success Response:**

  * **Code:** 200 OK<br />
       **Content:** `{"error":false,"os":[{"id":2,"name":"Debian 9","family":"debian"},{"id":5924233,"name":"Debian 10","family":"debian"}]}`

* **Error Response:**

  * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": ""}`


* **Sample Call:**
  ```curl
  curl --location --request GET 'http://localhost:8001/api/os.php?provider=hetzner&family=debian' \
	--header 'Content-Type: application/json' \
	--header 'Authorization: Barear <jwt token>' \
  ```
  
**Create virtual servers**
----
  Create one or multiple virtual servers at a specific provider

* **URL**

  /create.php

* **Method:**

  `POST`
  
* **URL Params**

     **Required:**
	 
 `hostname=[string]` Hostname of the server
  `provider=[string]` Provider name
  `location=[string]` ID (name) of the location
  `os=[string]` ID (name) of the operating system
  `plan=[string]` ID (name) of the server plan
  `sshkey=[string]` Name of the SSH Key
	   
  **Optional:**
 
  `amount=[integer]` Amount of servers that should be created (default: 1)
  `script=[string]` Name of the shell startup script
   
  
*  **Data Params**

      **Required:**
	  
    `Authorization: Bearer <jwt token>`
	

* **Success Response:**

  * **Code:** 200 OK<br />
       **Content:** `{"error":false,"message":"Server successfully created","servers":{"id":13047474,"hostname":"testserver","status":"initializing","os":"Debian 10","osID":5924233,"location":"nbg1","plan":"cx11"}}`

 
 
* **Error Response:**

  * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": "Server could not be created"}`


* **Sample Call:**
  ```curl
  curl --location --request POST 'http://localhost:8001/api/create.php?provider=hetzner&location=nbg1&os=5924233&plan=cx11&hostname=testserver&sshkey=test&script=test&amount=4' \
	--header 'Content-Type: application/json' \
	--header 'Authorization: Barear <jwt token>' \
  ```
    
**Delete virtual server**
----
 Delete a specific virtual servers at a specific provider

* **URL**

  /delete.php

* **Method:**

  `POST`
  
* **URL Params**

     **Required:**
	 
  `provider=[string]` Provider name
  `id=[string]` ID of the server

*  **Data Params**

      **Required:**
	  
    `Authorization: Bearer <jwt token>`
	
* **Success Response:**

  * **Code:** 200 OK<br />
    **Content:** `{"error":false,"message":"Server successfully deleted"}`

 
 
* **Error Response:**

  * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": "Server could not be deleted"}`


* **Sample Call:**
  ```curl
  curl --location --request POST 'http://localhost:8001/api/delete.php?provider=hetzner&id=12870359' \
	--header 'Content-Type: application/json' \
	--header 'Authorization: Barear <jwt token>' \
  ```
      
**List virtual server(s)**
----
 List a specific or all virtual servers of a specific or all providers

* **URL**

  /server.php

* **Method:**

  `GET`
  
* **URL Params**

     **Optional:**
	 
  `provider=[string]` Provider name
  `id=[string]` ID of the server 
  
*  **Data Params**

      **Required:**
	  
    `Authorization: Bearer <jwt token>`
	
* **Success Response:**

  * **Code:** 200 OK<br />
       **Content:** `{"error":false,"servers":[{"id":13047474,"hostname":"testh","status":"running","created":1625686722,"ipv4":"116.203.100.32","ipv6":"2a01:4f8:c0c:76a0::\/64","location":"nbg1","os":"Debian 10","osID":5924233,"plan":"cx11","bandwidth":21990232555,"cores":1,"memory":2000,"disk":20000}]}`

 
* **Error Response:**

  * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": "Server not found"}`


* **Sample Call:**
  ```curl
  curl --location --request GET 'http://localhost:8001/api/server.php?provider=vultr&id=id=09046fc7-3ae6-46a0-8e3e-29c9d9b12bac' \
	--header 'Content-Type: application/json' \
	--header 'Authorization: Barear <jwt token>' \
  ```
  
  
  **Control a virtual server**
----
 Boot, reboot or shutdown a specific virtual server

* **URL**

  /server.php

* **Method:**

  `GET`
  
* **URL Params**

     **Required:**
	 
  `provider=[string]` Provider name
  `id=[string]` ID of the server
  `action=[string]` boot, reboot or shutdown
  
*  **Data Params**

      **Required:**
	  
    `Authorization: Bearer <jwt token>`

* **Success Response:**


 * **Code:** 200 OK <br />
    **Content:** `{ "error": false, "message": "The server has been restarted successfully"}`

OR 
 
 * **Code:** 200 OK <br />
    **Content:** `{ "error": false, "message": "The server has been started successfully"}`
	
OR 
 * **Code:** 200 OK <br />
    **Content:** `{ "error": false, "message": "The server has been stopped successfully"}`
 
 
* **Error Response:**

 * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": "The server could not be restarted."}`

OR 
 
 * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": "The server could not be started."}`
	
OR 
 * **Code:** 400 BAD Request <br />
    **Content:** `{ "error": true, "message": "The server could not be stopped."}`

* **Sample Call:**
  ```curl
  curl --location --request GET 'http://localhost:8001/api/control.php?action=reboot&provider=vultr&id=id=09046fc7-3ae6-46a0-8e3e-29c9d9b12bac' \	
	--header 'Content-Type: application/json' \
	--header 'Authorization: Barear <jwt token>' \
  ```
