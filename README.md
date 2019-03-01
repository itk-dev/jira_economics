# ITK Invoicesystem frontend

Frontend based on Atlaskit starter:
[Atlaskit-starter](https://bitbucket.org/atlassian/atlaskit-starter/)

Package examples can be found here:
[Packages](https://atlaskit.atlassian.com/packages/core/) [Examples](https://atlaskit.atlassian.com/examples/core/analytics-next/basic-create-and-fire)

## Getting started

Please make sure you have [node](https://nodejs.org/en/download/) and [yarn](https://yarnpkg.com/en/docs/install) installed in your system.

Then run the following commands to clone the project, install dependencies and start the application.

```bash
git clone #TODO: Insert itk-dev git ulr # clone the project
yarn install  # install dependencies
yarn start  # start the project
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
