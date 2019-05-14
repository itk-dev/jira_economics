let Encore = require('@symfony/webpack-encore');

Encore
  .setOutputPath('public/build/')
  .setPublicPath('/build')
  .addEntry('app', './assets/js/app.js')
  .addEntry('billing', './bundles/Billing/Resources/assets/index.js')
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableSassLoader()
  .enableReactPreset()
  .enableVersioning()
  .enablePostCssLoader()
  .copyFiles({
    from: './assets/images',
  })
;

module.exports = [Encore.getWebpackConfig()];
