# ITK Invoicesystem frontend

Frontend based on Atlaskit starter:
[Atlaskit-starter](https://bitbucket.org/atlassian/atlaskit-starter/)

Package examples can be found here:
[Packages](https://atlaskit.atlassian.com/packages/core/) [Examples](https://atlaskit.atlassian.com/examples/core/analytics-next/basic-create-and-fire)

## Getting started

Please make sure you have [node](https://nodejs.org/en/download/) and [yarn](https://yarnpkg.com/en/docs/install) installed in your system.

Then run the following commands to clone the project, install dependencies and start the application.

```bash
git clone git@github.com:aakb/jira_economics.git # clone the project
yarn install  # install dependencies
composer install  # install dependencies
yarn run encore dev --watch # start webpack encore
bin/console server:run # start PHP's built-in web server
```

## Using more Atlaskit components

This repo ships with some of the Atlaskit components such as `@atlaskit/navigation` and `@atlaskit/avatar`.

You can add other components (listed at [https://atlaskit.atlassian.com/](https://atlaskit.atlassian.com/)) to your project. To see an exmaple in order to add button in your project run:

```bash
yarn add @atlaskit/button
```

Then in the relevant React component file (e.g. `src/App.jsx`) do the following:

```js
import Button from '@atlaskit/button';

// ...

render() {
  <Page>
    <Button>My button text</Button>
  </Page>
}
```

# Jira

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
