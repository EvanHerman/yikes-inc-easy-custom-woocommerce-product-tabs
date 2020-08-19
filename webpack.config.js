
const path = require( 'path' );
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = env => {
  console.log(`YIKES Tools 🛠️🛠️🛠️`);

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
    module: {
        rules: [
          {
            test: /\.scss$/,
            use: [
                MiniCssExtractPlugin.loader,
                'css-loader',
                'sass-loader'
            ]
          },
          {
            test: /\.js$/,
            exclude: /(node_modules)/,
            use: {
              loader: 'babel-loader',
              options: {
                presets: [ '@babel/preset-env' ]
              }
            }
          }
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].min.css',
        }),
    ]
  }
};
