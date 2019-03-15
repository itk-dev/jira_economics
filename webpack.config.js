let Encore = require('@symfony/webpack-encore');

Encore.setOutputPath('public/build/')
  .setPublicPath('/build')
  .enableReactPreset()
  .enableSourceMaps()
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .addEntry('js/app', './assets/index.js')
  .enableSassLoader(function (options) {
    options.includePaths = ['node_modules'];
  })
  .configureCssLoader(options => {
    options.modules = true;
  });

module.exports = Encore.getWebpackConfig();
