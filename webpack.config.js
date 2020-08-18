
const env = process.env.NODE_ENV
const path = require( 'path' );

module.exports = env => {
  console.log(`🛠️YIKES Watcher: ${env} Mode 🛠️`);

  return {
    entry: {
        'repeatable-custom-tabs-settings': './js/repeatable-custom-tabs-settings.js',
        'repeatable-custom-tabs-shared': './js/repeatable-custom-tabs-shared.js',
        'repeatable-custom-tabs': './js/repeatable-custom-tabs.js',
        premium: './js/premium.js',
        settings: './js/settings.js',
    },
    output: {
        filename: '[name].min.js',
        path: path.resolve( __dirname, 'dist' )
    },
  }
};
