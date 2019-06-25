let Encore = require('@symfony/webpack-encore');

Encore
  .setOutputPath('public/build/')
  .setPublicPath('/build')
  .addEntry('app', './assets/js/app.js')
  .addEntry('billing', './bundles/Billing/Resources/assets/index.js')
  .addEntry('create_project', './bundles/CreateProject/Resources/assets/index.js')
  .addEntry('graphic_service_order', './bundles/GraphicServiceOrder/Resources/assets/index.js')
  .splitEntryChunks()
  .autoProvidejQuery()
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableSassLoader()
  .enableReactPreset()
  .enableVersioning()
  .enablePostCssLoader()
  .autoProvidejQuery()
  .copyFiles({
    from: './assets/images',
    to: 'images/[path][name].[ext]'
  })
;

module.exports = [Encore.getWebpackConfig()];
