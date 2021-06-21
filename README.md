Ory Hydra Sample Implementation
===============================

Overview
--------

This repo contains two Laravel projects:  

1. server-app: This project contains the logic for Login, Consent, and Logout UIs for use with the Ory Hydra server.
2. client-app: This project contains a sample OAuth2 client application to test the authentication.

Besides these, you also need the `ory hydra` from `https://github.com/ory/hydra.git`

In order to test this, you must:
1. Start the `server-app` to listen on port `8000`
2. Edit the `ory hydra` quickstart sample `quickstart.yml` file and change the port for `self.consent`, `self.login`, and `self.logout` from original `3000` to `8000` to match the `server-app`.
3. Start the `ory hydra` docker containers by running `docker-compose up --build -d` from the `ory hydra` folder.
4. Register a new client with `ory hydra` by running  
   ```
   docker-compose -f quickstart.yml exec hydra hydra clients create --endpoint http://127.0.0.1:4445/ --id 8dJoZn1rY3oIH5R1Aau5aYl7TpuPTAVT --secret 49qkTWr7FmjQwAeaRkCuVz8aNXx4iRd1 -g client_credentials,authorization_code -c http://127.0.0.1:8001/login -a openid,offline
   ```
   This is telling `ory hydra` to register a client with the following specifications:
   ```
   client id: 8dJoZn1rY3oIH5R1Aau5aYl7TpuPTAVT
   client secret: 49qkTWr7FmjQwAeaRkCuVz8aNXx4iRd1
   redirect url: http://127.0.0.1:8001/login
   requested scopes: openid, offline
   ```
   `http://127.0.0.1:4445/` is the `ory hydra` admin API endpoint.
   Now edit the `client-app` .env file and add the following environtment variable values:
   `OAUTH2_CLIENT_ID=<client id>`
   `OAUTH2_CLIENT_SECRET=<client secret>`
   `OAUTH2_ISSUER_URL=http://127.0.0.1:4444` #ory hydra public api endpoint
   `OAUTH2_AUTHORIZE_URL=http://127.0.0.1:4444/oauth2/auth`
   `OAUTH2_ACCESS_TOKEN_URL=http://127.0.0.1:4444/oauth2/token`
   `OAUTH2_RESOURCE_URL=http://127.0.0.1:8000/resource` #doesn't exist yet. placeholder for future
   `OAUTH2_LOGOUT_URL=http://127.0.0.1:4444/oauth2/sessions/logout`
5. Start the `client-app` to listen on port `8001`
6. Start the browser and go to `http://127.0.0.1:8001/login`  
   Enter same value in username and password fields to simulate a successful login attempt. 
