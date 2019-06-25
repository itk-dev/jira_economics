# ITK Jira

Apps to make life with Jira easier.

## Getting started

### Using the docker setup (recommended)
The repository comes with a complete docker compose setup to run the project.

```bash
docker-compose up -d
docker-compose exec phpfpm composer install
docker-compose run yarn install
docker-compose run yarn watch # or docker-compose run yarn build for production build
```

### Project installation.

````bash
cp .env .env.local
docker-compose exec phpfpm bin/console doctrine:migrations:migrate
````

###
Find the port to access the project:
````bash
echo "http://0.0.0.0:$(docker-compose port reverse-proxy 80 | cut -d: -f2)"
````

# Production build

````bash
docker-compose exec phpfpm composer install --no-dev -o
docker-compose run yarn install
docker-compose run yarn build
```` 

### Without docker
Please make sure you have [node](https://nodejs.org/en/download/) and [yarn](https://yarnpkg.com/en/docs/install) installed in your system.

Then run the following commands to clone the project, install dependencies and start the application.

```bash
git clone git@github.com:aakb/jira_economics.git # clone the project
yarn install  # install dependencies
composer install  # install dependencies
yarn watch # start webpack encore from stripts in package.json
bin/console server:run # start PHP's built-in web server
```


# Connect to Jira

## Create the key
```
openssl genrsa -out mykey.pem 2048
openssl rsa -in mykey.pem -pubout
```

## Register application link in Jira
https://confluence.atlassian.com/adminjiraserver073/using-applinks-to-link-to-other-applications-861253079.html

### https://[SITE].atlassian.net/plugins/servlet/applinks/listApplicationLinks

"Create new link" -> Fill out "Incoming Authentication":
```
Consumer Key: [KEY]
Consumer Name: jira.vm
Public Key: Insert public key
Consumer Callback url: http://jira.vm/main/
```

Set values in .env.local:

```
JIRA_OAUTH_CUSTOMER_KEY=[KEY]
JIRA_OAUTH_PEM_PATH=[PATH TO PRIVATE KEY]
JIRA_URL='https://[SITE].atlassian.net'
JIRA_DEFAULT_BOARD=[TEAM BOARD ID]
```

#Setup Database
DATABASE_URL=""

#Setup bundles/CreateProject
Set values in .env.local:

```
# The default lead for new accounts being created
CPB_ACCOUNT_MANAGER="[A Jira username]"
```

Define the config
- Copy config/create_project_config.yml to config/create_project_config.local.yml
- Define each team config.


#Setup bundles/GraphicServiceOrder
Set values in .env.local:

```
# Form configuration
FORM_FILE_GS_UPLOAD_SIZE=100M
```

Set values for owncloudservice:

```
###> ownCloudService ###
OWNCLOUD_HOST=""
OWNCLOUD_USERNAME=""
OWNCLOUD_PASSWORD=""
###< ownCloudService ###
```