let Encore = require('@symfony/webpack-encore');

Encore
  .setOutputPath('public/build/')
  .setPublicPath('/build')
  .addEntry('app', './assets/js/app.js')
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .configureBabel(() => {}, {
    useBuiltIns: 'usage',
    corejs: 3
  })
  .enableSassLoader()
  .enablePostCssLoader()
;

const appConfig = Encore.getWebpackConfig();
appConfig.name = 'app';

Encore.reset();

Encore.setOutputPath('public/build/')
  .setPublicPath('/build')
  .enableReactPreset()
  .enableSourceMaps()
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableVersioning()
  .addEntry('js/billing', './bundles/Billing/Resources/assets/index.js')
  .enableSassLoader(function (options) {
    options.includePaths = ['node_modules'];
  })
  .configureCssLoader(options => {
    options.modules = true;
  });

const billingConfig = Encore.getWebpackConfig();
billingConfig.name = 'billing';

module.exports = [appConfig, billingConfig];
